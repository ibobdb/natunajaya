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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Tables\Columns\TextColumn::make('id')
                    ->label('Schedule ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('studentCourse.student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('studentCourse.course.name')
                    ->label('Course Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('for_session')
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
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => str_replace('_', ' ', ucfirst($state))),
                Tables\Columns\TextColumn::make('instructor_approval')
                    ->label('Instructor Approval')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => $state ? 'OK' : 'Pending'),
                Tables\Columns\TextColumn::make('admin_approval')
                    ->label('Admin Approval')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => $state ? 'OK' : 'Pending'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn(Schedule $record) => $record->start_date === null ? 'Set Date' : 'Reschedule')
                    ->form([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->minDate(now()->addDay())
                            ->helperText('Please select a date at least 1 day from now.')
                            ->validationMessages([
                                'min' => 'The date must be at least 1 day from now.',
                            ]),
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('After submitting a new date, you will need to wait for both instructor and admin approval before the schedule is confirmed.')
                    ->modalSubmitActionLabel('Submit Schedule')
                    ->afterFormFilled(function (array $data) {
                        return $data;
                    })
                    ->using(function (Schedule $record, array $data) {
                        $record->update([
                            'start_date' => $data['start_date'],
                            'instructor_approval' => 0,
                            'admin_approval' => 0,
                            'status' => 'waiting_approval',
                        ]);

                        // Verify values are updated correctly
                        $record->refresh();

                        // Show notification
                        \Filament\Notifications\Notification::make()
                            ->title('Schedule Updated')
                            ->body('Your schedule has been changed and is waiting for instructor and admin approval.')
                            ->success()
                            ->send();

                        return $record;
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
        return parent::getEloquentQuery()
            ->whereHas('studentCourse', function ($query) {
                $query->whereHas('student', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                });
            });
    }
}
