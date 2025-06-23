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
    protected static ?string $navigationLabel = 'Kursus';
    protected static ?string $modelLabel = 'Kursus';
    protected static ?string $pluralModelLabel = 'Kursus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('session')
                    ->label('Sesi')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('duration_session')
                    ->label('Durasi Sesi')
                    ->required()
                    ->options([
                        'week' => 'Minggu',
                        'month' => 'Bulan',
                        'year' => 'Tahun',
                    ])
                    ->default('week'),
                Forms\Components\TextInput::make('duration')
                    ->label('Durasi')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->label('Harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\Select::make('default_car_type')
                    ->label('Tipe Mobil Default')
                    ->options([
                        'matic' => 'Matic',
                        'manual' => 'Manual',
                    ])
                    ->required()
                    ->default('matic')
                    ->live(),
                Forms\Components\Select::make('default_car')
                    ->label('Mobil Default')
                    ->options(function (callable $get) {
                        $type = $get('default_car_type');
                        if (!$type) {
                            return [];
                        }
                        return \App\Models\Car::where('type', $type)->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('expired')
                    ->label('Kedaluwarsa'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('session')
                    ->label('Sesi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_session')
                    ->label('Durasi Sesi'),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Durasi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    // ->money()
                    ->label('Harga')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expired')
                    ->label('Kedaluwarsa')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
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
