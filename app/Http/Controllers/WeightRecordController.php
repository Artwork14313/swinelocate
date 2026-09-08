<?php

namespace App\Http\Controllers;

use App\Models\Swine;
use App\Models\WeightRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeightRecordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Weight Records
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {

        $weightRecords = WeightRecord::query()
            ->with([
                'swine',
                'recordedBy'
            ])
            ->latest('record_date')
            ->latest('id')
            ->paginate(15);

        return view(
            'weight-records.index',
            compact('weightRecords')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        Request $request
    ): View {

        $swines = Swine::query()
            ->orderBy('tag_number')
            ->get();

        $selectedSwine = null;

        if ($request->filled('swine_id')) {

            $selectedSwine =
                Swine::find(
                    $request->swine_id
                );

        }

        return view(
            'weight-records.create',
            compact(
                'swines',
                'selectedSwine'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normal Online Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        $validated = $request->validate([

            'swine_id' => [
                'required',
                'exists:swine,id'
            ],

            'record_date' => [
                'required',
                'date'
            ],

            'weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99'
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ],

        ]);


        /*
         * Record the authenticated user
         * for normal online records.
         */
        $validated['recorded_by'] =
            auth()->id();


        /*
         * Normal online records do not
         * require a local_id.
         */
        WeightRecord::create(
            $validated
        );


        return redirect()
            ->route(
                'weight-records.index'
            )
            ->with(
                'success',
                'Weight record added successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Offline Synchronization
    |--------------------------------------------------------------------------
    */

    public function syncStore(
        Request $request
    ): JsonResponse {

        /*
         * Validate synchronization data.
         */
        $validated = $request->validate([

            'local_id' => [
                'required',
                'string',
                'max:100'
            ],

            'swine_id' => [
                'required',
                'exists:swine,id'
            ],

            'record_date' => [
                'required',
                'date'
            ],

            'weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99'
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ],

        ]);


        /*
         * IMPORTANT:
         *
         * Check whether this exact offline
         * record has already been synchronized.
         */
        $existingRecord =
            WeightRecord::query()
                ->where(
                    'local_id',
                    $validated['local_id']
                )
                ->first();


        /*
         * If it already exists, DO NOT create
         * another database record.
         *
         * This protects against:
         *
         * - duplicate requests
         * - phone reconnect timing
         * - multiple sync attempts
         * - network retries
         * - accidental repeated synchronization
         */
        if ($existingRecord) {

            return response()->json([

                'success' => true,

                'message' =>
                    'Weight record was already synchronized.',

                'weight_record_id' =>
                    $existingRecord->id,

                'already_synced' =>
                    true,

            ], 200);
        }


        /*
         * Record the authenticated user
         * who synchronized the record.
         */
        $validated['recorded_by'] =
            auth()->id();


        /*
         * Create the server-side record.
         */
        $weightRecord =
            WeightRecord::create(
                $validated
            );


        return response()->json([

            'success' => true,

            'message' =>
                'Offline weight record synchronized successfully.',

            'weight_record_id' =>
                $weightRecord->id,

            'already_synced' =>
                false,

        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        WeightRecord $weightRecord
    ): View {

        $weightRecord->load([
            'swine',
            'recordedBy'
        ]);

        return view(
            'weight-records.show',
            compact('weightRecord')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        WeightRecord $weightRecord
    ): View {

        $swines = Swine::query()
            ->orderBy('tag_number')
            ->get();

        return view(
            'weight-records.edit',
            compact(
                'weightRecord',
                'swines'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        WeightRecord $weightRecord
    ): RedirectResponse {

        $validated = $request->validate([

            'record_date' => [
                'required',
                'date'
            ],

            'weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99'
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ],

        ]);


        $weightRecord->update(
            $validated
        );


        return redirect()
            ->route(
                'weight-records.show',
                $weightRecord
            )
            ->with(
                'success',
                'Weight record updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        WeightRecord $weightRecord
    ): RedirectResponse {

        $weightRecord->delete();

        return redirect()
            ->route(
                'weight-records.index'
            );
    }
}