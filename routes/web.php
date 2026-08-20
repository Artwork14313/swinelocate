<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FarmLocationController;
use App\Http\Controllers\SwineController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::resource('farms', FarmController::class)
        ->middleware('permission:manage-farms');
    Route::prefix('farms/{farm}')
        ->name('farms.')
        ->middleware('permission:manage-locations')
        ->group(function () {

            Route::resource('locations', FarmLocationController::class)
                ->names('locations');

        });
    Route::resource('swine', SwineController::class);
});

require __DIR__ . '/auth.php';
