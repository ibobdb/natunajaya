<?php

namespace App\Filament\Student\Widgets;

use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ScheduleOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $student = Auth::user();
        $today = Carbon::today();

        // Dapatkan jadwal mendatang untuk siswa saat ini menggunakan relasi studentCourse
        $upcomingSchedules = Schedule::whereHas('studentCourse', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
            ->where('start_date', '>=', $today)
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // Dapatkan jadwal hari ini
        $todaySchedules = Schedule::whereHas('studentCourse', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
            ->whereDate('start_date', $today)
            ->count();

        // Dapatkan jadwal minggu ini
        $weekStart = Carbon::today()->startOfWeek();
        $weekEnd = Carbon::today()->endOfWeek();
        $weekSchedules = Schedule::whereHas('studentCourse', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
            ->whereBetween('start_date', [$weekStart, $weekEnd])
            ->count();

        // Buat statistik untuk ditampilkan
        $stats = [
            Stat::make('Kelas Hari Ini', $todaySchedules)
                ->description('Terjadwal untuk hari ini')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('Minggu Ini', $weekSchedules)
                ->description('Kelas minggu ini')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('info'),
        ];

        // Tambahkan jadwal mendatang berikutnya jika tersedia
        if ($upcomingSchedules->count() > 0) {
            $nextClass = $upcomingSchedules->first();
            // Gunakan studentCourse untuk mendapatkan nama kursus daripada subjek
            $stats[] = Stat::make('Kelas Berikutnya', $nextClass->studentCourse->course->name ?? 'Kelas Mendatang')
                ->description(Carbon::parse($nextClass->start_date)->format('d M Y, H:i'))
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning');
        }

        return $stats;
    }
}
