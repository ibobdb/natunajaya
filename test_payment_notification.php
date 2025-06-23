<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Schema;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Payment WhatsApp Notification\n";
echo "====================================\n\n";

try {
  // Find a pending order to simulate payment
  $order = \App\Models\Order::where('status', 'pending')
    ->orWhere('status', 'processing')
    ->first();

  if (!$order) {
    echo "No pending orders found. Creating a test order...\n";

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
    }    // Create a test order
    $order = new \App\Models\Order();
    $order->invoice_id = 'TEST-INV-' . time();
    $order->student_id = $student->id;
    $order->course_id = $course->id;
    $order->amount = $course->price ?? 100000;
    $order->final_amount = $course->price ?? 100000;
    $order->status = 'pending';
    $order->save();

    echo "Created test order with ID: {$order->id}\n";
  }

  // Get order details
  echo "Order ID: " . $order->id . "\n";
  echo "Invoice ID: " . $order->invoice_id . "\n";
  echo "Amount: " . $order->amount . "\n";
  echo "Current Status: " . $order->status . "\n";

  // Get student information
  $student = \App\Models\Student::find($order->student_id);
  if (!$student) {
    echo "ERROR: No student associated with this order\n";
    exit(1);
  }

  echo "Student: " . $student->name . " (ID: " . $student->id . ")\n";

  if (empty($student->user->phone)) {
    echo "WARNING: Student has no phone number. Adding a test phone number.\n";
    $student->user->update(['phone' => '082169072681']);
    echo "Added phone number: " . $student->user->phone . "\n";
  } else {
    echo "Student Phone: " . $student->user->phone . "\n";
  }  // Simulate successful payment
  echo "\nSimulating successful payment...\n";
  $originalStatus = $order->status;
  $order->status = 'success';
  $order->save();

  echo "Order status updated from {$originalStatus} to success\n\n";

  // Send WhatsApp notification
  echo "Sending WhatsApp payment notification...\n";

  $whatsappController = new \App\Http\Controllers\WhatsappController();
  $result = $whatsappController->sendPaymentConfirmation($student, $order);

  echo "Notification Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

  if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === true)) {
    echo "SUCCESS: WhatsApp payment notification sent!\n";
  } else {
    echo "ERROR: WhatsApp payment notification failed.\n";
  }

  // Create message template for review
  $template = $whatsappController->getPaymentConfirmationTemplate($student->name, $order);

  echo "\nMessage Template Preview:\n";
  echo "------------------------\n";
  echo $template . "\n";
  echo "------------------------\n";
} catch (\Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
