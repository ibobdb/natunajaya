<?php

namespace App\Filament\Student\Resources\ScheduleResource\Pages;

use App\Filament\Student\Resources\ScheduleResource;
use App\Models\Schedule;
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
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Any additional information for the instructor'),
                ])
                ->action(function (array $data, Schedule $record): void {
                    $record->update([
                        'start_date' => $data['start_date'],
                        'notes' => $data['notes'] ?? null,
                        'status' => 'waiting_approval',
                        'instructor_approval' => false,
                        'admin_approval' => false,
                    ]);

                    Notification::make()
                        ->title('Schedule updated')
                        ->body('Your reschedule request has been submitted for approval.')
                        ->success()
                        ->send();
                }),
            // Add other actions if needed
        ];
    }
}
