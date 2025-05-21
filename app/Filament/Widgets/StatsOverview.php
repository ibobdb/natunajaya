<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
// Uncomment and modify these based on your actual models
// use App\Models\Order;
// use App\Models\Product;

class StatsOverview extends BaseWidget
{
  protected static ?string $pollingInterval = '15s';

  protected function getStats(): array
  {
    return [
      Stat::make('Total Users', User::count())
        ->description('Total registered users')
        ->descriptionIcon('heroicon-m-arrow-trending-up')
        ->chart([7, 2, 10, 3, 15, 4, 17])
        ->color('success'),

      // Uncomment and adjust based on your actual models
      /*
            Stat::make('Total Orders', Order::count())
                ->description('Orders this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('warning'),
                
            Stat::make('Revenue', 'Rp ' . number_format(Order::sum('total'), 0, ',', '.'))
                ->description('7% increase from last month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            */

      Stat::make('Server Uptime', '99.9%')
        ->description('Last 30 days')
        ->descriptionIcon('heroicon-m-server')
        ->color('primary'),
    ];
  }
}
