<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\ScheduleResource\Pages;
use App\Filament\Student\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
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
    protected static ?string $navigationLabel = 'My Schedules';

    protected static ?string $modelLabel = 'Driving Schedule';

    protected static ?string $pluralModelLabel = 'Driving Schedules';

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
                $query->orderByRaw('CASE WHEN start_date IS NULL THEN 1 ELSE 0 END, start_date ASC');
            })

            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Schedule ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('studentCourse.course.name')
                    ->label('Course Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('for_session')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('car.name')
                    ->label('Car')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Car not set')
                    ->badge(fn($state) => $state === null)
                    ->color(fn($state) => $state === null ? 'danger' : null),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->formatStateUsing(function ($state) {
                        if ($state === null || $state === '') {
                            return 'Date not set';
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
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => str_replace('_', ' ', ucfirst($state))),
                // Tables\Columns\TextColumn::make('instructor_approval')
                //     ->label('Instructor Approval')
                //     ->badge()
                //     ->color(fn($state) => $state ? 'success' : 'danger')
                //     ->formatStateUsing(fn($state) => $state ? 'OK' : 'Pending'),
                // Tables\Columns\TextColumn::make('admin_approval')
                //     ->label('Admin Approval')
                //     ->badge()
                //     ->color(fn($state) => $state ? 'success' : 'danger')
                //     ->formatStateUsing(fn($state) => $state ? 'OK' : 'Pending'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn(Schedule $record) => $record->start_date === null ? 'Set Date' : 'Reschedule')
                    ->form([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->minDate(now()->addDay())
                            // ->seconds(false)
                            ->helperText('Please select a date at least 1 day from now.')
                            ->validationMessages([
                                'min' => 'The date must be at least 1 day from now.',
                            ]),
                        Forms\Components\Select::make('car_id')
                            ->label('Car')
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
                            ->helperText('Optional. Only showing cars compatible with this course')
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('After submitting a new date, you will need to wait for both instructor and admin approval before the schedule is confirmed.')
                    ->modalSubmitActionLabel('Submit Schedule')
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

                        // Check if the instructor is available
                        $instructorId = $record->studentCourse->instructor_id;

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
                                        'title' => 'Session Order Error',
                                        'message' => "You must schedule session #" . ($record->for_session - 1) . " before scheduling session #" . $record->for_session . "."
                                    ];
                                }
                                // Check if current session is being scheduled before the previous session
                                else if ($startDateCarbon->lt($previousSession->start_date)) {
                                    $prevDate = $previousSession->start_date->format('M d, Y H:i');
                                    $validationErrors[] = [
                                        'title' => 'Session Order Error',
                                        'message' => "Session #" . $record->for_session . " cannot be scheduled before session #" . ($record->for_session - 1) . " ($prevDate)."
                                    ];
                                }
                            }
                        }

                        // Check if instructor has any conflicting schedules
                        $instructorConflict = Schedule::query()
                            ->where('id', '!=', $record->id)
                            ->whereNotNull('start_date')
                            ->whereHas('studentCourse', function ($query) use ($instructorId) {
                                $query->where('instructor_id', $instructorId);
                            })
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
                            $instructorName = $record->studentCourse->instructor->name ?? "Your instructor";
                            $validationErrors[] = [
                                'title' => 'Instructor Not Available',
                                'message' => "{$instructorName} is already scheduled within 3 hours of your requested time. Please select a different time."
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
                            $carName = \App\Models\Car::find($carId)->name ?? "Selected car";
                            $validationErrors[] = [
                                'title' => 'Car Not Available',
                                'message' => "The selected car ($carName) is already booked within 3 hours of your requested time. Please select a different time or car."
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
                                'title' => 'Session Already Scheduled',
                                'message' => "Session #{$sessionNumber} for \"{$courseName}\" is already scheduled on {$formattedDate}. Please choose a different date."
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
                                    ->title('Additional Issues Found')
                                    ->body("There are {$additionalErrors} more validation issues. Please address all issues before submitting.")
                                    ->warning()
                                    ->send();
                            }

                            // Halt execution and prevent form submission
                            return false;
                        }

                        // If all validations pass, then proceed with update
                        try {
                            $record->update([
                                'start_date' => $data['start_date'],
                                'car_id' => $data['car_id'],
                                'instructor_approval' => 1,
                                'admin_approval' => 1,
                                'status' => 'ready',
                            ]);

                            // Check all schedules for this student_course_id to see if any are waiting for approval
                            $studentCourseId = $record->student_course_id;
                            $hasWaitingSchedules = Schedule::where('student_course_id', $studentCourseId)
                                ->whereIn('status', [
                                    'waiting_approval',
                                    'waiting_instructor_approval',
                                    'waiting_admin_approval'
                                ])
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
                            \Filament\Notifications\Notification::make()
                                ->title('Schedule Updated')
                                ->body('Your schedule has been submitted and is awaiting approval.')
                                ->success()
                                ->send();

                            return $record;
                        } catch (\Exception $e) {
                            // Log the error
                            \Illuminate\Support\Facades\Log::error('Failed to update schedule: ' . $e->getMessage());

                            // Show error notification
                            \Filament\Notifications\Notification::make()
                                ->title('Update Failed')
                                ->body('An error occurred while updating your schedule. Please try again later.')
                                ->danger()
                                ->persistent()
                                ->send();

                            return false;
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
