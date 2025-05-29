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

        // Get upcoming schedules for the current student using studentCourse relationship
        $upcomingSchedules = Schedule::whereHas('studentCourse', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
            ->where('start_date', '>=', $today)
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // Get today's schedules
        $todaySchedules = Schedule::whereHas('studentCourse', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
            ->whereDate('start_date', $today)
            ->count();

        // Get this week's schedules
        $weekStart = Carbon::today()->startOfWeek();
        $weekEnd = Carbon::today()->endOfWeek();
        $weekSchedules = Schedule::whereHas('studentCourse', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })
            ->whereBetween('start_date', [$weekStart, $weekEnd])
            ->count();

        // Create stats for display
        $stats = [
            Stat::make('Today\'s Classes', $todaySchedules)
                ->description('Scheduled for today')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('This Week', $weekSchedules)
                ->description('Classes this week')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('info'),
        ];

        // Add next upcoming schedule if available
        if ($upcomingSchedules->count() > 0) {
            $nextClass = $upcomingSchedules->first();
            // Use studentCourse to get the course name instead of subject
            $stats[] = Stat::make('Next Class', $nextClass->studentCourse->course->name ?? 'Upcoming Class')
                ->description(Carbon::parse($nextClass->start_date)->format('d M Y, H:i'))
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning');
        }

        return $stats;
    }
}
