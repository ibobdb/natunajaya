<?php

namespace App\Filament\Instructor\Widgets;

use App\Models\Instructor;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstructorStats extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Get the currently logged-in user
        $user = auth()->user();

        if (!$user) {
            // User isn't logged in or session expired
            return [
                Stat::make('Belum Masuk', 'Silakan masuk')
                    ->description('Sesi mungkin telah berakhir')
                    ->color('danger'),
            ];
        }

        // Find the instructor associated with this user
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return [
                Stat::make('Error Instruktur', 'Tidak terhubung')
                    ->description('Akun pengguna Anda tidak terhubung ke profil instruktur')
                    ->color('danger'),
            ];
        }

        // Get completed schedules count
        $completedSchedulesCount = Schedule::where('instructor_id', $instructor->id)
            ->where('status', 'complete')
            ->count();

        // Calculate total teaching hours (2 hours per completed schedule)
        $totalHours = $completedSchedulesCount * 2;

        // Get upcoming schedules (future schedules that aren't completed)
        $upcomingSchedulesCount = Schedule::where('instructor_id', $instructor->id)
            ->where('status', '!=', 'complete')
            ->where('start_date', '>=', Carbon::now())
            ->count();

        // Get today's schedules
        $todaySchedulesCount = Schedule::where('instructor_id', $instructor->id)
            ->whereDate('start_date', Carbon::today())
            ->count();

        return [
            Stat::make('Sesi Selesai', $completedSchedulesCount)
                ->description('Total sesi mengemudi yang selesai')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Jam Mengajar', $totalHours)
                ->description('Total jam mengajar (2 jam per sesi)')
                ->descriptionIcon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('Sesi Mendatang', $upcomingSchedulesCount)
                ->description('Sesi terjadwal di masa depan')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('Sesi Hari Ini', $todaySchedulesCount)
                ->description('Sesi terjadwal untuk hari ini')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),
        ];
    }
}
