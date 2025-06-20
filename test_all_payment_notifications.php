<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing All Payment WhatsApp Notifications\n";
echo "========================================\n\n";

try {
  // Check if we have admin phone numbers configured
  $adminPhones = explode(',', env('ADMIN_PHONE_NUMBERS', ''));
  $adminPhones = array_filter($adminPhones);

  if (empty($adminPhones)) {
    echo "WARNING: No admin phone numbers configured in environment. Add ADMIN_PHONE_NUMBERS=number1,number2 to .env\n\n";
  } else {
    echo "Admin notification will be sent to: " . implode(", ", $adminPhones) . "\n\n";
  }

  // Create a test order
  echo "Creating a test order...\n";

  // Find a student
  $student = \App\Models\Student::first();
  if (!$student) {
    echo "ERROR: No students found in database\n";
    exit(1);
  }

  // Find a course
  $course = \App\Models\Course::first();
  if (!$course) {
    echo "ERROR: No courses found in database\n";
    exit(1);
  }

  // Test all payment statuses
  $statuses = ['pending', 'success', 'failed', 'expired'];

  foreach ($statuses as $status) {
    echo "\n-------------------------------------------\n";
    echo "Testing '{$status}' payment notification\n";
    echo "-------------------------------------------\n";    // Create a test order
    $order = new \App\Models\Order();
    $order->invoice_id = 'TEST-' . strtoupper($status) . '-' . time();
    $order->student_id = $student->id;
    // Don't include fields that are not in the orders table schema
    // $order->user_id = $student->user_id;
    $order->course_id = $course->id;
    $order->amount = $course->price ?? 100000;
    $order->final_amount = $course->price ?? 100000;
    $order->status = $status;
    // $order->payment_method = 'Test Payment';
    $order->save();

    echo "Created test order with ID: {$order->id}, Status: {$status}\n";

    // Send customer notification
    echo "Sending customer WhatsApp notification...\n";
    $whatsappController = new \App\Http\Controllers\WhatsappController();
    $result = $whatsappController->sendPaymentStatusNotification($student, $order, $status);
    echo "Customer notification result: " . ($result['status'] ?? 'unknown') . "\n";
    if ($result['status'] === 'error') {
      echo "Error: " . ($result['message'] ?? 'Unknown error') . "\n";
    }

    // Send admin notification
    echo "Sending admin WhatsApp notification...\n";
    $adminResult = $whatsappController->sendAdminPaymentNotification($order, $status);
    echo "Admin notification result: " . ($adminResult['status'] ?? 'unknown') . "\n";
    if ($adminResult['status'] === 'error') {
      echo "Error: " . ($adminResult['message'] ?? 'Unknown error') . "\n";
    }

    // Give a small delay between notifications to avoid rate limiting
    sleep(2);
  }

  echo "\n\nAll notification tests completed!\n";
} catch (\Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  echo $e->getTraceAsString() . "\n";
  exit(1);
}
