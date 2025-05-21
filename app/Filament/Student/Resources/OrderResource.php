<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\OrderResource\Pages;
use App\Filament\Student\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        // Get the authenticated user and ensure they have a student record
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        // If no student record exists, create one
        if (!$student) {
            $student = Student::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->options(\App\Models\Course::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $course = \App\Models\Course::find($state);
                            if ($course) {
                                $set('amount', $course->price);
                                $set('final_amount', $course->price);
                                $set('invoice_id', 'INV-' . time() . '-' . rand(1000, 9999));
                                $set('status', 'pending');
                            }
                        } else {
                            $set('amount', null);
                            $set('final_amount', null);
                            $set('invoice_id', null);
                            $set('status', null);
                        }
                    }),

                Forms\Components\Hidden::make('student_id')
                    ->default($student->id) // Use the actual student ID from the students table
                    ->required(),

                Forms\Components\TextInput::make('student_name')
                    ->label('Student Name')
                    ->default($student->name)
                    ->disabled()
                    ->dehydrated(false), // Don't include in form submission

                Forms\Components\TextInput::make('invoice_id')
                    ->required()
                    ->default(function () {
                        return 'INV-' . time() . '-' . rand(1000, 9999);
                    })
                    ->disabled(),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('final_amount')
                    ->numeric()
                    ->required()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    // Add a custom method to handle the "creating" event
    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->invoice_id)) {
                $order->invoice_id = 'INV-' . time() . '-' . rand(1000, 9999);
            }
            if (empty($order->status)) {
                $order->status = 'pending';
            }
        });
    }
}
