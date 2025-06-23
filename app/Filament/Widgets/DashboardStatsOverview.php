<?php

namespace App\Filament\Widgets;

use App\Models\Car;
use App\Models\Instructor;
use App\Models\Order;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsOverview extends BaseWidget
{
  protected int | string | array $columnSpan = 'full';

  protected function getStats(): array
  {
    // Total Cars
    $totalCars = Car::count();

    // Total Instructors
    $totalInstructors = Instructor::count();

    // New Students This Month
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;
    $currentMonthStudents = Student::whereMonth('created_at', $currentMonth)
      ->whereYear('created_at', $currentYear)
      ->count();

    // Student trend data
    $previousMonth = Carbon::now()->subMonth();
    $previousMonthStudents = Student::whereMonth('created_at', $previousMonth->month)
      ->whereYear('created_at', $previousMonth->year)
      ->count();

    $studentPercentageChange = $previousMonthStudents > 0
      ? (($currentMonthStudents - $previousMonthStudents) / $previousMonthStudents) * 100
      : ($currentMonthStudents > 0 ? 100 : 0);

    $studentTrendDescription = $studentPercentageChange >= 0
      ? number_format(abs($studentPercentageChange), 1) . '% increase'
      : number_format(abs($studentPercentageChange), 1) . '% decrease';

    // Monthly Income
    $currentMonthIncome = Order::where('status', 'success')
      ->whereMonth('created_at', $currentMonth)
      ->whereYear('created_at', $currentYear)
      ->sum('final_amount');

    // Income trend data
    $previousMonthIncome = Order::where('status', 'success')
      ->whereMonth('created_at', $previousMonth->month)
      ->whereYear('created_at', $previousMonth->year)
      ->sum('final_amount');

    $incomePercentageChange = $previousMonthIncome > 0
      ? (($currentMonthIncome - $previousMonthIncome) / $previousMonthIncome) * 100
      : ($currentMonthIncome > 0 ? 100 : 0);

    $incomeTrendDescription = $incomePercentageChange >= 0
      ? number_format(abs($incomePercentageChange), 1) . '% increase'
      : number_format(abs($incomePercentageChange), 1) . '% decrease';

    return [
      // Total Cars Stats
      Stat::make('Total Cars', $totalCars)
        ->description('All registered cars')
        ->descriptionIcon('heroicon-m-truck')
        ->chart([0])
        ->color('success'),

      // Total Instructors Stats
      Stat::make('Total Instructors', $totalInstructors)
        ->description('All active instructors')
        ->descriptionIcon('heroicon-m-user-group')
        ->chart([0])
        ->color('primary'),

      // New Students This Month Stats
      Stat::make('New Students This Month', $currentMonthStudents)
        ->description($studentTrendDescription . ' from previous month')
        ->descriptionIcon($studentPercentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
        ->chart($this->getMonthlyStudentChartData())
        ->color($studentPercentageChange >= 0 ? 'success' : 'danger'),

      // Monthly Income Stats
      Stat::make('Monthly Income', 'Rp ' . number_format($currentMonthIncome, 0, ',', '.'))
        ->description($incomeTrendDescription . ' from previous month')
        ->descriptionIcon($incomePercentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
        ->chart($this->getMonthlyIncomeChartData())
        ->color($incomePercentageChange >= 0 ? 'success' : 'danger'),
    ];
  }

  private function getMonthlyStudentChartData()
  {
    // Get student counts for the last 6 months
    $monthlyData = Student::select(
      DB::raw('YEAR(created_at) as year'),
      DB::raw('MONTH(created_at) as month'),
      DB::raw('COUNT(*) as count')
    )
      ->where('created_at', '>=', Carbon::now()->subMonths(6))
      ->groupBy('year', 'month')
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $chartData = $monthlyData->pluck('count')->toArray();

    if (empty($chartData)) {
      $chartData = [0]; // Default value to prevent errors
    }

    return $chartData;
  }

  private function getMonthlyIncomeChartData()
  {
    // Get income data for the last 6 months
    $monthlyData = Order::where('status', 'success')
      ->where('created_at', '>=', Carbon::now()->subMonths(6))
      ->select(
        DB::raw('YEAR(created_at) as year'),
        DB::raw('MONTH(created_at) as month'),
        DB::raw('SUM(final_amount) as total')
      )
      ->groupBy('year', 'month')
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $chartData = $monthlyData->pluck('total')->toArray();

    if (empty($chartData)) {
      $chartData = [0]; // Default value to prevent errors
    }

    return $chartData;
  }
}
