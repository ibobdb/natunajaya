<?php

namespace App\Filament\Student\Resources\ScheduleResource\Pages;

use App\Filament\Student\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Models\Instructor;
use App\Models\Student;
use App\Http\Controllers\WhatsappController;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ManageSchedules extends ManageRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),


        ];
    }

    /**
     * Send WhatsApp notification for schedule update
     * 
     * @param \App\Models\Schedule $schedule
     * @return void
     */
    protected function sendScheduleUpdateNotification(Schedule $schedule)
    {
        try {
            // Try to get the student directly from the schedule
            $student = null;

            // First, try to get the student using our new accessor attribute
            if ($schedule->student) {
                $student = $schedule->student;
                Log::info("Found student from schedule's student attribute", [
                    'student_id' => $student->id,
                    'schedule_id' => $schedule->id
                ]);
            }

            // If we couldn't find a student that way, try the authenticated user
            if (!$student) {
                $user = auth()->user();
                if (!$user) {
                    Log::error("Cannot send WhatsApp notification: No authenticated user and could not find student from schedule");
                    return;
                }

                $student = $user->student;
                if (!$student) {
                    Log::error("Cannot send WhatsApp notification: User {$user->id} has no student record");
                    return;
                }
            }

            // Debug info
            Log::info("Debug: Preparing to send WhatsApp notification", [
                'user_id' => $student->user->id ?? 'unknown',
                'student_id' => $student->id,
                'schedule_id' => $schedule->id,
                'user_has_phone' => !empty($student->user->phone),
                'phone' => $student->user->phone ?? 'Not set'
            ]);

            // Check if student's user has a phone number
            if (empty($student->user->phone)) {
                Log::warning("Cannot send WhatsApp notification: Student {$student->id} has no phone number");
                return;
            }

            // Use WhatsappController to send schedule update
            $whatsappController = new WhatsappController();
            $result = $whatsappController->sendScheduleUpdateNotification($student, $schedule);

            Log::info("WhatsApp schedule update notification sent", [
                'user_id' => $student->user->id ?? 'unknown',
                'student_id' => $student->id,
                'phone' => $student->user->phone,
                'schedule_id' => $schedule->id,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error sending WhatsApp notification: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Reschedule')
                ->icon('heroicon-o-calendar')
                ->modalHeading('Request Schedule Change')
                ->form([
                    Forms\Components\DateTimePicker::make('start_date')
                        ->label('Proposed Date & Time')
                        ->required()
                        ->minDate(now()->addDay())
                        ->helperText('Please select a date at least 24 hours in advance'),
                    Forms\Components\Select::make('instructor_id')
                        ->label('Instructor')
                        ->options(function () {
                            return \App\Models\Instructor::all()->pluck('name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->default(fn(Schedule $record) => $record->instructor_id)
                        ->helperText('You can change the assigned instructor'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Any additional information for the instructor'),

                ])
                ->action(function (array $data, Schedule $record): void {
                    $record->update([
                        'start_date' => $data['start_date'],
                        'instructor_id' => $data['instructor_id'] ?? $record->instructor_id,
                        'notes' => $data['notes'] ?? null,
                        'status' => 'ready', // Always ready, no need for approval
                        'instructor_approval' => true, // Always approved
                        'admin_approval' => true, // Always approved
                    ]);

                    // Check if instructor has changed
                    $instructorChanged = isset($data['instructor_id']) && $data['instructor_id'] != $record->getOriginal('instructor_id');
                    $instructorName = $instructorChanged
                        ? Instructor::find($data['instructor_id'])->name ?? 'New instructor'
                        : null;

                    // Get the updated schedule to ensure we have the latest data
                    $updatedRecord = Schedule::find($record->id);

                    // Send WhatsApp notification about schedule change
                    Log::info("About to call sendScheduleUpdateNotification", [
                        'schedule_id' => $updatedRecord->id,
                        'authenticated_user_id' => auth()->id(),
                        'authenticated_user_type' => auth()->user() ? get_class(auth()->user()) : 'null',
                        'has_student' => auth()->user() && auth()->user()->student ? true : false
                    ]);

                    $this->sendScheduleUpdateNotification($updatedRecord);

                    Notification::make()
                        ->title('Schedule updated')
                        ->body($instructorChanged
                            ? "Your schedule has been updated with instructor change to {$instructorName}."
                            : 'Your schedule has been updated successfully.')
                        ->success()
                        ->send();
                }),
            // Add other actions if needed
        ];
    }
}
