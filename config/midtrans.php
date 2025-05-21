<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for Midtrans payment gateway.
    |
    */

  // Midtrans Server Key
  'server_key' => env('MIDTRANS_SERVER_KEY', ''),

  // Midtrans Client Key
  'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

  // Midtrans Merchant ID
  'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),

  // Midtrans Environment: 'sandbox' or 'production'
  'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

  // Midtrans 3DS Transaction
  'is_3ds' => env('MIDTRANS_IS_3DS', true),

  // Midtrans Sanitize
  'is_sanitized' => true,

  // Midtrans Append Customer Details
  'append_notif_url' => true,

  // Midtrans Notification URL
  'notification_url' => env('MIDTRANS_NOTIFICATION_URL', ''),

  // Midtrans Finish Redirect URL
  'finish_redirect_url' => env('MIDTRANS_FINISH_URL', ''),

  // Midtrans Unfinish Redirect URL
  'unfinish_redirect_url' => env('MIDTRANS_UNFINISH_URL', ''),

  // Midtrans Error Redirect URL
  'error_redirect_url' => env('MIDTRANS_ERROR_URL', ''),

  // Midtrans Status Code Mapping
  'status_code' => [
    '200' => 'Success, transaction is successfully processed.',
    '201' => 'Pending, transaction is created but pending authorization.',
    '202' => 'Denied, transaction is denied by bank or Midtrans fraud detection system.',
    '300' => 'Settlement in progress.',
    '400' => 'Expired, transaction is expired.',
    '401' => 'Failed, transaction is failed.',
    '402' => 'Canceled, transaction is canceled.',
    '407' => 'Payment code has been used.',
    '410' => 'Authentication failed.',
    '411' => 'Token has expired.',
    '412' => 'Token not found.',
    '500' => 'Internal server error.',
  ],
];
