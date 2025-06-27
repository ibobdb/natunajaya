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
                ]);                // Send notifications via queue when payment status changes
                if ($newStatus !== $originalStatus) {
                    Log::info('Payment status changed, queueing notifications', [
                        'from' => $originalStatus,
                        'to' => $newStatus,
                        'order_id' => $order->id
                    ]);

                    // Only queue payment notifications when status changes to success
                    if ($newStatus === 'success') {
                        // Queue payment status notifications to student
                        $this->sendPaymentStatusNotification($order, $newStatus);

                        // Queue admin notifications for successful payment
                        $this->sendAdminPaymentNotification($order, $newStatus);

                        Log::info('Payment success notifications queued', [
                            'order_id' => $order->id,
                            'invoice_id' => $order->invoice_id
                        ]);
                    } else {
                        Log::info('Payment status changed but not success, skipping notifications', [
                            'status' => $newStatus,
                            'order_id' => $order->id
                        ]);
                    }
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
            // Get the student associated with this order
            $student = $order->student;
            if (!$student) {
                Log::warning("Cannot queue payment notification: Student not found for order {$order->id}");
                return;
            }

            // Get the user through the student relation
            $user = $student->user;
            if (!$user) {
                Log::warning("Cannot queue payment notification: User not found for student {$student->id}, order {$order->id}");
                return;
            }

            // Check if user has a phone number (empty or null)
            if (!$user->phone || trim($user->phone) === '') {
                Log::warning("Skipping notification: User {$user->id} has no phone number");
                return;
            }

            // Format the phone number correctly (remove leading 0, add country code if needed)
            $phoneNumber = $user->phone;
            if (substr($phoneNumber, 0, 1) === '0') {
                // Replace leading 0 with 62 (Indonesia country code)
                $phoneNumber = '62' . substr($phoneNumber, 1);
            } else if (substr($phoneNumber, 0, 2) !== '62') {
                // Add country code if not present
                $phoneNumber = '62' . $phoneNumber;
            }

            // Prepare notification data
            $notificationData = [
                'student_name' => $student->name,
                'course_name' => $order->course->name ?? 'Kursus',
                'amount' => number_format($order->amount, 0, ',', '.'),
                'payment_date' => now()->format('d M Y'),
                'invoice_number' => $order->invoice_id
            ];

            // Queue notification using NotificationController
            \App\Http\Controllers\NotificationController::paymentSuccess(
                $phoneNumber,
                $notificationData
            );

            Log::info("Payment notification queued", [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'phone' => $phoneNumber,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error("Error queueing payment notification: " . $e->getMessage(), [
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
            // Get admin phone numbers from config or DB
            $adminPhones = config('app.admin_phones', []);

            if (empty($adminPhones)) {
                Log::warning("No admin phones configured for notifications");
                return;
            }

            // Get the student associated with this order
            $student = $order->student;
            $studentName = $student ? $student->name : 'Student';

            // Queue admin notification instead of sending directly
            $notificationData = [
                'student_name' => $studentName,
                'course_name' => $order->course->name ?? 'Kursus',
                'amount' => number_format($order->amount, 0, ',', '.'),
                'payment_date' => now()->format('d M Y'),
                'invoice_number' => $order->invoice_id,
                'status' => $status
            ];

            foreach ($adminPhones as $adminPhone) {
                // Skip if phone number is null or empty
                if (!$adminPhone || trim($adminPhone) === '') {
                    Log::warning("Skipping admin notification: Empty phone number found in config");
                    continue;
                }

                // Format the phone number correctly (remove leading 0, add country code if needed)
                if (substr($adminPhone, 0, 1) === '0') {
                    // Replace leading 0 with 62 (Indonesia country code)
                    $adminPhone = '62' . substr($adminPhone, 1);
                } else if (substr($adminPhone, 0, 2) !== '62') {
                    // Add country code if not present
                    $adminPhone = '62' . $adminPhone;
                }

                // Insert notification to queue using the standard payment success notification
                // since adminPaymentNotification doesn't exist in NotificationController
                \App\Http\Controllers\NotificationController::paymentSuccess(
                    $adminPhone,
                    $notificationData
                );

                Log::info("Admin payment notification queued", [
                    'order_id' => $order->id,
                    'status' => $status,
                    'admin_phone' => $adminPhone
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error queueing admin notification: " . $e->getMessage(), [
                'order_id' => $order->id,
                'status' => $status,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
