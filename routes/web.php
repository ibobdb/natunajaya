<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController;

Route::get('/', [PageController::class, 'index'])->name('welcome');
Route::get('/check-schedule', [PageController::class, 'checkSchedule'])->name('check-schedule');

Route::get('/dashboard', function () {
    $user = Auth::user();
    $role = $user->role;

    // Redirect based on user role
    if ($role == 'student') {
        return redirect('/student');
    } elseif ($role == 'admin') {
        return redirect('/admin');
    } elseif ($role === 'instructor') {
        return redirect('/instructor');
    }

    // Default fallback - show dashboard view
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth'])->group(function () {
    // Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    // Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    // Route::post('/orders/check-availability', [\App\Http\Controllers\OrderController::class, 'checkAvailability'])->name('orders.check-availability');
    // Route::get('/teachers', [\App\Http\Controllers\OrderController::class, 'getTeachers'])->name('teachers.filter');
    // Route::get('/cars/filter', [App\Http\Controllers\OrderController::class, 'getCars'])->name('cars.filter');
    // Route::get('/student/payment/{invoiceNumber}', App\Filament\Student\Pages\Payment::class)
    //     ->name('payment.show');
});

// Midtrans Notification Route - No auth middleware needed
// Route::post('/payments/notification', [PaymentController::class, 'handleNotification'])
//     ->middleware('midtrans')
//     ->name('payments.notification');
// Route::post('/midtrans-callback', [MidtransCallbackController::class, 'handle'])
//     ->name('midtrans.callback');
Route::get('/ping', function () {
    return 'pong';
});

require __DIR__ . '/auth.php';

// Include WhatsApp API routes
require __DIR__ . '/whatsapp.php';

// Admin payment metrics routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/payment/metrics', [App\Http\Controllers\PaymentMetricsController::class, 'index'])
        ->name('payment.metrics');
    Route::get('/payment/notification-logs', [App\Http\Controllers\PaymentMetricsController::class, 'logs'])
        ->name('payment.logs');
    Route::post('/payment/{id}/resend-notification', [App\Http\Controllers\PaymentMetricsController::class, 'resendNotification'])
        ->name('payment.resend');

    // Test routes
    Route::get('/payment/run-tests', function () {
        return redirect()->route('admin.payment.metrics')
            ->with('info', 'Test initiated in background. Check logs for results.');
    })->name('payment.test');

    Route::get('/whatsapp/status', function () {
        $controller = new App\Http\Controllers\WhatsappController();
        $url = $controller->getHealthCheckUrl();
        $status = false;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
            $status = $response->successful();
        } catch (\Exception $e) {
            $status = false;
        }

        return redirect()->route('admin.payment.logs')
            ->with($status ? 'success' : 'error', 'WhatsApp API is ' . ($status ? 'online' : 'offline'));
    })->name('whatsapp.status');
});
