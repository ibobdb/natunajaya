<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Midtrans callback received', ['ip' => $request->ip()]);

        try {
            $notification = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON received', [
                    'error' => json_last_error_msg(),
                    'content' => $request->getContent()
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid JSON'], 400);
            }

            Log::info('Midtrans notification data', $notification);

            $orderId = $notification['order_id'] ?? null;
            $transactionStatus = $notification['transaction_status'] ?? null;
            $fraudStatus = $notification['fraud_status'] ?? null;

            if (!$orderId || !$transactionStatus) {
                Log::error('Missing required fields in notification', [
                    'order_id_exists' => isset($notification['order_id']),
                    'transaction_status_exists' => isset($notification['transaction_status'])
                ]);
                return response()->json(['status' => 'error', 'message' => 'Missing required fields'], 400);
            }

            Log::info('Looking up order', ['invoice_id' => $orderId]);
            $order = Order::where('invoice_id', $orderId)->first();

            if (!$order) {
                Log::error('Order not found', ['invoice_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }

            Log::info('Order found', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'current_status' => $order->status
            ]);

            $originalStatus = $order->status;
            $newStatus = $originalStatus;

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $newStatus = 'pending';
                    Log::info('Transaction challenged', ['fraud_status' => $fraudStatus]);
                } else if ($fraudStatus == 'accept') {
                    $newStatus = 'success';
                    Log::info('Payment captured and accepted');
                }
            } else if ($transactionStatus == 'settlement') {
                $newStatus = 'success';
                Log::info('Payment settled');
            } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $newStatus = 'failed';
                Log::info('Payment failed', ['reason' => $transactionStatus]);
            } else if ($transactionStatus == 'pending') {
                $newStatus = 'pending';
                Log::info('Payment pending');
            }

            // Fix the typo in course_id
            $session_order = $order->course->session ?? 0;
            $student_course = $order->course_id; // Fixed from 'courde_id'

            Log::info('Order details for processing', [
                'original_status' => $originalStatus,
                'new_status' => $newStatus,
                'session_order' => $session_order,
                'course_id' => $student_course
            ]);

            // Use transaction to ensure data consistency
            DB::beginTransaction();
            try {
                // Update order status
                $order->status = $newStatus;
                $order->save();

                Log::info('Order status updated', [
                    'from' => $originalStatus,
                    'to' => $newStatus
                ]);

                // Send WhatsApp notifications based on payment status changes
                if ($newStatus !== $originalStatus) {
                    $this->sendPaymentStatusNotification($order, $newStatus);

                    // Also send admin notifications for any payment status change
                    $this->sendAdminPaymentNotification($order, $newStatus);
                }

                // Create student course record first
                $studentCourseId = null;
                if (!empty($student_course)) {
                    Log::info('Creating/updating student course record', [
                        'user_id' => $order->user_id,
                        'course_id' => $student_course
                    ]);

                    // Get instructor with minimum ongoing courses
                    $instructor = \App\Models\Instructor::orderBy('ongoing_course', 'asc')
                        ->first();

                    if (!$instructor) {
                        Log::error('No instructor found');
                        throw new \Exception('No instructor available');
                    }

                    Log::info('Selected instructor', [
                        'instructor_id' => $instructor->id,
                        'ongoing_courses' => $instructor->ongoing_course
                    ]);

                    $studentCourse = \App\Models\StudentCourse::updateOrCreate(
                        [
                            'course_id' => $student_course,
                            'invoice_id' => $order->invoice_id,
                        ],
                        [
                            'student_id' => $order->student_id,
                            'status' => 'schedule_not_set',
                            'active_on' => now(),
                            'instructor_id' => $instructor->id,
                            'score' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    // Increment the instructor's ongoing course count
                    $instructor->ongoing_course += 1;
                    $instructor->save();

                    Log::info('Updated instructor ongoing course count', [
                        'instructor_id' => $instructor->id,
                        'new_count' => $instructor->ongoing_course
                    ]);

                    $studentCourseId = $studentCourse->id;
                    Log::info('Successfully created/updated student course record', ['student_course_id' => $studentCourseId]);
                }

                // Create schedules if we have session_order and course_id
                if (!empty($session_order) && !empty($student_course) && $studentCourseId) {
                    Log::info('Creating schedules', ['count' => $session_order]);

                    $schedules = [];
                    for ($i = 1; $i <= $session_order; $i++) {
                        Log::info('Preparing schedule', [
                            'iteration' => $i,
                            'order_id' => $order->id,
                            'student_course_id' => $studentCourseId
                        ]);
                        $schedules[] = [
                            'student_course_id' => $studentCourseId,
                            'for_session' => $i,
                            'start_date' => null,
                            'status' => 'date_not_set',
                            'duration_session' => 'week',
                            'instructor_id' => $instructor->id, // Add instructor_id from the assigned instructor
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        Log::info('Schedule prepared', ['session' => $i]);
                    }

                    Log::info('About to insert schedules into database', ['count' => count($schedules)]);
                    \App\Models\Schedule::insert($schedules);
                    Log::info('Successfully created schedules', ['count' => $session_order]);
                }

                DB::commit();
                Log::info('Midtrans callback processed successfully with transaction', ['order_id' => $orderId]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Transaction failed, rolling back all changes', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['status' => 'error', 'message' => 'Processing error'], 500);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error processing Midtrans callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Send WhatsApp notification based on payment status
     *
     * @param \App\Models\Order $order
     * @param string $status
     * @return void
     */
    private function sendPaymentStatusNotification($order, $status)
    {
        try {
            // Get the user and student
            $user = \App\Models\User::find($order->user_id);
            if (!$user) {
                Log::warning("Cannot send WhatsApp notification: User not found for order {$order->id}");
                return;
            }

            $student = $user->student;
            if (!$student) {
                Log::warning("Cannot send WhatsApp notification: No student record for user {$user->id}");
                return;
            }

            // Check if user has a phone number
            if (empty($user->phone)) {
                Log::warning("Cannot send WhatsApp notification: User {$user->id} has no phone number");
                return;
            }

            // Use WhatsappController to send payment status notification
            $whatsappController = new \App\Http\Controllers\WhatsappController();
            $result = $whatsappController->sendPaymentStatusNotification($student, $order, $status);

            Log::info("WhatsApp payment notification sent from callback controller", [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'phone' => $user->phone,
                'status' => $status,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp notification from callback: " . $e->getMessage(), [
                'order_id' => $order->id,
                'status' => $status,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send WhatsApp notification to admin about payment status
     *
     * @param \App\Models\Order $order
     * @param string $status
     * @return void
     */
    private function sendAdminPaymentNotification($order, $status)
    {
        try {
            $whatsappController = new \App\Http\Controllers\WhatsappController();
            $result = $whatsappController->sendAdminPaymentNotification($order, $status);

            Log::info("WhatsApp admin payment notification sent", [
                'order_id' => $order->id,
                'status' => $status,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp admin notification: " . $e->getMessage(), [
                'order_id' => $order->id,
                'status' => $status,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
