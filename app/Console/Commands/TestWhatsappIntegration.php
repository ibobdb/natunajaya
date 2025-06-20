<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WhatsappController;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\User;

class TestWhatsappIntegration extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'whatsapp:test {phone? : Phone number to send test message to} {--type=generic : Message type (generic, welcome, schedule, payment, reminder)} {--debug : Show detailed debug information}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Test WhatsApp integration by sending a test message';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $phoneNumber = $this->argument('phone');
    $messageType = $this->option('type');
    $debug = (bool) $this->option('debug');

    $this->info("WhatsApp Integration Test");
    $this->line("----------------------------");

    if ($debug) {
      $this->info("Environment variables:");
      $this->line("WHATSAPP_API_URL: " . (env('WHATSAPP_API_URL') ? env('WHATSAPP_API_URL') : 'Not set'));
      $this->line("WHATSAPP_KEY: " . (env('WHATSAPP_KEY') ? 'Set (value hidden)' : 'Not set'));
      $this->line("");
    }

    // Check if environment variables are set
    if (!env('WHATSAPP_API_URL') || !env('WHATSAPP_KEY')) {
      $this->error("WhatsApp environment variables not configured properly.");
      $this->line("Please set WHATSAPP_API_URL and WHATSAPP_KEY in your .env file.");
      return Command::FAILURE;
    }

    $controller = new WhatsappController();
    $name = "Test User";

    // Check if phone number is provided, otherwise look for a test user
    if (!$phoneNumber) {
      $this->info("No phone number provided. Looking for a test user...");

      $user = User::whereNotNull('phone')->first();
      if (!$user) {
        $this->error("No users found with phone numbers. Please provide a phone number as argument.");
        return Command::FAILURE;
      }

      $phoneNumber = $user->phone;
      $name = $user->name;
      $this->line("Using user: {$name} with phone: {$phoneNumber}");
    }

    // Get a test schedule and student if needed
    $schedule = null;
    $student = null;

    if (in_array($messageType, ['schedule', 'reminder', 'payment'])) {
      $student = Student::whereHas('user', function ($q) use ($phoneNumber) {
        $q->where('phone', $phoneNumber);
      })->first();

      if (!$student && $messageType !== 'generic') {
        $student = Student::first();
        if ($student) {
          $this->line("Using student: {$student->name} (ID: {$student->id})");
        } else {
          $this->error("No students found in database.");
          return Command::FAILURE;
        }
      }

      if (in_array($messageType, ['schedule', 'reminder'])) {
        $schedule = Schedule::first();
        if (!$schedule) {
          $this->error("No schedules found in database.");
          return Command::FAILURE;
        }
        $this->line("Using schedule ID: {$schedule->id}");
      }
    }

    // Confirm before sending
    if (!$this->confirm("Are you sure you want to send a test {$messageType} message to {$phoneNumber}?", true)) {
      $this->info("Operation cancelled.");
      return Command::SUCCESS;
    }

    $this->info("Sending test message...");

    try {
      // Generate content based on message type
      $content = "";
      $result = [];

      switch ($messageType) {
        case 'welcome':
          $content = $controller->getWelcomeTemplate($name);
          $result = $controller->sendMessage($phoneNumber, $content);
          break;
        case 'schedule':
          if ($student && $schedule) {
            $result = $controller->sendScheduleUpdateNotification($student, $schedule);
          }
          break;
        case 'reminder':
          if ($student && $schedule) {
            $result = $controller->sendReminderMessage($student, $schedule);
          }
          break;
        case 'payment':
          if ($student) {
            $order = \App\Models\Order::first();
            if ($order) {
              $result = $controller->sendPaymentConfirmation($student, $order);
            } else {
              $this->error("No orders found in database.");
              return Command::FAILURE;
            }
          }
          break;
        default:
          // Generic message
          $content = $controller->getGenericTemplate(
            $name,
            "Test Message",
            "This is a test message from the Natuna Jaya driving school application.",
            [
              "Date" => now()->format('d M Y H:i'),
              "Test ID" => uniqid()
            ]
          );
          $result = $controller->sendMessage($phoneNumber, $content);
          break;
      }

      $this->info("Message sent successfully!");
      $this->line("Response: " . json_encode($result));
    } catch (\Exception $e) {
      $this->error("Error: " . $e->getMessage());
      if ($debug) {
        $this->line("Trace: " . $e->getTraceAsString());
      }
      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }
}
