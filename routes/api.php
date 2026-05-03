<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ProductoController;
use Illuminate\Support\Facades\Route;

/*
| Listado y detalle públicos; creación, edición y borrado solo con token Sanctum y rol admin.
*/

Route::middleware('throttle:120,1')->group(function () {
    Route::get('v1/producto', [ProductoController::class, 'index']);
    Route::get('v1/producto/{producto}', [ProductoController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:admin', 'throttle:60,1'])->group(function () {
    Route::post('v1/producto', [ProductoController::class, 'store']);
    Route::put('v1/producto/{producto}', [ProductoController::class, 'update']);
    Route::patch('v1/producto/{producto}', [ProductoController::class, 'update']);
    Route::delete('v1/producto/{producto}', [ProductoController::class, 'destroy']);
});
