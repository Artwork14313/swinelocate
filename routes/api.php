<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login'])
        ->middleware('throttle:6,1');

    Route::middleware(['auth:sanctum', 'mobile.active'])->group(function () {
        Route::get('/me', [MobileAuthController::class, 'me']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);
        Route::get('/dashboard', [MobileDataController::class, 'dashboard']);
        Route::get('/data/{resource}', [MobileDataController::class, 'index']);
        Route::get('/data/{resource}/{id}', [MobileDataController::class, 'show']);
        Route::get('/qr/{token}', [MobileDataController::class, 'scan']);
    });
});
