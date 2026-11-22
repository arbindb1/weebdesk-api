<?php

use App\Http\Controllers\Api\V1\Arcade\MalClashController;
use App\Http\Controllers\Api\V1\Arcade\RedFlagController;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    // Public arcade — identified by X-Guest-UUID, no auth.
    Route::prefix('arcade')->middleware('guest.uuid')->group(function () {
        Route::get('mal-clash', [MalClashController::class, 'batch']);
        Route::post('mal-clash/guess', [MalClashController::class, 'guess']);

        Route::get('red-flag/characters', [RedFlagController::class, 'characters']);
        Route::post('red-flag/scan', [RedFlagController::class, 'scan']);
    });
});
