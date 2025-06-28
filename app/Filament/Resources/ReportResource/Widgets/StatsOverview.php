<?php

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\Order;
use App\Models\Course;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
  protected function getStats(): array
  {
    // Get the current month's start and end dates
    $currentMonthStart = Carbon::now()->startOfMonth();
    $currentMonthEnd = Carbon::now()->endOfMonth();

    // Get the previous month's start and end dates
    $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
    $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

    // Current month successful orders
    $currentMonthOrders = Order::where('status', 'success')
      ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
      ->count();

    // Previous month successful orders
    $previousMonthOrders = Order::where('status', 'success')
      ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
      ->count();

    // Current month total sales
    $currentMonthSales = Order::where('status', 'success')
      ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
      ->sum('final_amount');

    // Previous month total sales
    $previousMonthSales = Order::where('status', 'success')
      ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
      ->sum('final_amount');

    // All-time total sales
    $totalSales = Order::where('status', 'success')->sum('final_amount');

    // Calculate order change percentage
    $orderChangePercentage = $previousMonthOrders > 0
      ? (($currentMonthOrders - $previousMonthOrders) / $previousMonthOrders) * 100
      : 0;

    // Calculate sales change percentage
    $salesChangePercentage = $previousMonthSales > 0
      ? (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100
      : 0;

    return [
      Stat::make('Total Orders This Month', $currentMonthOrders)
        ->description($orderChangePercentage >= 0
          ? $orderChangePercentage . '% increase'
          : abs($orderChangePercentage) . '% decrease')
        ->descriptionIcon($orderChangePercentage >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
        ->color($orderChangePercentage >= 0 ? 'success' : 'danger')
        ->chart([
          $previousMonthOrders,
          $currentMonthOrders,
        ]),

      Stat::make('Total Sales This Month', 'Rp ' . number_format($currentMonthSales, 0, ',', '.'))
        ->description($salesChangePercentage >= 0
          ? $salesChangePercentage . '% increase'
          : abs($salesChangePercentage) . '% decrease')
        ->descriptionIcon($salesChangePercentage >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
        ->color($salesChangePercentage >= 0 ? 'success' : 'danger')
        ->chart([
          $previousMonthSales / 1000000,
          $currentMonthSales / 1000000,
        ]),

      Stat::make('All-time Total Sales', 'Rp ' . number_format($totalSales, 0, ',', '.')),
    ];
  }
}
