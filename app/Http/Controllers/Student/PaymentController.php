<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
}
