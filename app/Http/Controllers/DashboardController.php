<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use Illuminate\View\View;
use App\Models\Farm;
use App\Models\Swine;

class DashboardController extends Controller
{
    public function index(): View
    {
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
        | Vaccination Monitoring
        |--------------------------------------------------------------------------
        */

        $vaccinationRecords = HealthRecord::query()
            ->where('record_type', 'Vaccination')
            ->whereNotNull('next_due_date')
            ->get();

        $totalVaccinations = $vaccinationRecords->count();

        $overdueVaccinations = $vaccinationRecords
            ->filter(fn($record) => $record->next_due_date->isPast())
            ->count();

        $dueTodayVaccinations = $vaccinationRecords
            ->filter(fn($record) => $record->next_due_date->isToday())
            ->count();

        $dueSoonVaccinations = $vaccinationRecords
            ->filter(function ($record) {
                return $record->next_due_date->isFuture()
                    && now()->diffInDays($record->next_due_date) <= 7;
            })
            ->count();

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
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        return view('dashboard', compact(
            'totalSwine',
            'activeSwine',
            'totalFarms',
            'totalHealthRecords',

            'totalVaccinations',
            'overdueVaccinations',
            'dueTodayVaccinations',
            'dueSoonVaccinations',
            'vaccinationAlerts',

            'swineByStatus',
            'swineByBreed'
        ));
    }
}