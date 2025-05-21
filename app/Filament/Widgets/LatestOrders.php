<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
// Uncomment and change to your actual Order model
// use App\Models\Order;

class LatestOrders extends BaseWidget
{
  protected static ?string $heading = 'Latest Orders';

  protected int | string | array $columnSpan = 'full';

  public function table(Table $table): Table
  {
    return $table
      // Change the model based on your application
      // ->query(Order::latest()->limit(5))
      ->query(\App\Models\User::latest()->limit(5)) // Using User as a placeholder
      ->columns([
        Tables\Columns\TextColumn::make('id')
          ->label('Order ID'),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Date')
          ->dateTime(),
        Tables\Columns\TextColumn::make('name')
          ->label('Customer'),
        // Add more relevant columns for your Order model
        Tables\Columns\TextColumn::make('email')
          ->label('Status')
          ->badge()
          ->color('success'),
      ]);
  }
}
