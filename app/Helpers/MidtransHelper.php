<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;

class MidtransHelper
{
  public function __construct()
  {
    // Set Midtrans configuration from config file
    MidtransConfig::$serverKey = Config::get('midtrans.server_key');
    MidtransConfig::$clientKey = Config::get('midtrans.client_key');
    MidtransConfig::$isProduction = Config::get('midtrans.is_production');
    MidtransConfig::$is3ds = Config::get('midtrans.is_3ds');
    MidtransConfig::$isSanitized = Config::get('midtrans.is_sanitized');
    MidtransConfig::$appendNotifUrl = Config::get('midtrans.append_notif_url');
  }

  /**
   * Create Snap payment transaction
   *
   * @param array $params Payment parameters
   * @return string Snap token
   */
  public function createTransaction(array $params)
  {
    return Snap::getSnapToken($params);
  }

  /**
   * Create payment page URL
   *
   * @param array $params Payment parameters
   * @return string Redirect URL
   */
  public function createPaymentUrl(array $params)
  {
    return Snap::createTransaction($params)->redirect_url;
  }

  /**
   * Get transaction status
   *
   * @param string $orderId Order ID
   * @return object Transaction status object
   */
  public function getStatus($orderId)
  {
    return Transaction::status($orderId);
  }

  /**
   * Verify payment notification
   *
   * @return Notification
   */
  public function parseNotification()
  {
    return new Notification();
  }

  /**
   * Get transaction status message by status code
   *
   * @param string $statusCode
   * @return string
   */
  public function getStatusMessage($statusCode)
  {
    $statusMessages = Config::get('midtrans.status_code');
    return $statusMessages[$statusCode] ?? 'Unknown status';
  }

  /**
   * Handle transaction callback/notification
   *
   * @param Notification $notification
   * @return array
   */
  public function handleNotification(Notification $notification)
  {
    $transaction = $notification->transaction_status;
    $type = $notification->payment_type;
    $orderId = $notification->order_id;
    $fraud = $notification->fraud_status;

    $status = null;

    if ($transaction == 'capture') {
      // For credit card transaction, we need to check whether transaction is challenge by FDS or not
      if ($type == 'credit_card') {
        if ($fraud == 'challenge') {
          $status = 'challenged';
        } else {
          $status = 'success';
        }
      }
    } else if ($transaction == 'settlement') {
      $status = 'success';
    } else if ($transaction == 'pending') {
      $status = 'pending';
    } else if ($transaction == 'deny') {
      $status = 'denied';
    } else if ($transaction == 'expire') {
      $status = 'expired';
    } else if ($transaction == 'cancel') {
      $status = 'canceled';
    }

    return [
      'status' => $status,
      'order_id' => $orderId,
      'raw' => $notification
    ];
  }
}
