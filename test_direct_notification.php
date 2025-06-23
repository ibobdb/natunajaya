<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Schedule Update with Specified Student\n";
echo "============================================\n\n";

try {
  // Get a valid student and schedule
  $student = \App\Models\Student::whereHas('user', function ($query) {
    $query->whereNotNull('phone');
  })->first();

  $schedule = \App\Models\Schedule::first();

  if (!$student || !$schedule) {
    echo "ERROR: Could not find required data for testing.\n";
    exit(1);
  }    // Check if the schedule has a related student using our new accessor
  if ($schedule->student) {
    echo "Schedule has a linked student: " . $schedule->student->name . " (ID: " . $schedule->student->id . ")\n";

    // Use the student linked to the schedule
    $student = $schedule->student;
  }

  echo "Using Student: " . $student->name . " (ID: " . $student->id . ")\n";
  echo "Student Phone: " . ($student->user->phone ?? "Not set") . "\n";
  echo "Using Schedule ID: " . $schedule->id . "\n\n";

  // Create a custom direct notification function that doesn't rely on auth()
  function sendDirectScheduleNotification($student, $schedule)
  {
    try {
      echo "Sending WhatsApp notification directly...\n";

      if (empty($student->user->phone)) {
        echo "ERROR: Student has no phone number.\n";
        return false;
      }

      $controller = new \App\Http\Controllers\WhatsappController();
      $template = $controller->getScheduleUpdateTemplate($student->name, $schedule);
      $result = $controller->sendMessage($student->user->phone, $template);

      echo "Notification result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";

      return $result;
    } catch (\Exception $e) {
      echo "ERROR: " . $e->getMessage() . "\n";
      return false;
    }
  }

  // Send the notification
  $result = sendDirectScheduleNotification($student, $schedule);

  if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === true)) {
    echo "SUCCESS: WhatsApp notification sent!\n";
  } else {
    echo "ERROR: WhatsApp notification failed.\n";
  }
} catch (\Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
