<?php

namespace App\Filament\Student\Pages;

use App\Helpers\MidtransHelper;
use App\Http\Controllers\WhatsappController;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class Payment extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Payment';
    protected static string $view = 'filament.student.pages.payment';
    protected static bool $shouldRegisterNavigation = false;

    public string $invoiceNumber = ''; // Initialize with default empty string
    public $order = null;
    public $snapToken = null;

    public function mount(): void
    {
        // Get invoice number from query parameter
        $this->invoiceNumber = Request::query('inv') ?? '';
        $this->order = \App\Models\Order::where('invoice_id', $this->invoiceNumber)->first();
    }

    public function processPayment()
    {
        try {
            if (!$this->order) {
                Notification::make()
                    ->title('No order found')
                    ->danger()
                    ->send();
                return;
            }

            // Check if order belongs to current student
            if ($this->order->student_id != auth()->user()->student->id) {
                Notification::make()
                    ->title('You do not have permission to process this payment')
                    ->danger()
                    ->send();
                return;
            }

            // Check if order is already paid
            if ($this->order->status === 'paid') {
                Notification::make()
                    ->title('This order has already been paid')
                    ->warning()
                    ->send();
                return;
            }

            $midtransHelper = new MidtransHelper();

            // Prepare Midtrans payment parameters
            $params = [
                'transaction_details' => [
                    'order_id' => $this->order->invoice_id,
                    'gross_amount' => (float) $this->order->amount,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
                'item_details' => [
                    [
                        'id' => $this->order->course_id,
                        'price' => (float) $this->order->final_amount,
                        'quantity' => 1,
                        'name' => $this->order->course->name,
                    ]
                ],
            ];

            // Get Snap payment page URL
            $this->snapToken = $midtransHelper->createTransaction($params);

            // Show success notification
            Notification::make()
                ->title('Payment ready')
                ->body('Click "Pay Now" to proceed with payment')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Payment Processing Error: ' . $e->getMessage());

            Notification::make()
                ->title('Failed to process payment')
                ->body('Please try again later. Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Add this new method
    public function initiateMidtransPayment()
    {
        $this->dispatch('openMidtransPopup', snapToken: $this->snapToken);
    }

    // Add the payment status handling methods

    public function paymentSuccess($result)
    {
        // The Midtrans callback will update the database
        // This is just for UI feedback

        // Reload the order to get the latest status
        $this->order->refresh();
        Log::info('Payment success for order', [
            'order_id' => $this->order->id,
            'result' => $result
        ]);

        // Send WhatsApp notification about successful payment
        $this->sendPaymentWhatsappNotification();

        // Run MidtransCallbackController handle function with the payment result
        try {
            $callbackController = new \App\Http\Controllers\MidtransCallbackController();

            // Create the notification data structure Midtrans would send
            $notificationData = [
                'transaction_status' => $result['transaction_status'] ?? 'settlement',
                'order_id' => $result['order_id'] ?? $this->order->invoice_id,
                'transaction_id' => $result['transaction_id'] ?? null,
                'status_code' => $result['status_code'] ?? '200',
                'payment_type' => $result['payment_type'] ?? null,
                'gross_amount' => $result['gross_amount'] ?? $this->order->amount,
                'fraud_status' => $result['fraud_status'] ?? 'accept',
                'currency' => $result['currency'] ?? 'IDR'
            ];

            // Create a request with the JSON content that the controller expects
            $request = Request::create(
                '/midtrans-callback',
                'POST',
                [],  // no query parameters
                [],  // no cookies
                [],  // no files
                [],  // no server variables
                json_encode($notificationData)  // JSON body content
            );

            // Set the content type to application/json
            $request->headers->set('Content-Type', 'application/json');

            $callbackController->handle($request);

            // Refresh order after callback processing
            $this->order->refresh();

            Notification::make()
                ->title('Payment successful')
                ->body('Your course has been activated. Please set up your schedule.')
                ->success()
                ->send();
            // Redirect to schedule page
            $this->redirect('/student/courses', navigate: true);
        } catch (\Exception $e) {
            Log::error('Error processing payment callback: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error updating payment status')
                ->body('Your payment was received but there was an error updating your account')
                ->warning()
                ->send();
        }

        // Comment out the immediate redirect to allow notifications to be seen
        // $this->redirect('/student/courses', navigate: true);
    }

    // Handle other payment statuses as needed

    public function paymentPending($result)
    {
        $this->dispatch('notify', [
            'type' => 'warning',
            'message' => 'Your payment is being processed. We will update you once completed.'
        ]);
        // Reload the order
        $this->order->refresh();
    }

    public function paymentError($result)
    {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'Payment failed. Please try again or contact support.'
        ]);
    }

    public function paymentCancelled()
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Payment cancelled.'
        ]);
    }

    /**
     * Send WhatsApp notification for successful payment
     */
    private function sendPaymentWhatsappNotification()
    {
        try {
            $user = auth()->user();
            $student = $user->student;

            // Check if user has a phone number
            if (!$user->phone) {
                Log::warning("Cannot send WhatsApp notification: User {$user->id} has no phone number");
                return;
            }

            // Use WhatsappController to send payment confirmation
            $whatsappController = new \App\Http\Controllers\WhatsappController();
            $result = $whatsappController->sendPaymentConfirmation($student, $this->order);

            Log::info("WhatsApp payment notification sent", [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp notification: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
