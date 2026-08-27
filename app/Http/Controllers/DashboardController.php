<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\HealthRecord;
use App\Models\Swine;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\SwineMovement;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | User Role
        |--------------------------------------------------------------------------
        */

        $role = $user->role?->slug
            ?? $user->role?->name
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | System Overview
        |--------------------------------------------------------------------------
        */

        $totalSwine = Swine::count();

        $activeSwine = Swine::where('status', 'active')->count();

        $totalFarms = Farm::count();

        $totalHealthRecords = HealthRecord::count();


        /*
|--------------------------------------------------------------------------
| Current Health Status Overview
|--------------------------------------------------------------------------
|
| Count only the latest health record of each swine.
|
*/

        $latestHealthRecords = HealthRecord::query()
            ->whereIn('id', function ($query) {

                $query->selectRaw('MAX(id)')
                    ->from('health_records')
                    ->whereNull('deleted_at')
                    ->groupBy('swine_id');

            })
            ->get();

        $healthStatusTotals = $latestHealthRecords
            ->groupBy('health_status')
            ->map
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Health Activity
        |--------------------------------------------------------------------------
        */

        $recentHealthActivity = HealthRecord::query()
            ->with('swine')
            ->latest('record_date')
            ->latest('id')
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Sick Swine
        |--------------------------------------------------------------------------
        */

        $latestHealthRecordIds = HealthRecord::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('swine_id');

        $sickSwine = HealthRecord::query()
            ->whereIn('id', $latestHealthRecordIds)
            ->where('health_status', 'sick')
            ->with('swine')
            ->latest('record_date')
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Vaccination Monitoring
        |--------------------------------------------------------------------------
        */

        $vaccinationRecords = HealthRecord::query()
            ->where('record_type', 'Vaccination')
            ->whereNotNull('next_due_date')
            ->get();

        $totalVaccinations = $vaccinationRecords->count();


        /*
        |--------------------------------------------------------------------------
        | Overdue Vaccinations
        |--------------------------------------------------------------------------
        */

        $overdueVaccinations = $vaccinationRecords
            ->filter(function ($record) {
                return $record->next_due_date->isPast()
                    && !$record->next_due_date->isToday();
            })
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Vaccinations Due Today
        |--------------------------------------------------------------------------
        */

        $dueTodayVaccinations = $vaccinationRecords
            ->filter(function ($record) {
                return $record->next_due_date->isToday();
            })
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Vaccinations Due Within 7 Days
        |--------------------------------------------------------------------------
        */

        $dueSoonVaccinations = $vaccinationRecords
            ->filter(function ($record) {
                return $record->next_due_date->isFuture()
                    && now()->diffInDays($record->next_due_date) <= 7;
            })
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Vaccination Alerts
        |--------------------------------------------------------------------------
        */

        $vaccinationAlerts = $vaccinationRecords
            ->filter(function ($record) {
                return $record->next_due_date->isPast()
                    || $record->next_due_date->isToday()
                    || (
                        $record->next_due_date->isFuture()
                        && now()->diffInDays($record->next_due_date) <= 7
                    );
            })
            ->sortBy('next_due_date')
            ->take(10);


        /*
        |--------------------------------------------------------------------------
        | Swine Population Overview
        |--------------------------------------------------------------------------
        */

        $swineByStatus = Swine::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();


        $swineByBreed = Swine::query()
            ->select('breed')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('breed')
            ->orderByDesc('total')
            ->get();


        $recentMovementActivity = SwineMovement::query()
            ->with([
                'swine',
                'fromLocation',
                'toLocation',
                'recordedBy',
            ])
            ->latest('movement_date')
            ->latest('id')
            ->take(5)
            ->get();
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        return view('dashboard', compact(
            'role',

            // System overview
            'totalSwine',
            'activeSwine',
            'totalFarms',
            'totalHealthRecords',

            // Health
            'healthStatusTotals',
            'recentHealthActivity',
            'sickSwine',

            // Vaccinations
            'totalVaccinations',
            'overdueVaccinations',
            'dueTodayVaccinations',
            'dueSoonVaccinations',
            'vaccinationAlerts',

            // Swine population
            'swineByStatus',
            'swineByBreed',

            'recentMovementActivity',
        ));
    }
}