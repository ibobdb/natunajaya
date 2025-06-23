<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
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

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_course_id')
                    ->relationship('studentCourse', 'id')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->student->name)
                    ->label('Student Name')
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('for_session')
                    ->disabled()
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('start_date')
                    ->label('Set Schedule')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('studentCourse.student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('studentCourse.course.name')
                    ->label('Course Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('studentCourse.instructor.name')
                    ->label('Instructor Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('for_session')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('duration_session'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSchedules::route('/'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
