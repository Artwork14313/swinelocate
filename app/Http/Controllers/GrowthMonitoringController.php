<?php

namespace App\Http\Controllers;

use App\Models\Swine;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrowthMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Swine List
        |--------------------------------------------------------------------------
        |
        | Only active swine are shown in the selector because Growth Monitoring
        | is intended for currently active animals.
        |
        */

        $swines = Swine::query()
            ->where('status', 'active')
            ->orderBy('tag_number')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Selected Swine
        |--------------------------------------------------------------------------
        */

        $selectedSwine = null;

        if ($request->filled('swine_id')) {
            $selectedSwine = Swine::query()
                ->with([
                    'farm',
                    'currentLocation',
                    'weightRecords' => function ($query) {
                        $query
                            ->orderBy('record_date')
                            ->orderBy('id');
                    },
                ])
                ->find($request->swine_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Growth Data
        |--------------------------------------------------------------------------
        */

        $weightRecords = collect();

        $currentWeight = null;
        $previousWeight = null;
        $totalWeightGain = null;
        $growthPeriod = null;
        $averageDailyGain = null;

        if ($selectedSwine) {
            $weightRecords = $selectedSwine->weightRecords;

            /*
            |--------------------------------------------------------------------------
            | Current Weight
            |--------------------------------------------------------------------------
            */

            if ($weightRecords->count() >= 1) {
                $latestRecord = $weightRecords->last();

                $currentWeight = (float) $latestRecord->weight;

                /*
                |--------------------------------------------------------------------------
                | Previous Weight
                |--------------------------------------------------------------------------
                */

                if ($weightRecords->count() >= 2) {
                    $previousRecord = $weightRecords->get(
                        $weightRecords->count() - 2
                    );

                    $previousWeight = (float) $previousRecord->weight;

                    /*
                    |--------------------------------------------------------------------------
                    | Weight Gain
                    |--------------------------------------------------------------------------
                    */

                    $totalWeightGain = $currentWeight - $previousWeight;

                    /*
                    |--------------------------------------------------------------------------
                    | Growth Period
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $previousRecord->record_date &&
                        $latestRecord->record_date
                    ) {
                        $growthPeriod = $previousRecord
                            ->record_date
                            ->diffInDays($latestRecord->record_date);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Average Daily Gain
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $growthPeriod !== null &&
                        $growthPeriod > 0
                    ) {
                        $averageDailyGain =
                            $totalWeightGain / $growthPeriod;
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Chart Data
        |--------------------------------------------------------------------------
        |
        | The chart displays the complete recorded weight history for the
        | selected swine.
        |
        */

        $chartLabels = $weightRecords
            ->map(function ($record) {
                return $record->record_date
                    ? $record->record_date->format('M d, Y')
                    : '';
            })
            ->values()
            ->all();

        $chartWeights = $weightRecords
            ->map(function ($record) {
                return (float) $record->weight;
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view('growth-monitoring.index', compact(
            'swines',
            'selectedSwine',
            'weightRecords',
            'currentWeight',
            'previousWeight',
            'totalWeightGain',
            'growthPeriod',
            'averageDailyGain',
            'chartLabels',
            'chartWeights'
        ));
    }
}