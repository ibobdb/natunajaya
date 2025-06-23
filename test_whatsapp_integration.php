<?php

/*
|--------------------------------------------------------------------------
| WhatsApp Integration Test Script
|--------------------------------------------------------------------------
|
| This script performs a comprehensive test of the WhatsApp integration
| features in the Natuna Jaya Driving School application.
|
*/

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\WhatsappController;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Order;
use App\Models\WhatsappFailedMessage;
use Illuminate\Support\Facades\Log;

echo "=================================================\n";
echo "WhatsApp Integration Comprehensive Test Tool\n";
echo "=================================================\n\n";

// Step 1: Check environment
echo "Step 1: Environment Check\n";
echo "-------------------------\n";

$apiUrl = env('WHATSAPP_API_URL');
$apiKey = env('WHATSAPP_KEY');

echo "WHATSAPP_API_URL: " . ($apiUrl ? $apiUrl : 'Not set') . "\n";
echo "WHATSAPP_KEY: " . ($apiKey ? 'Set (value hidden)' : 'Not set') . "\n\n";

if (!$apiUrl || !$apiKey) {
  echo "❌ WhatsApp environment variables not properly configured!\n";
  echo "Please set WHATSAPP_API_URL and WHATSAPP_KEY in your .env file.\n\n";
} else {
  echo "✅ WhatsApp environment variables configured correctly.\n\n";
}

// Step 2: Check controller functionality
echo "Step 2: Controller Functionality Test\n";
echo "-----------------------------------\n";

try {
  $controller = new WhatsappController();
  echo "✅ WhatsappController instantiated successfully.\n";

  // Test API connection
  $status = $controller->checkApiStatus();
  echo "API Status: " . ($status['status'] === 'ok' ? '✅ Connected' : '❌ Connection Failed') . "\n";
  echo "HTTP Code: " . $status['http_code'] . "\n";
  echo "Message: " . $status['message'] . "\n\n";
} catch (\Exception $e) {
  echo "❌ Error creating WhatsappController: " . $e->getMessage() . "\n\n";
}

// Step 3: Test templates
echo "Step 3: Template Generation Test\n";
echo "------------------------------\n";

try {
  // Test welcome template
  $welcomeTemplate = $controller->getWelcomeTemplate('Test Student');
  echo "Welcome Template: " . (strlen($welcomeTemplate) > 0 ? "✅ Generated (" . strlen($welcomeTemplate) . " chars)" : "❌ Failed") . "\n";

  // Find test data for other templates
  $schedule = Schedule::first();
  $order = Order::first();

  if ($schedule) {
    $scheduleTemplate = $controller->getScheduleUpdateTemplate('Test Student', $schedule);
    echo "Schedule Template: " . (strlen($scheduleTemplate) > 0 ? "✅ Generated (" . strlen($scheduleTemplate) . " chars)" : "❌ Failed") . "\n";

    $reminderTemplate = $controller->getReminderTemplate('Test Student', $schedule);
    echo "Reminder Template: " . (strlen($reminderTemplate) > 0 ? "✅ Generated (" . strlen($reminderTemplate) . " chars)" : "❌ Failed") . "\n";
  } else {
    echo "⚠️ No schedule found for testing templates.\n";
  }

  if ($order) {
    $paymentTemplate = $controller->getPaymentConfirmationTemplate('Test Student', $order);
    echo "Payment Template: " . (strlen($paymentTemplate) > 0 ? "✅ Generated (" . strlen($paymentTemplate) . " chars)" : "❌ Failed") . "\n";
  } else {
    echo "⚠️ No order found for testing templates.\n";
  }

  // Test generic template
  $genericTemplate = $controller->getGenericTemplate(
    'Test Student',
    'Test Title',
    'This is a test message body.',
    ['Field 1' => 'Value 1', 'Field 2' => 'Value 2']
  );
  echo "Generic Template: " . (strlen($genericTemplate) > 0 ? "✅ Generated (" . strlen($genericTemplate) . " chars)" : "❌ Failed") . "\n\n";
} catch (\Exception $e) {
  echo "❌ Error testing templates: " . $e->getMessage() . "\n\n";
}

// Step 4: Check database
echo "Step 4: Database Status\n";
echo "---------------------\n";

try {
  // Check students with phone numbers
  $studentCount = Student::whereHas('user', function ($q) {
    $q->whereNotNull('phone');
  })->count();

  echo "Students with phone numbers: {$studentCount}\n";

  // Check failed messages table
  try {
    $failedCount = WhatsappFailedMessage::count();
    $pendingCount = WhatsappFailedMessage::retryable()->count();

    echo "Failed messages in database: {$failedCount}\n";
    echo "Messages pending retry: {$pendingCount}\n\n";
  } catch (\Exception $e) {
    echo "❌ Error accessing failed messages table: " . $e->getMessage() . "\n";
    echo "   Have you run the migration? (php artisan migrate)\n\n";
  }
} catch (\Exception $e) {
  echo "❌ Error checking database: " . $e->getMessage() . "\n\n";
}

// Step 5: Test phone number validation
echo "Step 5: Phone Number Validation\n";
echo "----------------------------\n";

$phoneNumbers = [
  '08123456789',
  '+628123456789',
  '628123456789',
  '123456789',
  'invalid'
];

foreach ($phoneNumbers as $number) {
  $validated = $controller->validatePhoneNumber($number);
  echo "Number: {$number} → " . ($validated ? "✅ {$validated}" : "❌ Invalid") . "\n";
}

echo "\n";

// Step 6: Display sample usage
echo "Step 6: Usage Instructions\n";
echo "-----------------------\n";
echo "To send a test message, use one of these commands:\n\n";
echo "PHP Artisan Command:\n";
echo "  php artisan whatsapp:test [phone_number] --type=welcome\n\n";
echo "Direct Test Script:\n";
echo "  php test_whatsapp_direct.php\n\n";
echo "Run API Status Check:\n";
echo "  php artisan whatsapp:status\n\n";

echo "=================================================\n";
echo "Test Completed\n";
echo "=================================================\n";
