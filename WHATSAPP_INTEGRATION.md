# WhatsApp Integration for Natuna Jaya Driving School

## Overview

This document provides guidance on using the WhatsApp integration system for Natuna Jaya Driving School application. The system sends WhatsApp notifications to students for various events like welcome messages, schedule changes, payment confirmations, and reminders.

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file:

```
WHATSAPP_API_URL=https://api.example.com
WHATSAPP_KEY=your-api-key-here
```

-   `WHATSAPP_API_URL`: The base URL for the WhatsApp API service
-   `WHATSAPP_KEY`: Your WhatsApp API key for authentication

## Available Message Types

The system supports the following message types:

1. **Welcome Messages**: Sent when a new student registers
2. **Schedule Updates**: Sent when a student's lesson schedule changes
3. **Payment Confirmations**: Sent when a payment is successfully processed
4. **Reminders**: Sent the day before a scheduled lesson
5. **Custom Notifications**: Generic messages that can be used for any purpose

## Usage in Code

### Sending a WhatsApp Message

```php
use App\Http\Controllers\WhatsappController;

$controller = new WhatsappController();

// Send to a single recipient
$result = $controller->sendMessage('6282169072681', 'Your message content');

// Send to multiple recipients
$result = $controller->sendMessage(['6282169072681', '6282169072682'], 'Your message content');
```

### Sending Specific Message Types

```php
// Welcome message
$result = $controller->sendWelcomeMessage($student);

// Schedule update
$result = $controller->sendScheduleUpdateNotification($student, $schedule);

// Payment confirmation
$result = $controller->sendPaymentConfirmation($student, $order);

// Reminder
$result = $controller->sendReminderMessage($student, $schedule);

// Custom notification
$result = $controller->sendCustomNotification(
    $student,
    'Important Announcement',
    'The driving school will be closed on Monday for a holiday.',
    [
        'Date' => 'July 17, 2023',
        'Contact' => '+62 821-6907-2681'
    ]
);
```

### Sending Bulk Messages

```php
$recipients = [
    ['number' => '6282169072681', 'name' => 'John Doe'],
    ['number' => '6282169072682', 'name' => 'Jane Smith']
];

// Using welcome template for all recipients
$results = $controller->sendBulkMessages($recipients, 'getWelcomeTemplate');

// Using custom template with parameters
$results = $controller->sendBulkMessages($recipients, 'getGenericTemplate', [
    'Important Notice',
    'Our office hours have changed.',
    ['New Hours' => 'Mon-Fri: 9AM - 5PM'],
    'Thank you for your attention.'
]);
```

## Command-line Tools

### Testing WhatsApp Integration

```bash
# Test with default settings
php artisan whatsapp:test

# Test with specific phone number
php artisan whatsapp:test 6282169072681

# Test with specific message type
php artisan whatsapp:test --type=welcome
php artisan whatsapp:test --type=schedule
php artisan whatsapp:test --type=payment
php artisan whatsapp:test --type=reminder
php artisan whatsapp:test --type=generic

# Show debug information
php artisan whatsapp:test --debug
```

### Sending Bulk Messages

```bash
# Send welcome message to all students
php artisan whatsapp:bulk --type=welcome

# Send custom message
php artisan whatsapp:bulk --type=custom --title="Important Notice" --message="Classes are canceled tomorrow."

# Limit recipients
php artisan whatsapp:bulk --limit=10

# Test mode (no actual messages sent)
php artisan whatsapp:bulk --test
```

### Retrying Failed Messages

```bash
# Retry failed messages (default: up to 10)
php artisan whatsapp:retry

# Retry more messages
php artisan whatsapp:retry --max=50

# Show detailed results
php artisan whatsapp:retry --debug
```

## Diagnostic Tools

### WhatsApp Diagnostic Script

Run the diagnostic script to check your WhatsApp integration:

```bash
php whatsapp_diagnostic.php
```

### Direct API Test

Test direct API connectivity (bypasses Laravel):

```bash
php test_whatsapp_direct.php
```

## Troubleshooting

1. **Messages not sending**: Check your API key and URL in the `.env` file.
2. **API connection errors**: Ensure your server has internet access and can reach the WhatsApp API service.
3. **Failed messages**: Run `php artisan whatsapp:retry` to attempt resending failed messages.
4. **Phone number format**: Ensure phone numbers are in international format without the '+' sign (e.g., 6282169072681).

## Logs

WhatsApp logs are stored in the following locations:

-   **Debug Log**: `storage/logs/whatsapp_debug.log`
-   **Retry Command Log**: `storage/logs/whatsapp_retry.log`
-   **Laravel Log**: `storage/logs/laravel.log`

## Database

Failed messages are stored in the `whatsapp_failed_messages` table and will be automatically retried according to the scheduler configuration.

## Scheduled Tasks

A scheduled task runs every 30 minutes to retry failed messages (configured in `app/Console/Kernel.php`).

---

For additional support or questions, please contact the system administrator.
