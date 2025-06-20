<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing ManageSchedules - Reschedule with WhatsApp Notification\n";
echo "==========================================================\n\n";

try {
  // Find a schedule to reschedule
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
  echo "Student Phone: " . ($student->user->phone ?? "Not set") . "\n";

  if (empty($student->user->phone)) {
    echo "ERROR: Student has no phone number. Adding a test phone number.\n";
    $student->user->update(['phone' => '082169072681']);
    echo "Added phone number: " . $student->user->phone . "\n";
  }

  // Current start date
  echo "Current Start Date: " . $schedule->start_date . "\n";

  // Update the schedule with a new start date
  $newDate = now()->addDays(4)->setHour(15)->setMinute(30)->setSecond(0);
  echo "New Start Date: " . $newDate . "\n\n";

  // Find an instructor
  $instructor = \App\Models\Instructor::first();
  if (!$instructor) {
    echo "ERROR: No instructors found in database\n";
    exit(1);
  }

  echo "Updating schedule...\n";

  // Simulate form data from Filament
  $data = [
    'start_date' => $newDate,
    'instructor_id' => $instructor->id,
    'notes' => 'Test reschedule from script',
  ];

  // Update the record manually
  $schedule->update([
    'start_date' => $data['start_date'],
    'instructor_id' => $data['instructor_id'],
    'notes' => $data['notes'],
    'status' => 'ready',
    'instructor_approval' => true,
    'admin_approval' => true,
  ]);

  // Get updated record
  $updatedRecord = \App\Models\Schedule::find($schedule->id);

  // Directly call the notification method from ManageSchedules
  $manageSchedules = new \App\Filament\Student\Resources\ScheduleResource\Pages\ManageSchedules();

  // Use reflection to access protected method
  $reflectionMethod = new \ReflectionMethod($manageSchedules, 'sendScheduleUpdateNotification');
  $reflectionMethod->setAccessible(true);

  echo "Calling sendScheduleUpdateNotification method...\n";

  // Call the protected method
  $reflectionMethod->invoke($manageSchedules, $updatedRecord);

  echo "WhatsApp notification sent via ManageSchedules\n\n";

  // Verify if WhatsApp notification was sent by checking the logs
  echo "SUCCESS: Test completed. Check Laravel logs for results.\n";
} catch (\Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
