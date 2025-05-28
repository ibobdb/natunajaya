<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Car;
use App\Models\Schedule;
use App\Models\Instructor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $data = [
            'courses' => $this->getCourses(),
            'cars' => $this->getCars(),
        ];

        return view('welcome', $data);
    }

    private function getCourses()
    {
        try {
            // Logic to retrieve courses
            $courses = Course::all(); // Assuming you have a Course model
            return $courses;
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error retrieving courses: ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }
    }
    private function getCars()
    {
        try {
            // Logic to retrieve cars
            $cars = Car::all(); // Assuming you have a Car model
            return $cars;
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error retrieving cars: ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }
    }
    public function checkSchedule(Request $request)
    {
        try {
            // Log the incoming request data
            \Log::info('Schedule check requested', [
                'date' => $request->input('date'),
                'time_period' => $request->input('time'),
                'request_all' => $request->all()
            ]);

            // get data
            $date = Carbon::parse($request->input('date'));
            \Log::debug('Parsed date', ['date_parsed' => $date->toDateTimeString()]);

            $time = $request->input('time');
            \Log::debug('Time received', ['time' => $time]);

            // Convert date and time to string format
            $dateString = $date->format('Y-m-d');
            $combinedDateTime = $dateString . ' ' . $time;
            \Log::debug('Combined datetime', ['combined' => $combinedDateTime]);

            $starDate = Carbon::parse($combinedDateTime);
            \Log::debug('Start date parsed', ['start_date' => $starDate->toDateTimeString()]);

            $endDate = $starDate->copy()->addHours(3)->format('Y-m-d H:i:s');
            \Log::debug('End date calculated', ['end_date' => $endDate]);

            \Log::debug('Executing schedule query', [
                'start_date' => $starDate,
                'end_date' => $endDate
            ]);

            $getSchedule = Schedule::where('start_date', '>=', $starDate)
                ->where('start_date', '<=', $endDate)
                ->get();

            \Log::info('Schedule query complete', [
                'found_records' => $getSchedule->count(),
                'records' => $getSchedule->toArray()
            ]);
            if ($getSchedule->isEmpty()) {
                \Log::info('No schedules found for the given date and time');
            } else {
                \Log::info('Schedules found', ['schedules' => $getSchedule->toArray()]);
            }

            // Get all cars and instructors except those already in schedules
            $allCars = Car::all();
            $allInstructors = Instructor::all();

            // Extract car_id and instructor_id from schedules
            $bookedCarIds = $getSchedule->pluck('car_id')->toArray();
            $bookedInstructorIds = $getSchedule->pluck('instructor_id')->toArray();

            // Filter out booked cars and instructors
            $availableCars = $allCars->whereNotIn('id', $bookedCarIds);
            $availableInstructors = $allInstructors->whereNotIn('id', $bookedInstructorIds);

            // If no schedules found for the time slot, all cars and instructors are available
            if ($getSchedule->isEmpty()) {
                \Log::info('No schedules found, all cars and instructors are available');
                $availableCars = $allCars;
                $availableInstructors = $allInstructors;
            }

            // Log availability information
            \Log::info('Availability results', [
                'available_cars' => $availableCars->count(),
                'available_instructors' => $availableInstructors->count()
            ]);

            // Check if schedule is available based on cars and instructors
            if ($availableCars->count() <= 0 || $availableInstructors->count() <= 0) {
                \Log::warning('Schedule unavailable', [
                    'date' => $dateString,
                    'time' => $time,
                    'reason' => $availableCars->count() <= 0 ? 'No cars available' : 'No instructors available',
                    'available_cars_count' => $availableCars->count(),
                    'available_instructors_count' => $availableInstructors->count()
                ]);

                return response()->json([
                    'status' => 'unavailable',
                    'date' => $dateString,
                    'time' => $time,
                    'message' => 'Schedule is not available. No cars or instructors available for this time slot.',
                    'data' => [
                        'available_cars' => $availableCars->count(),
                        'available_instructors' => $availableInstructors->count()
                    ]
                ]);
            }

            // Log successful availability check
            \Log::info('Schedule available', [
                'date' => $dateString,
                'time' => $time,
                'available_cars_count' => $availableCars->count(),
                'available_instructors_count' => $availableInstructors->count(),
                'available_car_ids' => $availableCars->pluck('name')->toArray(),
                'available_instructor_ids' => $availableInstructors->pluck('name')->toArray()
            ]);

            // Return available status with available cars and instructors
            return response()->json([
                'status' => 'available',
                'message' => 'Schedule is available',
                'date' => $dateString,
                'time' => $time,
                'available_cars' => $availableCars->pluck('name')->toArray(),
                'available_instructors' => $availableInstructors->pluck('name')->toArray()
            ]);
        } catch (\Exception $e) {
            // Log the error with detailed information
            \Log::error('Error checking schedule availability', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while checking schedule availability. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
