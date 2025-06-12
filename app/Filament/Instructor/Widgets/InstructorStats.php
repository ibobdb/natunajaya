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
                Stat::make('Not Logged In', 'Please log in')
                    ->description('Session may have expired')
                    ->color('danger'),
            ];
        }

        // Find the instructor associated with this user
        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return [
                Stat::make('Instructor Error', 'Not connected')
                    ->description('Your user account is not linked to an instructor profile')
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
            Stat::make('Completed Sessions', $completedSchedulesCount)
                ->description('Total completed driving sessions')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Teaching Hours', $totalHours)
                ->description('Total hours teaching (2hrs per session)')
                ->descriptionIcon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('Upcoming Sessions', $upcomingSchedulesCount)
                ->description('Future scheduled sessions')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('Today\'s Sessions', $todaySchedulesCount)
                ->description('Sessions scheduled for today')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),
        ];
    }
}
