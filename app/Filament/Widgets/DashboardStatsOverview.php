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
    // Total Mobil
    $totalCars = Car::count();

    // Total Instruktur
    $totalInstructors = Instructor::count();

    // Siswa Baru Bulan Ini
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;
    $currentMonthStudents = Student::whereMonth('created_at', $currentMonth)
      ->whereYear('created_at', $currentYear)
      ->count();

    // Data tren siswa
    $previousMonth = Carbon::now()->subMonth();
    $previousMonthStudents = Student::whereMonth('created_at', $previousMonth->month)
      ->whereYear('created_at', $previousMonth->year)
      ->count();

    $studentPercentageChange = $previousMonthStudents > 0
      ? (($currentMonthStudents - $previousMonthStudents) / $previousMonthStudents) * 100
      : ($currentMonthStudents > 0 ? 100 : 0);

    $studentTrendDescription = $studentPercentageChange >= 0
      ? number_format(abs($studentPercentageChange), 1) . '% peningkatan'
      : number_format(abs($studentPercentageChange), 1) . '% penurunan';

    // Pendapatan Bulanan
    $currentMonthIncome = Order::where('status', 'success')
      ->whereMonth('created_at', $currentMonth)
      ->whereYear('created_at', $currentYear)
      ->sum('final_amount');

    // Data tren pendapatan
    $previousMonthIncome = Order::where('status', 'success')
      ->whereMonth('created_at', $previousMonth->month)
      ->whereYear('created_at', $previousMonth->year)
      ->sum('final_amount');

    $incomePercentageChange = $previousMonthIncome > 0
      ? (($currentMonthIncome - $previousMonthIncome) / $previousMonthIncome) * 100
      : ($currentMonthIncome > 0 ? 100 : 0);

    $incomeTrendDescription = $incomePercentageChange >= 0
      ? number_format(abs($incomePercentageChange), 1) . '% peningkatan'
      : number_format(abs($incomePercentageChange), 1) . '% penurunan';

    return [
      // Statistik Total Mobil
      Stat::make('Total Mobil', $totalCars)
        ->description('Semua mobil terdaftar')
        ->descriptionIcon('heroicon-m-truck')
        ->chart([0])
        ->color('success'),

      // Statistik Total Instruktur
      Stat::make('Total Instruktur', $totalInstructors)
        ->description('Semua instruktur aktif')
        ->descriptionIcon('heroicon-m-user-group')
        ->chart([0])
        ->color('primary'),

      // Statistik Siswa Baru Bulan Ini
      Stat::make('Siswa Baru Bulan Ini', $currentMonthStudents)
        ->description($studentTrendDescription . ' dari bulan sebelumnya')
        ->descriptionIcon($studentPercentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
        ->chart($this->getMonthlyStudentChartData())
        ->color($studentPercentageChange >= 0 ? 'success' : 'danger'),

      // Statistik Pendapatan Bulanan
      Stat::make('Pendapatan Bulanan', 'Rp ' . number_format($currentMonthIncome, 0, ',', '.'))
        ->description($incomeTrendDescription . ' dari bulan sebelumnya')
        ->descriptionIcon($incomePercentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
        ->chart($this->getMonthlyIncomeChartData())
        ->color($incomePercentageChange >= 0 ? 'success' : 'danger'),
    ];
  }

  private function getMonthlyStudentChartData()
  {
    // Dapatkan jumlah siswa untuk 6 bulan terakhir
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
      $chartData = [0]; // Nilai default untuk mencegah error
    }

    return $chartData;
  }

  private function getMonthlyIncomeChartData()
  {
    // Dapatkan data pendapatan untuk 6 bulan terakhir
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
      $chartData = [0]; // Nilai default untuk mencegah error
    }

    return $chartData;
  }
}
