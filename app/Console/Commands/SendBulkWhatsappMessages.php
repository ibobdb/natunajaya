<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WhatsappController;
use App\Models\Student;

class SendBulkWhatsappMessages extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'whatsapp:bulk {--type=custom : Message type (welcome, custom)} {--title= : Message title (for custom type)} {--message= : Message content (for custom type)} {--limit=0 : Limit the number of recipients (0 for no limit)} {--test : Run in test mode without actually sending}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send bulk WhatsApp messages to all students with phone numbers';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $messageType = $this->option('type');
    $title = $this->option('title') ?? 'Notification';
    $message = $this->option('message') ?? 'This is an important notification from Natuna Jaya Driving School.';
    $limit = (int) $this->option('limit');
    $testMode = (bool) $this->option('test');

    $this->info("WhatsApp Bulk Messaging");
    $this->line("----------------------------");

    // Find students with phone numbers
    $query = Student::whereHas('user', function ($q) {
      $q->whereNotNull('phone');
    });

    if ($limit > 0) {
      $query->limit($limit);
    }

    $students = $query->get();
    $totalRecipients = $students->count();

    $this->info("Found {$totalRecipients} students with phone numbers.");

    if ($totalRecipients === 0) {
      $this->error("No recipients found with phone numbers.");
      return Command::FAILURE;
    }

    // Prepare recipients array
    $recipients = [];
    foreach ($students as $student) {
      if ($student->user && $student->user->phone) {
        $recipients[] = [
          'number' => $student->user->phone,
          'name' => $student->name
        ];
      }
    }

    // Show preview
    $this->info("Recipient preview (first 5):");
    foreach (array_slice($recipients, 0, 5) as $recipient) {
      $this->line("- {$recipient['name']}: {$recipient['number']}");
    }

    if (count($recipients) > 5) {
      $this->line("- ... and " . (count($recipients) - 5) . " more");
    }

    // Confirm before sending
    if (!$this->confirm("Are you sure you want to send a bulk {$messageType} message to {$totalRecipients} recipients?", false)) {
      $this->info("Operation cancelled.");
      return Command::SUCCESS;
    }

    $controller = new WhatsappController();

    // Execute according to message type
    $this->info("Sending messages...");

    $progressBar = $this->output->createProgressBar($totalRecipients);
    $progressBar->start();

    if ($testMode) {
      $this->info("TEST MODE: No messages will be sent");
      sleep(2);
      $progressBar->finish();
      $this->newLine();
      $this->info("Test completed for {$totalRecipients} recipients.");
      return Command::SUCCESS;
    }

    try {
      // Template method to use
      $templateMethod = 'getWelcomeTemplate';
      $templateParams = [];

      if ($messageType === 'custom') {
        $templateMethod = 'getGenericTemplate';
        $templateParams = [$title, $message, [], null];
      }

      $results = $controller->sendBulkMessages($recipients, $templateMethod, $templateParams);

      $progressBar->finish();
      $this->newLine(2);

      $this->info("Bulk messaging completed:");
      $this->line("Total recipients: {$results['summary']['total']}");
      $this->line("Successfully sent: {$results['summary']['success']}");
      $this->line("Failed to send: {$results['summary']['failure']}");

      // Show failures if any
      if ($results['summary']['failure'] > 0) {
        $this->warn("Failed messages:");
        $failures = array_filter($results['details'], function ($result) {
          return isset($result['status']) && $result['status'] === 'error';
        });

        foreach ($failures as $name => $result) {
          $this->line("- {$name}: {$result['message']}");
        }
      }
    } catch (\Exception $e) {
      $this->error("Error: " . $e->getMessage());
      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }
}
