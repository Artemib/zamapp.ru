<?php

use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\MegafonController;
use Illuminate\Support\Facades\Route;

Route::post('megafon', [MegafonController::class, 'cmd']);

Route::prefix('v1')->group(function () {
    Route::apiResource('calls', CallController::class)->only(['index', 'store']);
    Route::apiResource('orders', OrderController::class);
});