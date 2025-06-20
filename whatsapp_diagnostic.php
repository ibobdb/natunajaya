<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "==========================================\n";
echo "WhatsApp Integration Diagnostic Tool\n";
echo "==========================================\n\n";

// 1. Check environment variables
echo "Step 1: Checking environment variables...\n";
$apiUrl = env('WHATSAPP_API_URL', null);
$apiKey = env('WHATSAPP_KEY', null);

echo "WHATSAPP_API_URL: " . ($apiUrl ? $apiUrl : 'Not set') . "\n";
echo "WHATSAPP_KEY: " . ($apiKey ? 'Set (value hidden)' : 'Not set') . "\n\n";

// 2. Check if there's a student with a phone number
echo "Step 2: Looking for a student with a phone number...\n";
try {
  $student = null;
  $user = null;

  // Find a user with a phone number
  $user = App\Models\User::whereNotNull('phone')->first();

  if ($user) {
    echo "Found user with phone number: {$user->phone}\n";

    $student = App\Models\Student::where('user_id', $user->id)->first();

    if ($student) {
      echo "Found linked student record (ID: {$student->id})\n\n";
    } else {
      echo "No student record linked to this user\n\n";
    }
  } else {
    echo "No users found with phone numbers set\n\n";
  }

  // 3. Find a schedule for testing
  echo "Step 3: Looking for a schedule record to test with...\n";
  $schedule = null;

  if ($student) {
    $schedule = App\Models\Schedule::whereHas('studentCourse', function ($query) use ($student) {
      $query->where('student_id', $student->id);
    })->first();

    if ($schedule) {
      echo "Found schedule (ID: {$schedule->id})\n\n";
    } else {
      // Try to find any schedule
      $schedule = App\Models\Schedule::first();
      echo $schedule
        ? "No schedule found for this student, using a generic schedule (ID: {$schedule->id})\n\n"
        : "No schedules found in database\n\n";
    }
  } else {
    // Try to find any schedule
    $schedule = App\Models\Schedule::first();
    echo $schedule
      ? "Using a generic schedule for testing (ID: {$schedule->id})\n\n"
      : "No schedules found in database\n\n";
  }

  // 4. Test template generation
  echo "Step 4: Testing template generation...\n";

  $controller = new App\Http\Controllers\WhatsappController();

  if ($student && $schedule) {
    $template = $controller->getScheduleUpdateTemplate($student->name ?? "Test Student", $schedule);
    echo "Template generated successfully:\n";
    echo "--------------------------------------------\n";
    echo $template . "\n";
    echo "--------------------------------------------\n\n";
  } else {
    echo "Cannot test template generation: missing student or schedule\n\n";
  }
  // 5. Test direct API connection
  echo "Step 5: Testing direct connection to WhatsApp API...\n";

  if (!$apiUrl || !$apiKey) {
    echo "Cannot test API connection: environment variables not set\n\n";
  } else {
    // First, test the health check endpoint
    $healthUrl = rtrim($apiUrl, '/') . '/health';
    echo "Testing health check endpoint: $healthUrl\n";

    $ch = curl_init($healthUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'x-api-key' => $apiKey
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Health Check - HTTP Code: {$httpCode}\n";
    if ($error) {
      echo "Health Check - Error: {$error}\n";
    } else {
      echo "Health Check - Response: {$response}\n";
    }
    echo "\n";

    // Then test the send message endpoint
    $testUrl = rtrim($apiUrl, '/') . '/api-keys/send-messages';

    $testPayload = [
      'numbers' => ['1234567890'], // Using dummy number to avoid actual message
      'content' => 'This is a test message (not actually sent)'
    ];

    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'x-api-key: ' . $apiKey
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "API URL: {$testUrl}\n";
    echo "HTTP Code: {$httpCode}\n";

    if ($error) {
      echo "Error: {$error}\n\n";
    } else {
      echo "Response: {$response}\n\n";
    }
  }

  // 6. Send real message if confirmed
  echo "Step 6: Ready to send a real test message\n";

  if ($student && $user && $user->phone) {
    // This is just a simulation to avoid sending real messages
    echo "* Not sending actual test message for this diagnostic *\n";
    echo "To send a real test message, modify this script.\n\n";

    // Uncomment this to send a real test message
    // $result = $controller->sendMessage([$user->phone], "This is a test WhatsApp message from the diagnostic tool");
    // echo "Message sending result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
  } else {
    echo "Cannot send test message: missing student, user, or phone number\n\n";
  }
} catch (\Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n\n";
}

echo "==========================================\n";
echo "Diagnostic complete\n";
echo "==========================================\n";
