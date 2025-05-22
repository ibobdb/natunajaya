<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransCallbackController;

Route::post('/midtrans-callback', [MidtransCallbackController::class, 'handle'])
    ->name('midtrans.callback');
