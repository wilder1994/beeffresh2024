<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:120,1')->group(function () {
    Route::get('v1/producto', [ProductController::class, 'index']);
    Route::get('v1/producto/{product}', [ProductController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:admin', 'throttle:60,1'])->group(function () {
    Route::post('v1/producto', [ProductController::class, 'store']);
    Route::put('v1/producto/{product}', [ProductController::class, 'update']);
    Route::patch('v1/producto/{product}', [ProductController::class, 'update']);
    Route::delete('v1/producto/{product}', [ProductController::class, 'destroy']);
});
