<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Course;

class OrderController extends Controller
{
    //
    public static function index()
    {
        $data = [
            'courses' => \App\Models\Course::with('teachers')->get(),
            'cars' => request('type') ? \App\Models\Car::where('type', request('type'))->get() : \App\Models\Car::all(),

        ];
        return view('users.orders.index', $data);
    }

    public function getCars(Request $request)
    {
        $type = $request->query('type');
        $cars = Car::when($type, function ($query) use ($type) {
            return $query->where('type', $type);
        })->get();

        return response()->json($cars);
    }
    public function getTeachers(Request $request)
    {
        $courseId = $request->query('course_id');

        if ($courseId) {
            $course = Course::with('teachers')->find($courseId);
            if ($course) {
                return response()->json($course->teachers);
            }
        }

        return response()->json([]);
    }
    public function checkAvailability(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date_format:Y-m-d',
                'time' => 'required|date_format:H:i:s',
                'car_id' => 'required|exists:cars,id',
                'teacher_id' => 'required|exists:courses,id',
            ]);

            $date = $validated['date'];
            $time = $validated['time'];
            $carId = $validated['car_id'];
            $teacherId = $validated['teacher_id'];

            // Format date and time for comparison
            $requestDate = $date;
            $requestTime = substr($time, 0, 5); // Extract HH:MM from the time

            // Convert request time to minutes for easier comparison
            $requestHour = (int) substr($requestTime, 0, 2);
            $requestMinute = (int) substr($requestTime, 3, 2);
            $requestTimeInMinutes = $requestHour * 60 + $requestMinute;

            // Calculate start and end of the 1-hour window
            $windowStartMinutes = $requestTimeInMinutes;
            $windowEndMinutes = $requestTimeInMinutes + 60;

            // Log for debugging
            \Log::info('Checking availability for:', [
                'date' => $requestDate,
                'time' => $requestTime,
                'time_window' => [
                    'start' => floor($windowStartMinutes / 60) . ':' . ($windowStartMinutes % 60),
                    'end' => floor($windowEndMinutes / 60) . ':' . ($windowEndMinutes % 60)
                ],
                'car_id' => $carId,
                'teacher_id' => $teacherId
            ]);

            // Get schedules for the selected date
            $schedules = \App\Models\Schedule::where('date', $requestDate);

            // Check car availability
            $carAvailable = true;
            if ($carId) {
                $carSchedules = clone $schedules;
                $carSchedules = $carSchedules->where('car_id', $carId)->get();

                foreach ($carSchedules as $schedule) {
                    // Convert schedule time to minutes
                    $scheduleTime = $schedule->time->format('H:i');
                    $scheduleHour = (int) substr($scheduleTime, 0, 2);
                    $scheduleMinute = (int) substr($scheduleTime, 3, 2);
                    $scheduleTimeInMinutes = $scheduleHour * 60 + $scheduleMinute;

                    // Calculate schedule's 1-hour window
                    $scheduleEndMinutes = $scheduleTimeInMinutes + 60;

                    // Check for overlap:
                    // 1. Request window starts during schedule window
                    // 2. Request window ends during schedule window
                    // 3. Request window completely contains schedule window
                    // 4. Schedule window completely contains request window
                    if (
                        ($windowStartMinutes >= $scheduleTimeInMinutes && $windowStartMinutes < $scheduleEndMinutes) ||
                        ($windowEndMinutes > $scheduleTimeInMinutes && $windowEndMinutes <= $scheduleEndMinutes) ||
                        ($windowStartMinutes <= $scheduleTimeInMinutes && $windowEndMinutes >= $scheduleEndMinutes) ||
                        ($scheduleTimeInMinutes <= $windowStartMinutes && $scheduleEndMinutes >= $windowEndMinutes)
                    ) {
                        $carAvailable = false;
                        \Log::info('Car unavailable due to schedule:', [
                            'schedule_id' => $schedule->id,
                            'schedule_time' => $scheduleTime,
                            'schedule_window' => [
                                'start' => $scheduleTime,
                                'end' => floor($scheduleEndMinutes / 60) . ':' . ($scheduleEndMinutes % 60)
                            ]
                        ]);
                        break;
                    }
                }
            }

            // Check teacher availability
            $teacherAvailable = true;
            if ($teacherId) {
                $teacherSchedules = clone $schedules;
                $teacherSchedules = $teacherSchedules->where('teacher_id', $teacherId)->get();

                foreach ($teacherSchedules as $schedule) {
                    // Convert schedule time to minutes
                    $scheduleTime = $schedule->time->format('H:i');
                    $scheduleHour = (int) substr($scheduleTime, 0, 2);
                    $scheduleMinute = (int) substr($scheduleTime, 3, 2);
                    $scheduleTimeInMinutes = $scheduleHour * 60 + $scheduleMinute;

                    // Calculate schedule's 1-hour window
                    $scheduleEndMinutes = $scheduleTimeInMinutes + 60;

                    // Check for overlap using the same logic as above
                    if (
                        ($windowStartMinutes >= $scheduleTimeInMinutes && $windowStartMinutes < $scheduleEndMinutes) ||
                        ($windowEndMinutes > $scheduleTimeInMinutes && $windowEndMinutes <= $scheduleEndMinutes) ||
                        ($windowStartMinutes <= $scheduleTimeInMinutes && $windowEndMinutes >= $scheduleEndMinutes) ||
                        ($scheduleTimeInMinutes <= $windowStartMinutes && $scheduleEndMinutes >= $windowEndMinutes)
                    ) {
                        $teacherAvailable = false;
                        \Log::info('Teacher unavailable due to schedule:', [
                            'schedule_id' => $schedule->id,
                            'schedule_time' => $scheduleTime,
                            'schedule_window' => [
                                'start' => $scheduleTime,
                                'end' => floor($scheduleEndMinutes / 60) . ':' . ($scheduleEndMinutes % 60)
                            ]
                        ]);
                        break;
                    }
                }
            }

            // Log results for debugging
            \Log::info('Availability results:', [
                'car_available' => $carAvailable,
                'teacher_available' => $teacherAvailable
            ]);

            return response()->json([
                'car_available' => $carAvailable,
                'teacher_available' => $teacherAvailable,
                'available' => $carAvailable && $teacherAvailable
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in checkAvailability: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'order_date' => 'required|date',
                'class_id' => 'required|exists:courses,id',
                'transmission_type' => 'required|in:manual,matic',
                'car_id' => 'required|exists:cars,id',
                'teacher_id' => 'required|exists:teachers,id',
                'availability_checked' => 'required|in:1', // Ensure availability was checked
            ]);
            // dd($validated);

            // Begin transaction
            \DB::beginTransaction();

            // Format date and time for the schedule
            $orderDateTime = new \DateTime($validated['order_date']);
            $date = $orderDateTime->format('Y-m-d');
            $time = $orderDateTime->format('H:i:s');

            // Create a new schedule
            $schedule = new \App\Models\Schedule();
            $schedule->date = $date;
            $schedule->time = $time;
            $schedule->class_id = $validated['class_id'];
            $schedule->car_id = $validated['car_id'];
            $schedule->teacher_id = $validated['teacher_id']; // Add the teacher_id to the schedule
            $schedule->save();

            // get course
            $course = \App\Models\Course::find($validated['class_id']);
            $amount = $course->price;
            $final_amount = $amount;
            // Generate a UUID for the invoice
            $invoice_id = \Illuminate\Support\Str::uuid()->toString();

            // Create a new order
            $order = new \App\Models\Order();
            $order->user_id = auth()->id();
            $order->invoice_id = $invoice_id; // Add the invoice_id to the order
            $order->amount = $amount;
            $order->schedule_id = $schedule->id; // Add the schedule_id to the order
            $order->car_id = $validated['car_id'];
            $order->final_amount = $final_amount;
            $order->start_date = $date;
            $order->teacher_id = $validated['teacher_id'];
            $order->course_id = $validated['class_id']; // Add the course_id from the selected class
            $order->status = 'pending';
            $order->save();

            // Commit the transaction
            \DB::commit();

            return redirect()->route('orders.index')->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Rollback the transaction in case of error
            \DB::rollBack();
            \Log::error('Error creating order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create order. Please try again.');
        }
    }
}
