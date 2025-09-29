<?php

use App\Http\Controllers\Api\CallController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::apiResource('calls', CallController::class)->only(['index', 'store']);
});