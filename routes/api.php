<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDataController;
use App\Http\Controllers\Api\MobileManageController;
use App\Http\Middleware\IdempotentMobileRequest;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login'])
        ->middleware('throttle:6,1');

    Route::get('/public/farmers-nearby', [MobileDataController::class, 'publicNearby'])
        ->middleware('throttle:60,1');

    Route::get('/public/farmers/{token}', [MobileDataController::class, 'publicFarmer'])
        ->middleware('throttle:60,1');

    Route::middleware(['auth:sanctum', 'mobile.active'])->group(function () {
        Route::get('/me', [MobileAuthController::class, 'me']);
        Route::post('/logout', [MobileAuthController::class, 'logout']);
        Route::get('/dashboard', [MobileDataController::class, 'dashboard']);
        Route::get('/options/{resource}', [MobileDataController::class, 'options']);
        Route::get('/data/{resource}', [MobileDataController::class, 'index']);
        Route::post('/data/{resource}', [MobileDataController::class, 'store'])
            ->middleware(IdempotentMobileRequest::class);
        Route::get('/data/{resource}/{id}', [MobileDataController::class, 'show']);
        Route::put('/data/{resource}/{id}', [MobileManageController::class, 'update'])
            ->middleware(IdempotentMobileRequest::class);
        Route::delete('/data/{resource}/{id}', [MobileManageController::class, 'destroy'])
            ->middleware(IdempotentMobileRequest::class);
        Route::post('/data/{resource}/{id}/activate', [MobileManageController::class, 'activate'])
            ->middleware(IdempotentMobileRequest::class);
        Route::get('/qr/{token}', [MobileDataController::class, 'scan']);
    });
});
