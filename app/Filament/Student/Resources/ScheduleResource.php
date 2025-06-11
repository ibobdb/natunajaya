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
                    ->label('Course Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('for_session')
                    ->label('Session')
                    ->numeric()
                    ->sortable(),
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
                        'waiting_signature' => 'warning',
                        'complete' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => str_replace('_', ' ', ucfirst($state))),
                Tables\Columns\IconColumn::make('att_student')
                    ->label('Student Signature')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('att_instructor')
                    ->label('Instructor Signature')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

            ])
            ->actions([
                // View action for showing schedule details
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn(Schedule $record) => 'Driving Schedule #' . $record->id)
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('View Details')
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
                            ->tooltip('Add Signature')
                            ->visible(fn(Schedule $record) => $record->status === 'waiting_signature' && $record->att_student != 1)
                            ->requiresConfirmation()
                            ->modalHeading('Confirm Signature')
                            ->modalDescription('By adding your signature, you confirm that you attended this driving session. This cannot be undone.')
                            ->modalSubmitActionLabel('Confirm Signature')
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
                                            ->title('Course Completed')
                                            ->body('Congratulations! You have completed all sessions for this course.')
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Session Completed')
                                            ->body('Both you and the instructor have signed. The session is now complete.')
                                            ->success()
                                            ->send();
                                    }
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Signature Confirmed')
                                        ->body('Your signature has been added. Waiting for instructor signature to complete the session.')
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
                            ->label('Start Date')
                            ->required()
                            ->minDate(now()->addDay())
                            // ->seconds(false)
                            ->helperText('Please select a date at least 1 day from now.')
                            ->validationMessages([
                                'min' => 'The date must be at least 1 day from now.',
                            ]),
                        Forms\Components\Select::make('instructor_id')
                            ->label('Instructor')
                            ->options(function () {
                                return \App\Models\Instructor::all()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->default(fn(Schedule $record) => $record->instructor_id)
                            ->helperText('Choose an instructor for this session'),
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
                    ->modalDescription('Please confirm the schedule details before submitting.')
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
                                Instructor::find($data['instructor_id'])->name ?? "Selected instructor" :
                                $record->studentCourse->instructor->name ?? "Your instructor";
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
                                ->title('Schedule Updated')
                                ->body($instructorChanged
                                    ? "Your schedule has been updated with instructor {$instructorName}."
                                    : 'Your schedule has been updated successfully.')
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

                // Add Signature button
                Tables\Actions\Action::make('add_signature')
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->iconButton()
                    ->color('success')
                    ->tooltip('Add Signature')
                    ->visible(fn(Schedule $record) => $record->status === 'waiting_signature')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Signature')
                    ->modalDescription('By adding your signature, you confirm that you attended this driving session. This cannot be undone.')
                    ->modalSubmitActionLabel('Confirm Signature')
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
                                        ->title('Course Completed')
                                        ->body('Congratulations! You have completed all sessions for this course.')
                                        ->success()
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Session Completed')
                                        ->body('Both you and the instructor have signed. The session is now complete.')
                                        ->success()
                                        ->send();
                                }
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Signature Confirmed')
                                    ->body('Your signature has been added. Waiting for instructor signature to complete the session.')
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to add signature: ' . $e->getMessage());

                            \Filament\Notifications\Notification::make()
                                ->title('Failed to Add Signature')
                                ->body('An error occurred. Please try again later.')
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
