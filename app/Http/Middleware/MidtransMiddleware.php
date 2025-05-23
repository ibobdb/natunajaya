<?php

namespace App\Http\Middleware;

use App\Helpers\MidtransHelper;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    try {
      // Verify that the request is a valid Midtrans notification
      $midtransHelper = new MidtransHelper();
      $notification = $midtransHelper->parseNotification();

      // Add notification data to request
      $request->merge([
        'midtrans_notification' => $notification,
        'transaction_status' => $notification->transaction_status,
        'order_id' => $notification->order_id,
        'payment_type' => $notification->payment_type,
        'fraud_status' => $notification->fraud_status ?? null
      ]);

      return $next($request);
    } catch (Exception $e) {
      // Log the error
      Log::error('Midtrans notification verification failed: ' . $e->getMessage());

      // Return error response
      return response()->json([
        'status' => 'error',
        'message' => 'Invalid notification data'
      ], 403);
    }
  }
}
