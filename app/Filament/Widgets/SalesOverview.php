<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class SalesOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    // Add filter form
    protected static ?string $pollingInterval = null;

    // Define properties for the date range filter
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $filter = 'current_month'; // Default filter to current month

    public function mount(): void
    {
        // Set default date range to current month
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Start Date')
                ->default(Carbon::now()->startOfMonth())
                ->reactive()
                ->afterStateUpdated(function () {
                    $this->filter = 'custom';
                }),

            DatePicker::make('endDate')
                ->label('End Date')
                ->default(Carbon::now()->endOfMonth())
                ->reactive()
                ->afterStateUpdated(function () {
                    $this->filter = 'custom';
                }),
        ];
    }

    public function filterCurrentMonth(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->filter = 'current_month';
    }

    public function filterPreviousMonth(): void
    {
        $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $this->filter = 'previous_month';
    }

    public function filterLast30Days(): void
    {
        $this->startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->filter = 'last_30_days';
    }

    protected function getStats(): array
    {
        // Get start and end date as Carbon objects
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        // Total sales amount for the period
        $totalSales = Order::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('final_amount');

        // Count of successful orders
        $ordersCount = Order::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Average order value
        $averageOrderValue = $ordersCount > 0 ? $totalSales / $ordersCount : 0;

        // Daily sales breakdown for trend analysis
        $dailySales = Order::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(final_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate trend percentages
        $previousPeriodStart = (clone $startDate)->subDays($endDate->diffInDays($startDate) + 1);
        $previousPeriodEnd = (clone $startDate)->subDay();

        $previousPeriodSales = Order::where('status', 'success')
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('final_amount');

        $salesTrendPercentage = $previousPeriodSales > 0
            ? (($totalSales - $previousPeriodSales) / $previousPeriodSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $salesTrendDescription = $salesTrendPercentage >= 0
            ? number_format(abs($salesTrendPercentage), 1) . '% increase'
            : number_format(abs($salesTrendPercentage), 1) . '% decrease';

        // Format dates for display
        $dateRangeString = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');

        // Ensure we have values for the chart (prevent empty chart error)
        $chartData = $dailySales->pluck('total')->toArray();
        if (empty($chartData)) {
            $chartData = [0]; // Provide a default value to avoid empty chart errors
        }

        return [
            Stat::make('Total Sales', 'Rp ' . number_format($totalSales, 0, ',', '.'))
                ->description($dateRangeString)
                ->descriptionIcon('heroicon-m-calendar')
                ->chart($chartData)
                ->color($salesTrendPercentage >= 0 ? 'success' : 'danger'),

            Stat::make('Orders', $ordersCount)
                ->description($salesTrendDescription . ' from previous period')
                ->descriptionIcon($salesTrendPercentage >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesTrendPercentage >= 0 ? 'success' : 'danger'),

            Stat::make('Average Order Value', 'Rp ' . number_format($averageOrderValue, 0, ',', '.'))
                ->description('Per order average')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),
        ];
    }

    protected function getFooter(): ?View
    {
        $activeFilterClass = 'px-3 py-1 bg-primary-600 text-white rounded-lg';
        $inactiveFilterClass = 'px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors';

        $currentMonthClass = $this->filter === 'current_month' ? $activeFilterClass : $inactiveFilterClass;
        $previousMonthClass = $this->filter === 'previous_month' ? $activeFilterClass : $inactiveFilterClass;
        $last30DaysClass = $this->filter === 'last_30_days' ? $activeFilterClass : $inactiveFilterClass;

        return view('filament.widgets.sales-overview-footer', [
            'currentMonthClass' => $currentMonthClass,
            'previousMonthClass' => $previousMonthClass,
            'last30DaysClass' => $last30DaysClass,
        ]);
    }
}
