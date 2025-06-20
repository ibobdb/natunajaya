# Natuna Jaya WhatsApp Payment Notification System

This document outlines the enhanced WhatsApp notification system for payment events in the Natuna Jaya driving school application.

## Features Implemented

1. **Status-based Notifications**

    - Success payment notifications
    - Pending payment notifications
    - Failed payment notifications
    - Expired payment notifications

2. **Admin Notifications**

    - Automatic notifications to admin(s) for all payment status changes
    - Admin phone numbers configurable via environment variables

3. **Enhanced Templates**

    - Rich formatting with emojis and visual separators
    - Detailed payment information including invoice ID, amount, date, time, and payment method
    - Course details (name, type, session count)
    - Branding elements for Natuna Jaya

4. **Payment Metrics Dashboard**
    - Overview of all payment statuses (success, pending, failed, expired)
    - Monthly revenue chart
    - Success rate calculation
    - Recent orders table with ability to resend notifications
    - Notification log viewer

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```
WHATSAPP_API_URL=http://your-whatsapp-api-url
WHATSAPP_KEY=your-api-key
ADMIN_PHONE_NUMBERS=628123456789,628198765432
BRANCH_CONTACT=628123456789
```

## Testing

Several test scripts are available to verify the notification functionality:

-   `test_all_payment_notifications.php`: Tests all notification types (success, pending, failed, expired)
-   `test_payment_notification.php`: Tests basic payment notification
-   `run_payment_tests.bat` / `run_payment_tests.sh`: Convenience script to run tests

## Usage in Code

### Sending Status-Based Notifications

```php
$whatsappController = new WhatsappController();
$result = $whatsappController->sendPaymentStatusNotification($student, $order, $status);
```

### Sending Admin Notifications

```php
$whatsappController = new WhatsappController();
$result = $whatsappController->sendAdminPaymentNotification($order, $status);
```

## Dashboard Access

The payment metrics dashboard is available at:

```
/admin/payment/metrics
```

The notification logs can be viewed at:

```
/admin/payment/notification-logs
```

## Future Improvements

1. **Enhanced Metrics**
    - User engagement metrics with notifications
    - Delivery confirmations and read receipts
2. **Advanced Templates**
    - Allow customizable templates via admin interface
    - Support for rich media in notifications (images, buttons)
3. **Additional Notification Types**
    - Partial payment notifications
    - Course start reminders tied to payments
    - Payment due reminders
4. **Automated Reconciliation**
    - Smart matching of payment proofs with orders
    - Automatic follow-ups for failed payments
