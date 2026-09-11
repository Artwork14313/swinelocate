<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FarmLocationController;
use App\Http\Controllers\SwineController;
use App\Http\Controllers\SwineMovementController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WeightRecordController;
use App\Http\Controllers\GrowthMonitoringController;
use App\Http\Controllers\QrTraceabilityController;
use App\Http\Controllers\SyncStatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    //USER MANAGEMENT
    Route::resource('users', UserController::class)
        ->middleware('permission:manage-users');

    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->middleware('permission:manage-users')
        ->name('users.toggle-status');

    //ROLE MANAGEMENT
    Route::middleware('permission:manage-roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])
            ->name('roles.index');

        Route::get('/roles/{role}', [RoleController::class, 'show'])
            ->name('roles.show');

        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->name('roles.edit');

        Route::put('/roles/{role}', [RoleController::class, 'update'])
            ->name('roles.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Farm Management
    |--------------------------------------------------------------------------
    */

    Route::resource('farms', FarmController::class)
        ->middleware('permission:manage-farms');

    Route::prefix('farms/{farm}')
        ->name('farms.')
        ->middleware('permission:manage-locations')
        ->group(function () {

            Route::resource('locations', FarmLocationController::class)
                ->names('locations');

        });

    Route::patch('/farms/{farm}/activate', [FarmController::class, 'activate'])
        ->middleware('permission:manage-farms')
        ->name('farms.activate');


    /*
    |--------------------------------------------------------------------------
    | Swine Management
    |--------------------------------------------------------------------------
    |
    | Farm Manager + Veterinarian can manage swine.
    | Registration is additionally protected by register-swine.
    |
    */

    Route::get('/swine', [SwineController::class, 'index'])
        ->middleware('permission:manage-swine')
        ->name('swine.index');

    Route::get('/swine/create', [SwineController::class, 'create'])
        ->middleware('permission:register-swine')
        ->name('swine.create');

    Route::post('/swine', [SwineController::class, 'store'])
        ->middleware('permission:register-swine')
        ->name('swine.store');

    Route::get('/swine/{swine}/edit', [SwineController::class, 'edit'])
        ->middleware('permission:manage-swine')
        ->name('swine.edit');

    Route::put('/swine/{swine}', [SwineController::class, 'update'])
        ->middleware('permission:manage-swine')
        ->name('swine.update');

    Route::patch('/swine/{swine}', [SwineController::class, 'update'])
        ->middleware('permission:manage-swine')
        ->name('swine.update.patch');

    Route::delete('/swine/{swine}', [SwineController::class, 'destroy'])
        ->middleware('permission:manage-swine')
        ->name('swine.destroy');

    Route::get('/swine/{swine}', [SwineController::class, 'show'])
        ->middleware('permission:view-traceability')
        ->name('swine.show');


    /*
    |--------------------------------------------------------------------------
    | QR Scanning
    |--------------------------------------------------------------------------
    */

    Route::get('/qr/scan/{qr_token}', [SwineController::class, 'scan'])
        ->middleware('permission:scan-qr')
        ->name('swine.scan');

    Route::get('/swine/scan/{qr_token}', [SwineController::class, 'scan'])
        ->middleware('permission:scan-qr')
        ->name('swine.scan.alt');

    Route::get('/qr/scanner', [QrTraceabilityController::class, 'scanner'])
        ->middleware('permission:scan-qr')
        ->name('qr.scanner');


    /*
    |--------------------------------------------------------------------------
    | Traceability
    |--------------------------------------------------------------------------
    |
    | All roles can view traceability.
    |
    */

    Route::get('/traceability', [QrTraceabilityController::class, 'index'])
        ->middleware('permission:view-traceability')
        ->name('traceability.index');


    /*
    |--------------------------------------------------------------------------
    | Health Records
    |--------------------------------------------------------------------------
    |
    | Administrator + Farm Manager + Veterinarian.
    | Vaccination is handled as a Health Record type.
    |
    */

    Route::get('/health-records', [HealthRecordController::class, 'index'])
        ->middleware('permission:manage-health')
        ->name('health-records.index');

    Route::get('/health-records/create', [HealthRecordController::class, 'create'])
        ->middleware('permission:manage-health')
        ->name('health-records.create');

    Route::post('/health-records', [HealthRecordController::class, 'store'])
        ->middleware('permission:manage-health')
        ->name('health-records.store');

    Route::get('/health-records/{health_record}', [HealthRecordController::class, 'show'])
        ->middleware('permission:manage-health')
        ->name('health-records.show');

    Route::get('/health-records/{health_record}/edit', [HealthRecordController::class, 'edit'])
        ->middleware('permission:manage-health')
        ->name('health-records.edit');

    Route::put('/health-records/{health_record}', [HealthRecordController::class, 'update'])
        ->middleware('permission:manage-health')
        ->name('health-records.update');

    Route::patch('/health-records/{health_record}', [HealthRecordController::class, 'update'])
        ->middleware('permission:manage-health')
        ->name('health-records.update.patch');

    Route::delete('/health-records/{health_record}', [HealthRecordController::class, 'destroy'])
        ->middleware('permission:manage-health')
        ->name('health-records.destroy');

    Route::get('/health-history', [HealthRecordController::class, 'historyIndex'])
        ->middleware('permission:manage-health')
        ->name('health-records.history.index');

    Route::get('/health-history/{swine}/history', [HealthRecordController::class, 'history'])
        ->middleware('permission:manage-health')
        ->name('health-records.history');


    /*
    |--------------------------------------------------------------------------
    | Weight Records
    |--------------------------------------------------------------------------
    |
    | Farm Manager + Farm Staff.
    |
    */

    Route::resource('weight-records', WeightRecordController::class)
        ->middleware('permission:record-weight');

    Route::get('/growth-monitoring', [GrowthMonitoringController::class, 'index'])
        ->middleware('permission:record-weight')
        ->name('growth-monitoring.index');


    /*
    |--------------------------------------------------------------------------
    | Swine Movement
    |--------------------------------------------------------------------------
    |
    | Farm Manager + Farm Staff.
    |
    */

    Route::get('/swine/{swine}/move', [SwineMovementController::class, 'create'])
        ->middleware('permission:manage-movements')
        ->name('swine.movements.create');

    Route::post('/swine/{swine}/move', [SwineMovementController::class, 'store'])
        ->middleware('permission:manage-movements')
        ->name('swine-movements.store');

    Route::get('/swine-movements', [SwineMovementController::class, 'index'])
        ->middleware('permission:manage-movements')
        ->name('swine-movements.index');

    Route::get('/swine-movements/{swineMovement}', [SwineMovementController::class, 'show'])
        ->middleware('permission:manage-movements')
        ->name('swine-movements.show');


    /*
    |--------------------------------------------------------------------------
    | Offline Movement
    |--------------------------------------------------------------------------
    */

    Route::get('/offline-movement', function () {
        return view('swine.movements.offline');
    })
        ->middleware('permission:manage-movements')
        ->name('swine.movements.offline');


    /*
    |--------------------------------------------------------------------------
    | Swine Offline Synchronization
    |--------------------------------------------------------------------------
    */

    Route::get('/swine/offline-state', [SwineController::class, 'offlineState'])
        ->middleware('permission:manage-swine')
        ->name('swine.offline-state');

    Route::post('/swine/sync', [SwineController::class, 'syncStore'])
        ->middleware('permission:register-swine')
        ->name('swine.sync.store');

    Route::put('/swine/{swine}/sync', [SwineController::class, 'syncUpdate'])
        ->middleware('permission:manage-swine')
        ->name('swine.sync.update');

    Route::put('/swine/{swine}/resolve-conflict', [SwineController::class, 'resolveConflict'])
        ->middleware('permission:manage-swine')
        ->name('swine.resolve-conflict');


    /*
    |--------------------------------------------------------------------------
    | Movement Synchronization
    |--------------------------------------------------------------------------
    |
    | Farm Manager + Farm Staff.
    |
    */

    Route::post('/swine-movements/sync', [SwineMovementController::class, 'syncStore'])
        ->middleware('permission:manage-movements')
        ->name('swine-movements.sync');

    Route::put(
        '/swine-movements/{swineMovement}/resolve-conflict',
        [SwineMovementController::class, 'resolveConflict']
    )
        ->middleware('permission:manage-movements')
        ->name('swine-movements.resolve-conflict');


    /*
    |--------------------------------------------------------------------------
    | Weight Synchronization
    |--------------------------------------------------------------------------
    |
    | Farm Manager + Farm Staff.
    |
    */

    Route::post('/weight-records/sync', [WeightRecordController::class, 'syncStore'])
        ->middleware('permission:record-weight')
        ->name('weight-records.sync');


    /*
    |--------------------------------------------------------------------------
    | Synchronization Status
    |--------------------------------------------------------------------------
    */

    Route::get('/sync-status', [SyncStatusController::class, 'index'])
        ->name('sync-status.index');
});

require __DIR__ . '/auth.php';