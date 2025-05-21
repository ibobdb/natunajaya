<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    $role = $user->role;

    // Redirect based on user role
    if ($role === 'student') {
        return redirect('/student');
    } elseif ($role === 'admin') {
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

require __DIR__ . '/auth.php';
