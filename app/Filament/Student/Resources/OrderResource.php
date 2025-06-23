<?php

namespace App\Filament\Student\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Student;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Student\Resources\OrderResource\Pages;
use App\Filament\Student\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $label = 'Transaksi';

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
                    ->label('Kursus')
                    ->options(function () {
                        return \App\Models\Course::all()->map(function ($course) {
                            return [
                                'id' => $course->id,
                                'label' => "{$course->name} ({$course->category})"
                            ];
                        })->pluck('label', 'id');
                    })
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
                                $set('session', $course->session);
                                $set('status', 'pending');
                                $set('description', $course->description);
                            }
                        } else {
                            $set('amount', null);
                            $set('final_amount', null);
                            $set('invoice_id', null);
                            $set('status', null);
                            $set('description', null);
                        }
                    }),

                Forms\Components\Hidden::make('student_id')
                    ->default($student->id) // Use the actual student ID from the students table
                    ->required(),

                Forms\Components\TextInput::make('student_name')
                    ->label('Nama Siswa')
                    ->default($student->name)
                    ->disabled()
                    ->dehydrated(false), // Don't include in form submission

                Forms\Components\TextInput::make('invoice_id')
                    ->required()
                    ->hidden()
                    ->default(function () {
                        return 'INV-' . time() . '-' . rand(1000, 9999);
                    })
                    ->disabled(),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('session')
                    ->label('Sesi')
                    ->required()
                    ->label('Pertemuan')
                    ->disabled(),
                Forms\Components\TextInput::make('final_amount')
                    ->label('Harga')
                    ->numeric()
                    ->required()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->recordUrl(fn(Order $record): string => route('filament.student.pages.payment', ['inv' => $record->invoice_id]))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_id')
                    ->label('Nomor Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Harga Awal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Harga Akhir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Actions\EditAction::make()
                    ->visible(fn(Order $record) => $record->status !== 'success'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(Order $record) => $record->status !== 'success')
                    ->action(function (Order $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Pesanan berhasil dihapus')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
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
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('student', function ($query) {
                $query->where('user_id', auth()->user()->id);
            });
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
