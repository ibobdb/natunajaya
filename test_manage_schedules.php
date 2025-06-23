<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Schedule Update via ManageSchedules class\n";
echo "=============================================\n\n";

try {
  // Find a schedule
  $schedule = \App\Models\Schedule::first();
  if (!$schedule) {
    echo "ERROR: No schedules found in database\n";
    exit(1);
  }

  echo "Found Schedule ID: " . $schedule->id . "\n";

  // Get the ManageSchedules class
  $manageSchedules = new \App\Filament\Student\Resources\ScheduleResource\Pages\ManageSchedules();

  // Get the sendScheduleUpdateNotification method using reflection
  $reflectionMethod = new \ReflectionMethod($manageSchedules, 'sendScheduleUpdateNotification');
  $reflectionMethod->setAccessible(true);

  echo "Sending schedule update notification using ManageSchedules class...\n";

  // Manually get a student with a phone number
  $student = \App\Models\Student::whereHas('user', function ($query) {
    $query->whereNotNull('phone');
  })->first();

  if (!$student) {
    echo "ERROR: No students with phone numbers found\n";
    exit(1);
  }

  echo "Using Student: " . $student->name . " (ID: " . $student->id . ")\n";
  echo "Student Phone: " . $student->user->phone . "\n\n";

  // Create a mock Auth facade
  \Illuminate\Support\Facades\Auth::shouldReceive('user')
    ->andReturn($student->user);

  // Call the protected method with our schedule
  $reflectionMethod->invoke($manageSchedules, $schedule);

  echo "Notification sent via ManageSchedules class\n";

  // Check the log file
  $logPath = storage_path('logs/laravel.log');
  $logContent = file_exists($logPath) ? file_get_contents($logPath) : '';

  if (strpos($logContent, 'WhatsApp schedule update notification sent') !== false) {
    echo "SUCCESS: Found log entry confirming notification was sent\n";
  } else {
    echo "WARNING: Could not find confirmation in logs. Check laravel.log manually.\n";
  }
} catch (\Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
