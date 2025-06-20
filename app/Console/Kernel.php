  /**
  * Define the application's command schedule.
  */
  protected function schedule(Schedule $schedule): void
  {
  // Retry failed WhatsApp messages every 30 minutes
  $schedule->command('whatsapp:retry --max=20')
  ->everyThirtyMinutes()
  ->withoutOverlapping()
  ->runInBackground()
  ->appendOutputTo(storage_path('logs/whatsapp_retry.log'));

  // Add other scheduled tasks below
  }