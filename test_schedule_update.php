<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing ScheduleResource Update with WhatsApp Notification\n";
echo "======================================================\n\n";

try {
  // Find a schedule to update
  $schedule = \App\Models\Schedule::whereNotNull('start_date')->first();

  if (!$schedule) {
    echo "ERROR: No schedule with a start date found. Creating one instead.\n";

    // Try to find any schedule
    $schedule = \App\Models\Schedule::first();

    if (!$schedule) {
      echo "ERROR: No schedules found in database\n";
      exit(1);
    }

    // Set a start date for the schedule
    $schedule->update([
      'start_date' => now()->addDays(2),
    ]);

    echo "Created schedule with start date: " . $schedule->start_date . "\n";
  }

  echo "Found Schedule ID: " . $schedule->id . "\n";

  // Get student information
  $student = $schedule->student;

  if (!$student) {
    echo "ERROR: No student associated with this schedule\n";
    exit(1);
  }

  echo "Student: " . $student->name . " (ID: " . $student->id . ")\n";

  if (empty($student->user->phone)) {
    echo "ERROR: Student has no phone number. Adding a test phone number.\n";
    $student->user->update(['phone' => '082169072681']);
    echo "Added phone number: " . $student->user->phone . "\n";
  } else {
    echo "Student Phone: " . $student->user->phone . "\n";
  }

  // Current start date
  echo "Current Start Date: " . $schedule->start_date . "\n";

  // Update the schedule with a new start date
  $newDate = now()->addDays(3)->setHour(10)->setMinute(0)->setSecond(0);
  echo "New Start Date: " . $newDate . "\n\n";

  echo "Updating schedule...\n";

  // Find an instructor
  $instructor = \App\Models\Instructor::first();
  if (!$instructor) {
    echo "ERROR: No instructors found in database\n";
    exit(1);
  }

  // Update the schedule 
  $schedule->update([
    'start_date' => $newDate,
    'instructor_id' => $instructor->id,
    'status' => 'ready'
  ]);

  echo "Schedule updated successfully\n\n";

  // Now manually trigger the WhatsApp notification
  echo "Sending WhatsApp notification...\n";

  $whatsappController = new \App\Http\Controllers\WhatsappController();
  $result = $whatsappController->sendScheduleUpdateNotification($student, $schedule);

  echo "Notification Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

  if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === true)) {
    echo "SUCCESS: WhatsApp notification sent!\n";
  } else {
    echo "ERROR: WhatsApp notification failed.\n";
  }
} catch (\Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
