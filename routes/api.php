<?php

use App\Http\Controllers\HoldController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;

Route::get('/products/{product}', [ProductController::class, 'show']);
Route::post('/holds', [HoldController::class, 'store']);
Route::post('/orders', [OrderController::class, 'store']);
Route::post('webhooks/payment', [PaymentWebhookController::class, 'handle'])->middleware('throttle:100,1');
