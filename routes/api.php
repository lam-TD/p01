<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentFilterController;
Route::apiResource('payments', PaymentController::class);
Route::get('payments-filters', [PaymentFilterController::class, 'index']);
