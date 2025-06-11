<?php

namespace App\Filament\Student\Resources\ScheduleResource\Pages;

use App\Filament\Student\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Models\Instructor;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Notification;

class ManageSchedules extends ManageRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),


        ];
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
