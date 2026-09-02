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


/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Farm Management
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'farms',
        FarmController::class
    )->middleware('permission:manage-farms');


    /*
    |--------------------------------------------------------------------------
    | Farm Locations
    |--------------------------------------------------------------------------
    */

    Route::prefix('farms/{farm}')
        ->name('farms.')
        ->middleware('permission:manage-locations')
        ->group(function () {

            Route::resource(
                'locations',
                FarmLocationController::class
            )->names('locations');

        });


    /*
    |--------------------------------------------------------------------------
    | Swine Management
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'swine',
        SwineController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Swine QR Scan
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/qr/scan/{qr_token}',
        [SwineController::class, 'scan']
    )->name('swine.scan');


    Route::get(
        '/swine/scan/{qr_token}',
        [SwineController::class, 'scan']
    )->name('swine.scan.alt');


    /*
    |--------------------------------------------------------------------------
    | Health History
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/health-history',
        [HealthRecordController::class, 'historyIndex']
    )->name('health-records.history.index');


    Route::get(
        '/health-history/{swine}/history',
        [HealthRecordController::class, 'history']
    )->name('health-records.history');


    /*
    |--------------------------------------------------------------------------
    | Health Records
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'health-records',
        HealthRecordController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Weight Records
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'weight-records',
        WeightRecordController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Growth Monitoring
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/growth-monitoring',
        [GrowthMonitoringController::class, 'index']
    )->name('growth-monitoring.index');


    /*
    |--------------------------------------------------------------------------
    | Swine Movement
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/swine/{swine}/move',
        [SwineMovementController::class, 'create']
    )->name('swine.movements.create');


    Route::post(
        '/swine/{swine}/move',
        [SwineMovementController::class, 'store']
    )->name('swine-movements.store');


    Route::get(
        '/swine-movements',
        [SwineMovementController::class, 'index']
    )->name('swine-movements.index');


    Route::get(
        '/swine-movements/{swineMovement}',
        [SwineMovementController::class, 'show']
    )->name('swine-movements.show');


    /*
     * Location endpoint
     *
     * KEEP ONLY if locations()
     * exists in SwineMovementController.
     */
    Route::get(
        '/swine-movements/{swine}/locations',
        [SwineMovementController::class, 'locations']
    )->name('swine-movements.locations');


    /*
    |--------------------------------------------------------------------------
    | QR & Traceability
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/qr/scanner',
        [QrTraceabilityController::class, 'scanner']
    )->name('qr.scanner');


    /*
    |--------------------------------------------------------------------------
    | Offline Synchronization
    |--------------------------------------------------------------------------
    */


    /*
     * Offline Swine Creation
     */
    Route::post(
        '/swine/sync',
        [SwineController::class, 'syncStore']
    )->name('swine.sync.store');


    /*
     * Offline Swine Update
     */
    Route::put(
        '/swine/{swine}/sync',
        [SwineController::class, 'syncUpdate']
    )->name('swine.sync.update');


    /*
     * Resolve Swine Update Conflict
     */
    Route::put(
        '/swine/{swine}/resolve-conflict',
        [SwineController::class, 'resolveConflict']
    )->name('swine.resolve-conflict');


    /*
     * Offline Movement Synchronization
     *
     * IMPORTANT:
     * This is required by:
     *
     * POST /swine-movements/sync
     */
    Route::post(
        '/swine-movements/sync',
        [SwineMovementController::class, 'syncStore']
    )->name('swine-movements.sync');

    Route::post(
        '/swine/{swine}/move',
        [SwineMovementController::class, 'store']
    )->name('swine-movements.store');


    /*
     * Offline Weight Record Synchronization
     */
    Route::post(
        '/weight-records/sync',
        [WeightRecordController::class, 'syncStore']
    )->name('weight-records.sync');


    /*
    |--------------------------------------------------------------------------
    | Synchronization Status
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sync-status',
        [SyncStatusController::class, 'index']
    )->name('sync-status.index');

});


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';