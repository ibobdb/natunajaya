<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Helpers\MidtransHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
  protected $midtransHelper;

  public function __construct(MidtransHelper $midtransHelper)
  {
    $this->midtransHelper = $midtransHelper;
  }

  /**
   * Show payment page for the order
   */
  public function show(Order $order)
  {
    // Check if order belongs to the authenticated user's student profile
    $student = auth()->user()->student;

    if (!$student || $order->student_id !== $student->id) {
      abort(403, 'Unauthorized action.');
    }

    return view('student.payment.show', compact('order'));
  }

  /**
   * Process payment
   */
  public function process(Request $request, Order $order)
  {
    // Check if order belongs to the authenticated user's student profile
    $student = auth()->user()->student;

    if (!$student || $order->student_id !== $student->id) {
      abort(403, 'Unauthorized action.');
    }

    // Update order status based on payment method
    $order->update([
      'status' => 'processing',
      'payment_method' => $request->payment_method,
    ]);

    return redirect()->route('filament.student.resources.orders.index')
      ->with('success', 'Payment processed successfully!');
  }

  /**
   * Process payment for an order
   */
  public function processPayment(Request $request, $invoice_id)
  {
    try {
      // Find the order
      $order = Order::where('invoice_id', $invoice_id)
        ->firstOrFail();

      // Check if order belongs to current user
      if ($order->student_id != auth()->user()->student->id) {
        return redirect()->back()->with('error', 'You do not have permission to process this payment');
      }

      // Prepare Midtrans payment parameters
      $params = [
        'transaction_details' => [
          'order_id' => $order->invoice_id,
          'gross_amount' => (float) $order->final_amount,
        ],
        'customer_details' => [
          'first_name' => auth()->user()->name,
          'email' => auth()->user()->email,
        ],
        'item_details' => [
          [
            'id' => $order->course_id,
            'price' => (float) $order->amount,
            'quantity' => 1,
            'name' => $order->course->name,
          ]
        ],
      ];

      // Get Snap payment page URL
      $snapToken = $this->midtransHelper->createTransaction($params);

      return view('payments.checkout', compact('snapToken', 'order'));
    } catch (\Exception $e) {
      Log::error('Payment Processing Error: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to process payment. Please try again later.');
    }
  }

  /**
   * Handle callback notification from Midtrans
   */
  public function handleNotification(Request $request)
  {
    // Note: This route should be protected by the MidtransMiddleware

    try {
      $notificationData = $request->midtrans_notification;
      $orderId = $request->order_id;
      $transactionStatus = $request->transaction_status;
      $fraudStatus = $request->fraud_status;

      // Get the order by invoice_id
      $order = Order::where('invoice_id', $orderId)->firstOrFail();

      // Process the notification based on transaction status
      if ($transactionStatus == 'capture') {
        if ($fraudStatus == 'accept') {
          // Payment success and accepted
          $order->status = 'paid';
          $order->payment_date = now();
        }
      } else if ($transactionStatus == 'settlement') {
        // Payment success
        $order->status = 'paid';
        $order->payment_date = now();
      } else if (
        $transactionStatus == 'cancel' ||
        $transactionStatus == 'deny' ||
        $transactionStatus == 'expire'
      ) {
        // Payment failed
        $order->status = 'failed';
      } else if ($transactionStatus == 'pending') {
        // Payment pending
        $order->status = 'pending';
      }

      // Save the updated order status
      $order->save();

      return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
      Log::error('Notification Handling Error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Finish payment page (redirect from Midtrans)
   */
  public function finishPayment(Request $request)
  {
    return redirect()->route('student.orders.index')->with('success', 'Payment process completed. We will update your order status shortly.');
  }
}
