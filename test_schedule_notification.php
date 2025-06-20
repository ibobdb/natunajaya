<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Schedule Update Notification\n";
echo "===================================\n\n";

try {
  // Get the first schedule and a student with a phone number for testing
  $schedule = \App\Models\Schedule::first();

  // Find a student with a phone number
  $student = \App\Models\Student::whereHas('user', function ($query) {
    $query->whereNotNull('phone');
  })->first();

  if (!$schedule || !$student) {
    echo "ERROR: Could not find a schedule or student for testing.\n";
    exit(1);
  }

  echo "Found Schedule ID: " . $schedule->id . "\n";
  echo "Found Student: " . $student->name . " (ID: " . $student->id . ")\n";
  echo "Student Phone: " . ($student->user->phone ?? "Not set") . "\n\n";

  if (empty($student->user->phone)) {
    echo "ERROR: Student has no phone number.\n";
    exit(1);
  }
  echo "Sending WhatsApp notification...\n";
  $controller = new \App\Http\Controllers\WhatsappController();

  // Debug the URL construction
  echo "API Base URL: " . $controller->getWhatsappApiUrl() . "\n";
  echo "Send Message URL: " . $controller->getSendMessageUrl() . "\n";
  echo "Health Check URL: " . $controller->getHealthCheckUrl() . "\n\n";

  // Add debug info to the logs
  file_put_contents(
    storage_path('logs/whatsapp_debug.log'),
    date('Y-m-d H:i:s') . " - TEST: Schedule update notification\n" .
      "Student ID: {$student->id}, Name: {$student->name}\n" .
      "Phone: " . ($student->user->phone ?? 'No phone found') . "\n" .
      "Schedule ID: {$schedule->id}\n",
    FILE_APPEND
  );

  // Send the notification
  $result = $controller->sendScheduleUpdateNotification($student, $schedule);

  echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

  if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === true)) {
    echo "SUCCESS: WhatsApp notification sent!\n";
  } else {
    echo "ERROR: WhatsApp notification failed.\n";
  }
} catch (\Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
