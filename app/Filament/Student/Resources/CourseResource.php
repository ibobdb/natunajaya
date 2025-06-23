<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\CourseResource\Pages;
use App\Filament\Student\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use App\Models\StudentCourse;
use App\Models\Testimoni;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class CourseResource extends Resource
{
    protected static ?string $model = StudentCourse::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Kursus Saya';

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('student', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->where('status', '!=', 'done')
            ->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Nama Kursus')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('course.session')
                    ->label('Semua Sesi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'waiting_schedule' => 'warning',
                        'schedule_not_set' => 'danger',
                        'done' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('upcoming_schedule')
                    ->label('Jadwal Selanjutnya')
                    ->state(function (StudentCourse $record) {
                        $upcomingSchedule = \App\Models\Schedule::where('student_course_id', $record->id)
                            ->where('start_date', '>', now())
                            ->orderBy('start_date', 'asc')
                            ->first();

                        return $upcomingSchedule ? $upcomingSchedule->start_date : null;
                    })
                    ->date('d M Y - H:i')
                    ->badge()
                    ->color('success')
                    ->placeholder('Belum ada jadwal'),
                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Instruktur')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_schedule')
                    ->label('Detail Jadwal')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->modalHeading(fn(StudentCourse $record) => "Schedule for {$record->course->name}")
                    ->modalContent(function (StudentCourse $record) {
                        $schedules = \App\Models\Schedule::where('student_course_id', $record->id)->get();
                        if ($schedules->isEmpty()) {
                            return 'No schedules found for this course.';
                        }
                        return view('filament.student.schedules-list', [
                            'schedules' => $schedules,
                        ]);
                    })
                    ->modalSubmitActionLabel('Open Schedule Page')
                    ->modalCancelAction(false)
                    ->action(function (StudentCourse $record) {
                        return redirect()->route('filament.student.resources.schedules.index');
                    }),

                Tables\Actions\Action::make('create_testimony')
                    ->label('Buat Testimoni')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(function () {
                        // Check if user already created a testimony
                        $user = auth()->user();
                        if ($user->isTestimoni === 1) {
                            return false;
                        }

                        // Check if user has at least one completed course
                        $hasCompletedCourse = StudentCourse::whereHas('student', function (Builder $query) {
                            $query->where('user_id', auth()->id());
                        })
                            ->where('status', 'done')
                            ->exists();

                        return $hasCompletedCourse;
                    })
                    ->form([
                        Textarea::make('content')
                            ->label('Testimoni Anda')
                            ->required()
                            ->minLength(10)
                            ->maxLength(500),
                        Select::make('rating')
                            ->label('Rating')
                            ->required()
                            ->options([
                                1 => '⭐ Poor',
                                2 => '⭐⭐ Fair',
                                3 => '⭐⭐⭐ Good',
                                4 => '⭐⭐⭐⭐ Very Good',
                                5 => '⭐⭐⭐⭐⭐ Excellent'
                            ])
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();

                        // Double check if user hasn't created a testimony yet
                        if ($user->isTestimoni === 1) {
                            Notification::make()
                                ->title('Anda sudah pernah mengirimkan testimoni')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Create testimony
                        Testimoni::create([
                            'user_id' => $user->id,
                            'content' => $data['content'],
                            'rating' => $data['rating'],
                            'is_active' => true,
                        ]);

                        // Update user isTestimoni flag
                        $user->update(['isTestimoni' => 1]);

                        Notification::make()
                            ->title('Testimoni berhasil dikirim')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCourses::route('/'),
        ];
    }
}
