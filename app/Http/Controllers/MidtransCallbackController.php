<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $notification = json_decode($request->getContent(), true);
        $orderId = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'];
        $fraudStatus = $notification['fraud_status'] ?? null;

        Log::info('Midtrans notification', $notification);

        $order = Order::where('invoice_id', $orderId)->firstOrFail();

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $order->status = 'pending';
            } else if ($fraudStatus == 'accept') {
                $order->status = 'paid';
                $order->payment_date = now();
            }
        } else if ($transactionStatus == 'settlement') {
            $order->status = 'paid';
            $order->payment_date = now();
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $order->status = 'failed';
        } else if ($transactionStatus == 'pending') {
            $order->status = 'pending';
        }

        $order->save();

        return response()->json(['status' => 'success']);
    }
}
