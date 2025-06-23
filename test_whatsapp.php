<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check environment variables
echo "WHATSAPP_API_URL: " . env('WHATSAPP_API_URL', 'Not set') . PHP_EOL;
echo "WHATSAPP_KEY: " . (env('WHATSAPP_KEY') ? 'Set (value hidden)' : 'Not set') . PHP_EOL;

// Create a test message using the controller
$controller = new App\Http\Controllers\WhatsappController();

// Test the getScheduleUpdateTemplate method directly
try {
  // Find a student who has a user with a phone number
  $student = App\Models\Student::whereHas('user', function ($q) {
    $q->whereNotNull('phone');
  })->first();

  if ($student) {
    echo "Found student with phone: {$student->user->phone}" . PHP_EOL;

    // Find a schedule
    $schedule = App\Models\Schedule::where('student_course_id', function ($q) use ($student) {
      $q->select('id')
        ->from('student_courses')
        ->where('student_id', $student->id)
        ->limit(1);
    })->first();

    if ($schedule) {
      echo "Found schedule ID: {$schedule->id}" . PHP_EOL;

      // Test schedule update template
      $template = $controller->getScheduleUpdateTemplate($student->name, $schedule);
      echo "Template generated successfully" . PHP_EOL;

      // Try sending a test message
      $result = $controller->sendScheduleUpdateNotification($student, $schedule);
      echo "Message sending result: " . json_encode($result) . PHP_EOL;
    } else {
      echo "No schedule found for the student" . PHP_EOL;
    }
  } else {
    echo "No student found with a phone number" . PHP_EOL;
  }
} catch (\Exception $e) {
  echo "Error: " . $e->getMessage() . PHP_EOL;
  echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo "Test completed" . PHP_EOL;
