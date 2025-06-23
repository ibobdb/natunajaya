<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WhatsappController;
use App\Models\WhatsappFailedMessage;

class RetryFailedWhatsappMessages extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'whatsapp:retry {--max=10 : Maximum number of messages to retry} {--debug : Enable debug output}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Retry failed WhatsApp messages';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $maxMessages = (int) $this->option('max');
    $debug = (bool) $this->option('debug');

    $this->info("Starting retry of up to {$maxMessages} failed WhatsApp messages...");

    if ($debug) {
      $this->info("Checking environment variables...");
      $this->line("WHATSAPP_API_URL: " . (env('WHATSAPP_API_URL') ? env('WHATSAPP_API_URL') : 'Not set'));
      $this->line("WHATSAPP_KEY: " . (env('WHATSAPP_KEY') ? 'Set (value hidden)' : 'Not set'));
    }

    $pendingCount = WhatsappFailedMessage::retryable()->count();
    $this->info("Found {$pendingCount} messages eligible for retry");

    if ($pendingCount === 0) {
      $this->info("No failed messages to retry. Exiting.");
      return Command::SUCCESS;
    }

    $controller = new WhatsappController();
    $results = $controller->retryFailedMessages($maxMessages);

    $this->info("Retry completed. Results:");
    $this->line("Messages processed: {$results['total_processed']}");
    $this->line("Success: {$results['success']}");
    $this->line("Failed: {$results['failed']}");

    if ($debug && !empty($results['details'])) {
      $this->info("Details:");
      foreach ($results['details'] as $id => $result) {
        $this->line("Message #{$id}: " . json_encode($result));
      }
    }

    return Command::SUCCESS;
  }
}
