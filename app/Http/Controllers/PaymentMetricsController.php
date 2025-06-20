<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentMetricsController extends Controller
{
  /**
   * Show payment notification metrics dashboard
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // Collect metrics
    $totalOrders = Order::count();
    $successfulOrders = Order::where('status', 'success')->count();
    $pendingOrders = Order::where('status', 'pending')->count();
    $failedOrders = Order::where('status', 'failed')->count();
    $expiredOrders = Order::where('status', 'expired')->count();

    $recentOrders = Order::with(['student', 'course'])
      ->orderBy('updated_at', 'desc')
      ->take(10)
      ->get();

    $totalRevenue = Order::where('status', 'success')->sum('amount');

    $paymentsByMonth = Order::where('status', 'success')
      ->select(DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'), DB::raw('SUM(amount) as total'))
      ->groupBy('year', 'month')
      ->orderBy('year', 'desc')
      ->orderBy('month', 'desc')
      ->take(12)
      ->get();

    // Format for chart
    $monthlyLabels = [];
    $monthlyData = [];

    foreach ($paymentsByMonth as $payment) {
      $monthName = date('F', mktime(0, 0, 0, $payment->month, 10));
      $monthlyLabels[] = $monthName . ' ' . $payment->year;
      $monthlyData[] = $payment->total;
    }

    // Reverse arrays for chronological display
    $monthlyLabels = array_reverse($monthlyLabels);
    $monthlyData = array_reverse($monthlyData);

    return view('admin.payment-metrics', compact(
      'totalOrders',
      'successfulOrders',
      'pendingOrders',
      'failedOrders',
      'expiredOrders',
      'recentOrders',
      'totalRevenue',
      'monthlyLabels',
      'monthlyData'
    ));
  }

  /**
   * Resend a payment notification
   *
   * @param Request $request
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function resendNotification(Request $request, $id)
  {
    try {
      $order = Order::findOrFail($id);
      $student = Student::findOrFail($order->student_id);

      // Use the status-based notification
      $whatsappController = new WhatsappController();
      $result = $whatsappController->sendPaymentStatusNotification($student, $order, $order->status);

      if ($result['status'] === 'success') {
        return redirect()->back()->with('success', 'Payment notification resent successfully');
      } else {
        return redirect()->back()->with('error', 'Failed to resend notification: ' . ($result['message'] ?? 'Unknown error'));
      }
    } catch (\Exception $e) {
      Log::error('Error resending payment notification: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to resend notification: ' . $e->getMessage());
    }
  }

  /**
   * Get payment notification logs
   *
   * @return \Illuminate\Http\Response
   */
  public function logs()
  {
    // In a real implementation, you'd parse the logs for WhatsApp notifications
    // This is a simplified version showing the concept

    $notificationLogs = [];

    // Example log file path
    $logPath = storage_path('logs/whatsapp_debug.log');

    if (file_exists($logPath)) {
      $logContent = file_get_contents($logPath);
      $lines = explode("\n", $logContent);

      foreach ($lines as $line) {
        if (strpos($line, 'WhatsApp') !== false) {
          $notificationLogs[] = $line;
        }
      }
    }

    return view('admin.payment-notification-logs', compact('notificationLogs'));
  }
}
