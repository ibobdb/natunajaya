<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n=======================================================\n";
echo "INSTRUCTOR SCHEDULE COMPLETION WHATSAPP NOTIFICATION TEST\n";
echo "=======================================================\n\n";

// First clear previous debug logs to make it easier to see new entries
file_put_contents(storage_path('logs/whatsapp_test.log'), date('Y-m-d H:i:s') . " - Starting test\n");

try {
  // Find a schedule with waiting_signature status
  $schedule = \App\Models\Schedule::where('status', 'waiting_signature')
    ->whereNull('att_instructor')
    ->orWhere('att_instructor', 0)
    ->first();

  if (!$schedule) {
    echo "No schedule with waiting_signature status and no instructor signature found.\n";
    echo "Creating a test schedule in waiting_signature status...\n";

    // Find any schedule to modify
    $schedule = \App\Models\Schedule::where('status', 'ready')->first();

    if (!$schedule) {
      echo "ERROR: No suitable schedules found to modify.\n";
      exit(1);
    }

    // Update to waiting_signature status
    $schedule->update([
      'status' => 'waiting_signature',
      'att_instructor' => 0,
      'att_student' => 1  // Let's assume student already signed
    ]);

    echo "Created schedule with waiting_signature status (ID: {$schedule->id}).\n";
  }

  // Get schedule details
  echo "Schedule ID: " . $schedule->id . "\n";
  echo "Current Status: " . $schedule->status . "\n";
  echo "Instructor Signature: " . ($schedule->att_instructor ? "Yes" : "No") . "\n";
  echo "Student Signature: " . ($schedule->att_student ? "Yes" : "No") . "\n";

  // Get student information
  $student = $schedule->student;

  if (!$student) {
    echo "ERROR: No student associated with this schedule\n";
    exit(1);
  }

  echo "Student: " . $student->name . " (ID: " . $student->id . ")\n";

  if (empty($student->user->phone)) {
    echo "WARNING: Student has no phone number. Adding a test phone number.\n";
    $student->user->update(['phone' => '082169072681']);
    echo "Added phone number: " . $student->user->phone . "\n";
  } else {
    echo "Student Phone: " . $student->user->phone . "\n";
  }

  echo "\nSimulating instructor signing the schedule...\n";

  // Prepare the update data - simulates instructor signing
  $updateData = ['att_instructor' => 1];

  // If student has already signed, mark as complete
  if ($schedule->att_student == 1) {
    $updateData['status'] = 'complete';
    echo "Student has already signed, marking session as complete.\n";
  } else {
    echo "Student has not signed yet, just adding instructor signature.\n";
  }

  // Update the schedule
  $schedule->update($updateData);
  $schedule->refresh();

  echo "Schedule updated.\n";
  echo "New Status: " . $schedule->status . "\n";

  // Now manually trigger the WhatsApp notification as the Instructor ScheduleResource would
  echo "\nSending WhatsApp notification from instructor context...\n";
  // Write to the WhatsApp debug log directly
  file_put_contents(
    storage_path('logs/whatsapp_debug.log'),
    date('Y-m-d H:i:s') . " - TEST SCRIPT: About to send notification\n" .
      "Student ID: {$student->id}, Name: {$student->name}\n" .
      "Phone: " . ($student->user->phone ?? 'No phone found') . "\n" .
      "Schedule ID: {$schedule->id}, Status: {$schedule->status}\n",
    FILE_APPEND
  );

  // Instantiate the resource class and call its notification method
  $scheduleResource = new \App\Filament\Instructor\Resources\ScheduleResource();

  // Use reflection to access the protected static method
  $reflectionMethod = new \ReflectionMethod('\App\Filament\Instructor\Resources\ScheduleResource', 'sendScheduleUpdateNotification');
  $reflectionMethod->setAccessible(true);
  $reflectionMethod->invoke(null, $schedule);

  // Also send directly with the controller for comparison
  $whatsappController = new \App\Http\Controllers\WhatsappController();
  $result = $whatsappController->sendScheduleUpdateNotification($student, $schedule);
  file_put_contents(
    storage_path('logs/whatsapp_debug.log'),
    date('Y-m-d H:i:s') . " - TEST SCRIPT: Direct controller result:\n" .
      json_encode($result, JSON_PRETTY_PRINT) . "\n",
    FILE_APPEND
  );

  echo "Notification sent. Check the logs for details.\n";
  echo "Test log: " . storage_path('logs/whatsapp_test.log') . "\n";
  echo "WhatsApp debug log: " . storage_path('logs/whatsapp_debug.log') . "\n";
  echo "Laravel log: " . storage_path('logs/laravel.log') . "\n";

  echo "\n=======================================================\n";
  echo "TEST COMPLETED. Check the logs for details.\n";
  echo "=======================================================\n";
} catch (\Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";

  file_put_contents(
    storage_path('logs/whatsapp_test.log'),
    date('Y-m-d H:i:s') . " - Test error\n" .
      "Error: " . $e->getMessage() . "\n" .
      "Trace: " . $e->getTraceAsString() . "\n",
    FILE_APPEND
  );
}
