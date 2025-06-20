<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WhatsappController;

class WhatsAppStatusCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check WhatsApp API connection status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("WhatsApp API Status Check");
        $this->line("----------------------------");

        // Check environment variables
        $apiUrl = env('WHATSAPP_API_URL');
        $apiKey = env('WHATSAPP_KEY');

        $this->line("Environment configuration:");
        $this->line("  WHATSAPP_API_URL: " . ($apiUrl ? $apiUrl : 'Not set'));
        $this->line("  WHATSAPP_KEY: " . ($apiKey ? 'Set (value hidden)' : 'Not set'));
        $this->line("");

        if (!$apiUrl || !$apiKey) {
            $this->error("WhatsApp environment variables not properly configured.");
            $this->line("Please set WHATSAPP_API_URL and WHATSAPP_KEY in your .env file.");
            return Command::FAILURE;
        }

        $this->info("Checking API connection...");

        $controller = new WhatsappController();
        $result = $controller->checkApiStatus();

        if ($result['status'] === 'ok') {
            $this->info("✅ API connection successful!");
            $this->line("  HTTP Status: " . $result['http_code']);
        } else {
            $this->error("❌ API connection failed!");
            $this->line("  HTTP Status: " . $result['http_code']);
            $this->line("  Error: " . $result['message']);
        }

        // Show API response data if available
        if (!empty($result['data'])) {
            $this->line("\nAPI Response Data:");

            foreach ($result['data'] as $key => $value) {
                if (is_array($value)) {
                    $this->line("  {$key}: " . json_encode($value));
                } else {
                    $this->line("  {$key}: {$value}");
                }
            }
        }

        // Check failed messages table
        $this->line("\nFailed Messages Status:");
        try {
            $pendingCount = \App\Models\WhatsappFailedMessage::retryable()->count();
            $totalCount = \App\Models\WhatsappFailedMessage::count();

            $this->line("  Total failed messages: {$totalCount}");
            $this->line("  Messages pending retry: {$pendingCount}");

            if ($pendingCount > 0) {
                $this->warn("\nYou have {$pendingCount} messages pending retry.");
                $this->line("Use 'php artisan whatsapp:retry' to attempt resending them.");
            }
        } catch (\Exception $e) {
            $this->error("  Error checking failed messages: " . $e->getMessage());
        }

        return $result['status'] === 'ok' ? Command::SUCCESS : Command::FAILURE;
    }
}
