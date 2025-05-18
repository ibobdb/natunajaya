<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('type')
                    ->default('Online')
                    ->maxLength(255),
                Forms\Components\Select::make('levels')
                    ->required()
                    ->options([
                        'beginner' => 'Beginner Level',
                        'intermediate' => 'Intermediate Level',
                        'advanced' => 'Advanced Level',
                    ])
                    ->native(false),
                Forms\Components\TextInput::make('available_teachers')
                    ->required()
                    ->disabled()
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('teachers')
                    ->relationship('teachers', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Available Teachers')
                    ->placeholder('Select teachers')
                    ->helperText('Select teachers available for this course')
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Update the available teachers count based on selected teachers
                        $set('available_teachers', count($state));
                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('levels'),
                Tables\Columns\TextColumn::make('available_teachers')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teachers')
                    ->getStateUsing(function (Course $record) {
                        return $record->teachers->pluck('name')->implode(', ');
                    })
                    ->searchable()
                    ->label('Available Teachers'),
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
                Tables\Actions\DeleteAction::make(),
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
