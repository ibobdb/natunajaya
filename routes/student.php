<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\PaymentController;

// Student routes group
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
  // Payment routes
  Route::get('/payment/{order}', [PaymentController::class, 'show'])->name('payment.show');
  Route::post('/payment/{order}/process', [PaymentController::class, 'process'])->name('payment.process');
});
