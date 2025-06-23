<?php

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Resources\ScheduleResource\Pages;
use App\Filament\Instructor\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use App\Models\Instructor;
use App\Http\Controllers\WhatsappController;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Jadwal Saya';
    protected static ?string $modelLabel = 'Jadwal Mengemudi';
    protected static ?string $pluralModelLabel = 'Jadwal Mengemudi';
    protected static ?int $navigationSort = 10;

    /**
     * Mengirim notifikasi WhatsApp untuk pembaruan jadwal
     * 
     * @param \App\Models\Schedule $schedule
     * @return void
     */
    protected static function sendScheduleUpdateNotification(Schedule $schedule)
    {
        try {
            // Try to get the student directly from the schedule
            $student = null;

            // First, try to get the student using the accessor attribute
            if ($schedule->student) {
                $student = $schedule->student;
                Log::info("Found student from schedule's student attribute", [
                    'student_id' => $student->id,
                    'schedule_id' => $schedule->id
                ]);
            }

            // If we couldn't find a student that way, try to get through studentCourse
            if (!$student && $schedule->studentCourse && $schedule->studentCourse->student) {
                $student = $schedule->studentCourse->student;
                Log::info("Found student from studentCourse relationship", [
                    'student_id' => $student->id,
                    'schedule_id' => $schedule->id
                ]);
            }

            if (!$student) {
                Log::error("Cannot send WhatsApp notification: Could not find student from schedule", [
                    'schedule_id' => $schedule->id
                ]);
                return;
            }

            // Check if student's user has a phone number
            if (empty($student->user->phone)) {
                Log::warning("Cannot send WhatsApp notification: Student {$student->id} has no phone number");
                return;
            }

            // Use WhatsappController to send schedule update
            $whatsappController = new WhatsappController();
            $result = $whatsappController->sendScheduleUpdateNotification($student, $schedule);

            Log::info("WhatsApp schedule update notification sent from instructor side", [
                'user_id' => $student->user->id ?? 'unknown',
                'student_id' => $student->id,
                'phone' => $student->user->phone,
                'schedule_id' => $schedule->id,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp notification from instructor side: " . $e->getMessage(), [
                'schedule_id' => $schedule->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('studentCourse.student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('studentCourse.course.name')
                    ->label('Nama Kursus')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('for_session')
                    ->label('Sesi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->formatStateUsing(function ($state) {
                        if ($state === null || $state === '') {
                            return 'Tanggal belum diatur';
                        }
                        return $state->format('M d, Y H:i');
                    })
                    ->badge(fn($state) => $state === null)
                    ->color(fn($state) => $state === null ? 'danger' : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'waiting_approval' => 'warning',
                        'waiting_instructor_approval' => 'info',
                        'waiting_admin_approval' => 'gray',
                        'date_not_set' => 'danger',
                        'ready' => 'success',
                        'waiting_signature' => 'warning',
                        'complete' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => str_replace('_', ' ', ucfirst($state))),

                Tables\Columns\IconColumn::make('att_instructor')
                    ->label('Tanda Tangan Instruktur')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'ready' => 'Siap',
                        'waiting_signature' => 'Menunggu Tanda Tangan',
                        'complete' => 'Selesai',
                    ]),
            ])
            ->actions([
                // View action for showing schedule details
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn(Schedule $record) => 'Jadwal Mengemudi #' . $record->id)
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Lihat Detail')
                    ->modalWidth('xl')
                    ->extraModalFooterActions(fn() => [])
                    ->modalContent(function (Schedule $record) {
                        return view('filament.instructor.resources.schedule.view', [
                            'record' => $record
                        ]);
                    })->modalActions([
                        Tables\Actions\Action::make('add_signature_modal')
                            ->label('')
                            ->icon('heroicon-o-pencil')
                            ->color('success')
                            ->tooltip('Tambah Tanda Tangan')
                            ->visible(fn(Schedule $record) => $record->status === 'waiting_signature' && $record->att_instructor != 1)
                            ->requiresConfirmation()
                            ->modalHeading('Konfirmasi Tanda Tangan')
                            ->modalDescription('Dengan menambahkan tanda tangan Anda, Anda mengonfirmasi bahwa Anda telah melakukan sesi mengemudi ini. Tindakan ini tidak dapat dibatalkan.')
                            ->modalSubmitActionLabel('Konfirmasi Tanda Tangan')
                            ->action(function (Schedule $record) {
                                // Since we're in the Instructor namespace, update att_instructor to 1
                                $updateData = ['att_instructor' => 1];

                                // Check if student has already signed (att_student = 1)
                                if ($record->att_student == 1) {
                                    // Both student and instructor have signed, mark as complete
                                    $updateData['status'] = 'complete';
                                }

                                $record->update($updateData);

                                // Refresh the record to get updated data
                                $record->refresh();

                                // Send WhatsApp notification if schedule is completed
                                if (isset($updateData['status']) && $updateData['status'] === 'complete') {
                                    self::sendScheduleUpdateNotification($record);
                                }

                                // Check if the session is now complete
                                if (isset($updateData['status']) && $updateData['status'] === 'complete') {
                                    // Check if all schedules for this student course are complete
                                    $studentCourseId = $record->student_course_id;

                                    // Count total schedules for this course
                                    $totalSchedules = Schedule::where('student_course_id', $studentCourseId)->count();

                                    // Count completed schedules
                                    $completedSchedules = Schedule::where('student_course_id', $studentCourseId)
                                        ->where('status', 'complete')
                                        ->count();

                                    // If all schedules are complete, update the student_course status to 'done'
                                    if ($totalSchedules === $completedSchedules) {
                                        \App\Models\StudentCourse::where('id', $studentCourseId)
                                            ->update(['status' => 'done']);

                                        \Filament\Notifications\Notification::make()
                                            ->title('Kursus Selesai')
                                            ->body('Semua sesi untuk kursus ini telah selesai.')
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Sesi Selesai')
                                            ->body('Anda dan siswa telah menandatangani. Sesi sekarang telah selesai.')
                                            ->success()
                                            ->send();
                                    }
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Tanda Tangan Dikonfirmasi')
                                        ->body('Tanda tangan Anda telah ditambahkan. Menunggu tanda tangan siswa untuk menyelesaikan sesi.')
                                        ->success()
                                        ->send();
                                }
                            }),
                    ]),

                // Direct sign action in the table
                Tables\Actions\Action::make('confirm_sign')
                    ->label('Konfirmasi Tanda Tangan')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(Schedule $record) => $record->status === 'waiting_signature' && $record->att_instructor != 1)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Tanda Tangan Sesi')
                    ->modalDescription('Dengan menambahkan tanda tangan Anda, Anda mengonfirmasi bahwa Anda telah melakukan sesi mengemudi ini. Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Tanda Tangan')
                    ->action(function (Schedule $record) {
                        // Update att_instructor to 1
                        $updateData = ['att_instructor' => 1];

                        // Check if student has already signed
                        if ($record->att_student == 1) {
                            // Both have signed, mark as complete
                            $updateData['status'] = 'complete';
                        }

                        $record->update($updateData);

                        // Refresh the record to get updated data
                        $record->refresh();

                        // Send WhatsApp notification if schedule is completed
                        if (isset($updateData['status']) && $updateData['status'] === 'complete') {
                            self::sendScheduleUpdateNotification($record);
                        }

                        // Check if the session is now complete
                        if (isset($updateData['status']) && $updateData['status'] === 'complete') {
                            // Check if all schedules for this student course are complete
                            $studentCourseId = $record->student_course_id;
                            $totalSchedules = Schedule::where('student_course_id', $studentCourseId)->count();
                            $completedSchedules = Schedule::where('student_course_id', $studentCourseId)
                                ->where('status', 'complete')
                                ->count();

                            // If all schedules are complete, update the student_course status
                            if ($totalSchedules === $completedSchedules) {
                                \App\Models\StudentCourse::where('id', $studentCourseId)
                                    ->update(['status' => 'done']);

                                Notification::make()
                                    ->title('Kursus Selesai')
                                    ->body('Semua sesi untuk kursus ini telah selesai.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Sesi Selesai')
                                    ->body('Anda dan siswa telah menandatangani. Sesi sekarang telah selesai.')
                                    ->success()
                                    ->send();
                            }
                        } else {
                            Notification::make()
                                ->title('Tanda Tangan Dikonfirmasi')
                                ->body('Tanda tangan Anda telah ditambahkan. Menunggu tanda tangan siswa untuk menyelesaikan sesi.')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            // No need for create or edit pages
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Get the currently logged-in user
        $user = auth()->user();

        if (!$user) {
            // If user is not logged in, return empty query
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Find the instructor associated with this user
        $instructor = Instructor::where('user_id', $user->id)->first();

        // Only show schedules assigned to the currently logged-in instructor
        return parent::getEloquentQuery()
            ->when($instructor, function ($query) use ($instructor) {
                return $query->where('instructor_id', $instructor->id);
            }, function ($query) {
                // If no instructor is found, return no results (safety measure)
                return $query->whereRaw('1 = 0');
            });
    }

    public static function getNavigationBadge(): ?string
    {
        // If no user is logged in, avoid the query entirely
        if (!auth()->user()) {
            return null;
        }

        // Count schedules that require attention (need signatures)
        try {
            return static::getEloquentQuery()
                ->where('status', 'waiting_signature')
                ->count() ?: null;
        } catch (\Exception $e) {
            // In case of any error, don't show a badge
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 0 ? 'warning' : null;
    }
}
