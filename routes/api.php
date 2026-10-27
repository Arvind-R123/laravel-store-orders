<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('customers/orders', [OrderController::class, 'history']);
    Route::get('customers', [OrderController::class, 'customer']);
    Route::get('products/low-stock', [OrderController::class, 'lowStock']);
    Route::get('products', [OrderController::class, 'products']);
    Route::get('health', fn () => ['status' => 'ok']);
});
