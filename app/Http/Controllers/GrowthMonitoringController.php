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
        */

        $swines = Swine::query()
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
                    'weightRecords' => function ($query) {
                        $query
                            ->orderBy('record_date')
                            ->orderBy('id');
                    }
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


            if ($weightRecords->count() >= 1) {

                $latestRecord = $weightRecords->last();

                $currentWeight = (float) $latestRecord->weight;


                /*
                |----------------------------------------------------------------------
                | Previous Weight
                |----------------------------------------------------------------------
                */

                if ($weightRecords->count() >= 2) {

                    $previousRecord = $weightRecords->get(
                        $weightRecords->count() - 2
                    );

                    $previousWeight = (float) $previousRecord->weight;


                    /*
                    |------------------------------------------------------------------
                    | Weight Gain
                    |------------------------------------------------------------------
                    */

                    $totalWeightGain = $currentWeight - $previousWeight;


                    /*
                    |------------------------------------------------------------------
                    | Growth Period
                    |------------------------------------------------------------------
                    */

                    $growthPeriod = $previousRecord
                        ->record_date
                        ->diffInDays(
                            $latestRecord->record_date
                        );


                    /*
                    |------------------------------------------------------------------
                    | Average Daily Gain
                    |------------------------------------------------------------------
                    */

                    if ($growthPeriod > 0) {

                        $averageDailyGain =
                            $totalWeightGain / $growthPeriod;

                    } else {

                        $averageDailyGain = null;

                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Chart Data
        |--------------------------------------------------------------------------
        */

        $chartLabels = $weightRecords
            ->map(function ($record) {

                return $record->record_date
                    ->format('M d, Y');

            })
            ->values();

        $chartWeights = $weightRecords
            ->map(function ($record) {

                return (float) $record->weight;

            })
            ->values();


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