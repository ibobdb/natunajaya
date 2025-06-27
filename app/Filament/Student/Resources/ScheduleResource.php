<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\ScheduleResource\Pages;
use App\Filament\Student\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use App\Models\Instructor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Jadwal Saya';

    protected static ?string $modelLabel = 'Jadwal Mengemudi';

    protected static ?string $pluralModelLabel = 'Jadwal Mengemudi';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            // ->where('status', 'ready')
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 0 ? 'warning' : null;
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
            ->defaultSort(function (Builder $query) {
                // The strategy here is to use a subquery to get the first start_date for each course
                // Create a subquery to get the earliest start date for each student_course_id
                $subquery = \DB::table('schedules as s')
                    ->select('s.student_course_id', \DB::raw('MIN(s.start_date) as first_start_date'))
                    ->whereNotNull('s.start_date')
                    ->groupBy('s.student_course_id');

                // Join with the main query
                $query->join('student_courses', 'schedules.student_course_id', '=', 'student_courses.id')
                    ->join('courses', 'student_courses.course_id', '=', 'courses.id')
                    ->leftJoinSub($subquery, 'first_dates', function ($join) {
                        $join->on('schedules.student_course_id', '=', 'first_dates.student_course_id');
                    })
                    // First group by course name
                    ->orderBy('courses.name', 'asc')
                    // Then sort courses by their earliest scheduled date
                    ->orderByRaw('CASE WHEN first_dates.first_start_date IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('first_dates.first_start_date', 'asc')
                    // Within each course, order by session number
                    ->orderBy('schedules.for_session', 'asc')
                    // Keep the select focused on schedules table only
                    ->select('schedules.*');
            })
            ->recordUrl(null) // Disable default navigation on row click
            ->recordAction('view') // Action to trigger when clicking on row

            ->columns([
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
                Tables\Columns\IconColumn::make('att_student')
                    ->label('Tanda Tangan Siswa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('att_instructor')
                    ->label('Tanda Tangan Instruktur')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

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
                        return view('filament.student.resources.schedule.view', [
                            'record' => $record
                        ]);
                    })->modalActions([
                        Tables\Actions\Action::make('add_signature_modal')
                            ->label('')
                            ->icon('heroicon-o-pencil')
                            ->color('success')
                            ->tooltip('Tanda Tangan')
                            ->visible(fn(Schedule $record) => $record->status === 'waiting_signature' && $record->att_student != 1)
                            ->requiresConfirmation()
                            ->modalHeading('Konfirmasi Tanda Tangan')
                            ->modalDescription('Dengan menambahkan tanda tangan Anda, Anda mengonfirmasi bahwa Anda telah mengikuti sesi mengemudi ini. Tindakan ini tidak dapat dibatalkan.')
                            ->modalSubmitActionLabel('Konfirmasi Tanda Tangan')
                            ->action(function (Schedule $record) {
                                // Since we're in the Student namespace, update att_student to 1
                                $updateData = ['att_student' => 1];

                                // Check if instructor has already signed (att_instructor = 1)
                                if ($record->att_instructor == 1) {
                                    // Both student and instructor have signed, mark as complete
                                    $updateData['status'] = 'complete';
                                }

                                $record->update($updateData);

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

                                        \Illuminate\Support\Facades\Log::info(
                                            'Updated student course status to done - all schedules complete',
                                            ['student_course_id' => $studentCourseId]
                                        );

                                        \Filament\Notifications\Notification::make()
                                            ->title('Kursus Selesai')
                                            ->body('Selamat! Anda telah menyelesaikan semua sesi untuk kursus ini.')
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Sesi Selesai')
                                            ->body('Anda dan instruktur telah menandatangani. Sesi sekarang telah selesai.')
                                            ->success()
                                            ->send();
                                    }
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Tanda Tangan Dikonfirmasi')
                                        ->body('Tanda tangan Anda telah ditambahkan. Menunggu tanda tangan instruktur untuk menyelesaikan sesi.')
                                        ->success()
                                        ->send();
                                }
                            }),
                    ]),

                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip(fn(Schedule $record) => $record->start_date === null ? 'Set Date' : 'Reschedule')
                    ->icon('heroicon-o-calendar')
                    ->iconButton()
                    ->visible(fn(Schedule $record) => !in_array($record->status, ['complete', 'waiting_signature']))
                    ->form([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->minDate(now()->addDay())
                            // ->seconds(false)
                            ->helperText('Silakan pilih tanggal minimal 1 hari dari sekarang.')
                            ->validationMessages([
                                'min' => 'Tanggal harus minimal 1 hari dari sekarang.',
                            ]),
                        Forms\Components\Select::make('instructor_id')
                            ->label('Instruktur')
                            ->options(function () {
                                return \App\Models\Instructor::all()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->default(fn(Schedule $record) => $record->instructor_id)
                            ->helperText('Pilih instruktur untuk sesi ini'),
                        Forms\Components\Select::make('car_id')
                            ->label('Mobil')
                            ->options(function (Schedule $record) {
                                return \App\Models\Car::whereIn('type', function ($query) use ($record) {
                                    $query->select('courses.default_car_type')
                                        ->from('courses')
                                        ->join('student_courses', 'courses.id', '=', 'student_courses.course_id')
                                        ->where('student_courses.id', $record->student_course_id)
                                        ->distinct();
                                })
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Opsional. Hanya menampilkan mobil yang sesuai dengan kursus ini')
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('Silakan konfirmasi detail jadwal sebelum mengirim.')
                    ->modalSubmitActionLabel('Simpan Jadwal')
                    ->successNotification(null) // Disable the default success notification
                    ->afterFormFilled(function (array $data) {
                        return $data;
                    })
                    ->using(function (Schedule $record, array $data) {
                        // Check if car is available at the requested time
                        $startDate = $data['start_date'];
                        $carId = $data['car_id'];

                        // Convert to Carbon instance if it's not already
                        $startDateCarbon = $startDate instanceof \Carbon\Carbon
                            ? $startDate
                            : \Carbon\Carbon::parse($startDate);

                        // Define the time window (3 hours before and after the requested time)
                        $startWindow = $startDateCarbon->copy()->subHours(3);
                        $endWindow = $startDateCarbon->copy()->addHours(3);

                        // Perform all validations first before deciding what to do
                        $validationErrors = [];

                        // Get the instructor ID from the form if provided, otherwise use the one from student course
                        $instructorId = $data['instructor_id'] ?? $record->studentCourse->instructor_id;

                        // Check session order validation
                        // If current session is greater than 1, check that all previous sessions are scheduled
                        // and that this session is not scheduled before the most recent previous session
                        if ($record->for_session > 1) {
                            // Find the previous session
                            $previousSession = Schedule::query()
                                ->where('student_course_id', $record->student_course_id)
                                ->where('for_session', $record->for_session - 1)
                                ->first();

                            if ($previousSession) {
                                // Check if previous session has a start date
                                if (!$previousSession->start_date) {
                                    $validationErrors[] = [
                                        'title' => 'Perubahan Jadwal Tidak Valid',
                                        'message' => "Anda harus menjadwalkan sesi #" . ($record->for_session - 1) . " sebelum menjadwalkan sesi #" . $record->for_session . "."
                                    ];
                                }
                                // Check if current session is being scheduled before the previous session
                                else if ($startDateCarbon->lt($previousSession->start_date)) {
                                    $prevDate = $previousSession->start_date->format('M d, Y H:i');
                                    $validationErrors[] = [
                                        'title' => 'Perubahan Jadwal Tidak Valid',
                                        'message' => "Sesi #" . $record->for_session . " tidak dapat dijadwalkan sebelum sesi #" . ($record->for_session - 1) . " ($prevDate)."
                                    ];
                                }
                            }
                        }

                        // Check if instructor has any conflicting schedules
                        $instructorConflict = Schedule::query()
                            ->where('id', '!=', $record->id)
                            ->whereNotNull('start_date')
                            ->where('instructor_id', $instructorId)
                            ->where(function ($query) use ($startWindow, $endWindow, $startDateCarbon) {
                                // A schedule conflicts if:
                                // 1. It starts within our window
                                $query->whereBetween('start_date', [$startWindow, $endWindow]);
                                // OR 2. Our requested time is within their window (assuming 3 hour sessions)
                                $query->orWhere(function ($q) use ($startDateCarbon) {
                                    $q->where('start_date', '<=', $startDateCarbon)
                                        ->where('start_date', '>=', $startDateCarbon->copy()->subHours(3));
                                });
                            })
                            ->exists();

                        // Instructor availability validation
                        if ($instructorConflict) {
                            $instructorName = isset($data['instructor_id']) ?
                                Instructor::find($data['instructor_id'])->name ?? "Instruktur terpilih" :
                                $record->studentCourse->instructor->name ?? "Instruktur Anda";
                            $validationErrors[] = [
                                'title' => 'Instruktur Tidak Tersedia',
                                'message' => "{$instructorName} sudah dijadwalkan dalam rentang 3 jam dari waktu yang Anda minta. Silakan pilih waktu yang berbeda."
                            ];
                        }

                        // Check if car is available at the requested time
                        $conflictingSchedules = Schedule::query()
                            ->where('id', '!=', $record->id)
                            ->where('car_id', $carId)
                            ->whereNotNull('start_date')
                            ->where(function ($query) use ($startWindow, $endWindow, $startDateCarbon) {
                                // A schedule conflicts if:
                                // 1. It starts within our window
                                $query->whereBetween('start_date', [$startWindow, $endWindow]);
                                // OR 2. Our requested time is within their window (assuming 3 hour sessions)
                                $query->orWhere(function ($q) use ($startDateCarbon) {
                                    $q->where('start_date', '<=', $startDateCarbon)
                                        ->where('start_date', '>=', $startDateCarbon->copy()->subHours(3));
                                });
                            })
                            ->exists();

                        // Car availability validation
                        if ($conflictingSchedules) {
                            $carName = \App\Models\Car::find($carId)->name ?? "Mobil terpilih";
                            $validationErrors[] = [
                                'title' => 'Mobil Tidak Tersedia',
                                'message' => "Mobil yang dipilih ($carName) sudah dibooking dalam rentang 3 jam dari waktu yang Anda minta. Silakan pilih waktu atau mobil lain."
                            ];
                        }

                        // Check if there's already a session for the same course on the same date
                        $courseId = $record->studentCourse->course_id;
                        $sessionNumber = $record->for_session;

                        // Get just the date string (YYYY-MM-DD) for comparison
                        $scheduleDateString = $startDateCarbon->format('Y-m-d');

                        // Improved query to find any conflicting session for the same course on the same date
                        $sameDayScheduleExists = Schedule::query()
                            ->where('id', '!=', $record->id)
                            ->whereNotNull('start_date')
                            ->whereHas('studentCourse', function ($query) use ($courseId) {
                                $query->where('course_id', $courseId);
                            })
                            ->where('for_session', $sessionNumber)
                            ->whereRaw("DATE(start_date) = ?", [$scheduleDateString])
                            ->exists();

                        // Course session validation
                        if ($sameDayScheduleExists) {
                            $courseName = $record->studentCourse->course->name ?? "This course";
                            $formattedDate = $startDateCarbon->format('F j, Y');
                            $validationErrors[] = [
                                'title' => 'Jadwal Sudah Ada',
                                'message' => "Sesi #{$sessionNumber} untuk \"{$courseName}\" sudah dijadwalkan pada {$formattedDate}. Silakan pilih tanggal yang berbeda."
                            ];
                        }

                        // If any validation errors, show them and return false
                        if (!empty($validationErrors)) {
                            // Show only the first error to avoid notification overload
                            \Filament\Notifications\Notification::make()
                                ->title($validationErrors[0]['title'])
                                ->body($validationErrors[0]['message'])
                                ->danger()
                                ->persistent()
                                ->send();

                            // If there are multiple errors, show a summary
                            if (count($validationErrors) > 1) {
                                $additionalErrors = count($validationErrors) - 1;
                                \Filament\Notifications\Notification::make()
                                    ->title('Ditemukan Masalah Tambahan')
                                    ->body("Terdapat {$additionalErrors} masalah validasi lainnya. Harap perbaiki semua masalah sebelum mengirimkan.")
                                    ->warning()
                                    ->send();
                            }

                            // Halt execution and prevent form submission
                            return false;
                        }

                        // If all validations pass, then proceed with update
                        try {
                            // Use the instructor_id from the form if provided, otherwise use the one from student course
                            $instructorId = $data['instructor_id'] ?? $record->studentCourse->instructor_id;

                            // Check if instructor was changed (for notification purposes only)
                            $instructorChanged = isset($data['instructor_id']) && $data['instructor_id'] != $record->instructor_id;

                            $record->update([
                                'start_date' => $data['start_date'],
                                'car_id' => $data['car_id'],
                                'instructor_approval' => 1, // Always approved
                                'admin_approval' => 1,      // Always approved
                                'status' => 'ready',        // Always ready
                                'instructor_id' => $instructorId,
                            ]);

                            // Get the student course ID for this schedule
                            $studentCourseId = $record->student_course_id;
                            // Since we're not using approval statuses, just check for date_not_set status
                            $hasWaitingSchedules = Schedule::where('student_course_id', $studentCourseId)
                                ->where('status', 'date_not_set')
                                ->exists();

                            // If any schedules are waiting for approval, update the student_course status
                            if ($hasWaitingSchedules) {
                                \App\Models\StudentCourse::where('id', $studentCourseId)
                                    ->update(['status' => 'waiting_schedule']);

                                \Illuminate\Support\Facades\Log::info(
                                    'Updated student course status to waiting_schedule',
                                    ['student_course_id' => $studentCourseId]
                                );
                            }

                            // Verify values are updated correctly
                            $record->refresh();

                            // Show a success notification
                            $instructorName = $instructorChanged ?
                                Instructor::find($instructorId)->name ?? 'new instructor' : null;

                            \Filament\Notifications\Notification::make()
                                ->title('Jadwal Diperbarui')
                                ->body($instructorChanged
                                    ? "Jadwal Anda telah diperbarui dengan instruktur {$instructorName}."
                                    : 'Jadwal Anda telah berhasil diperbarui.')
                                ->success()
                                ->send();

                            // Use NotificationController to queue a notification
                            try {
                                // Get the updated schedule with fresh data
                                $record->refresh();                                // Get the student associated with this schedule
                                $student = $record->studentCourse->student;

                                // Get phone from user table, not from student table
                                $phone = null;
                                if ($student && $student->user) {
                                    $phone = $student->user->phone;
                                }

                                if ($student && $phone) {
                                    // Prepare notification data
                                    $notificationData = [
                                        'student_name' => $student->name,
                                        'course_name' => $record->studentCourse->course->name ?? 'Kursus Mengemudi',
                                        'session' => $record->for_session,
                                        'date' => $record->start_date->format('d M Y'),
                                        'time' => $record->start_date->format('H:i'),
                                        'instructor' => $record->instructor->name ?? 'Instruktur yang ditugaskan'
                                    ];

                                    // Use the static method from NotificationController
                                    \App\Http\Controllers\NotificationController::scheduleUpdate(
                                        $phone,
                                        $notificationData
                                    );

                                    \Illuminate\Support\Facades\Log::info('Schedule update notification queued', [
                                        'student_id' => $student->id,
                                        'schedule_id' => $record->id,
                                        'phone' => $phone
                                    ]);
                                } else {
                                    \Illuminate\Support\Facades\Log::warning('Could not queue notification - No student found or phone number missing', [
                                        'schedule_id' => $record->id,
                                        'student_id' => $student->id ?? null,
                                        'has_user' => $student && $student->user ? true : false,
                                        'has_phone' => $student && $student->user && !empty($student->user->phone)
                                    ]);
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error queueing schedule update notification', [
                                    'schedule_id' => $record->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                                // Don't show error to user, just log it
                            }

                            return $record;
                        } catch (\Exception $e) {
                            // Log the error
                            \Illuminate\Support\Facades\Log::error('Failed to update schedule: ' . $e->getMessage());

                            // Show error notification
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Diperbarui')
                                ->body('Terjadi kesalahan saat memperbarui jadwal Anda. Silakan coba lagi nanti.')
                                ->danger()
                                ->persistent()
                                ->send();

                            return false;
                        }
                    }),

                // Add Signature button
                Tables\Actions\Action::make('add_signature')
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Tambah Tanda Tangan')
                    ->visible(fn(Schedule $record) => $record->status === 'waiting_signature')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Tanda Tangan')
                    ->modalDescription('Dengan menambahkan tanda tangan Anda, Anda mengonfirmasi bahwa Anda telah mengikuti sesi mengemudi ini. Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Konfirmasi Tanda Tangan')
                    ->action(function (Schedule $record) {
                        try {
                            // Since we're in the Student namespace, update att_student to 1
                            $updateData = ['att_student' => 1];

                            // Check if instructor has already signed (att_instructor = 1)
                            if ($record->att_instructor == 1) {
                                // Both student and instructor have signed, mark as complete
                                $updateData['status'] = 'complete';
                            }

                            $record->update($updateData);

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

                                    \Illuminate\Support\Facades\Log::info(
                                        'Updated student course status to done - all schedules complete',
                                        ['student_course_id' => $studentCourseId]
                                    );

                                    \Filament\Notifications\Notification::make()
                                        ->title('Kursus Selesai')
                                        ->body('Selamat! Anda telah menyelesaikan semua sesi untuk kursus ini.')
                                        ->success()
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Sesi Selesai')
                                        ->body('Anda dan instruktur telah menandatangani. Sesi sekarang telah selesai.')
                                        ->success()
                                        ->send();
                                }
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Tanda Tangan Dikonfirmasi')
                                    ->body('Tanda tangan Anda telah ditambahkan. Menunggu tanda tangan instruktur untuk menyelesaikan sesi.')
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to add signature: ' . $e->getMessage());

                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Menambahkan Tanda Tangan')
                                ->body('Terjadi kesalahan. Silakan coba lagi nanti.')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
        ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSchedules::route('/'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        if (!auth()->user()) {
            // Return an empty query if there's no authenticated user
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->whereHas('studentCourse', function ($query) {
                $query->whereHas('student', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                });
            });
    }
}
