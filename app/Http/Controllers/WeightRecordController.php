<?php

namespace App\Http\Controllers;

use App\Models\Swine;
use App\Models\WeightRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WeightRecordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Weight Records
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $weightRecords = WeightRecord::query()
            ->with([
                'swine',
                'recordedBy',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->whereHas('swine', function ($swineQuery) use ($search) {
                    $swineQuery->where(
                        'tag_number',
                        'like',
                        "%{$search}%"
                    );
                });
            })
            ->latest('record_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

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

    public function create(Request $request): View
    {
        /*
         * Only active swine can receive
         * new weight records.
         */
        $swines = Swine::query()
            ->where('status', 'active')
            ->orderBy('tag_number')
            ->get();

        $selectedSwine = null;

        if ($request->filled('swine_id')) {
            $selectedSwine = Swine::query()
                ->where('status', 'active')
                ->find($request->swine_id);
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([

            'swine_id' => [
                'required',
                Rule::exists('swine', 'id')
                    ->where('status', 'active'),
            ],

            'record_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

        ]);


        /*
         * Record the authenticated user
         * for normal online records.
         */
        $validated['recorded_by'] = auth()->id();


        WeightRecord::create($validated);


        return redirect()
            ->route('weight-records.index')
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

    public function syncStore(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'local_id' => [
                'required',
                'string',
                'max:100',
            ],

            'swine_id' => [
                'required',
                Rule::exists('swine', 'id')
                    ->where('status', 'active'),
            ],

            'record_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

        ]);


        /*
         * Prevent duplicate synchronization.
         *
         * The same local record may be sent more
         * than once because of network retries.
         */
        $existingRecord = WeightRecord::query()
            ->where(
                'local_id',
                $validated['local_id']
            )
            ->first();


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
         * Record the authenticated user who
         * synchronized the offline record.
         */
        $validated['recorded_by'] =
            auth()->id();


        $weightRecord = WeightRecord::create(
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
            'recordedBy',
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

        /*
         * Include active swine plus the swine
         * currently associated with this record.
         *
         * This allows historical records to remain
         * editable even after the swine becomes
         * inactive, sold, or deceased.
         */
        $swines = Swine::query()
            ->where(function ($query) use ($weightRecord) {
                $query->where('status', 'active')
                    ->orWhere('id', $weightRecord->swine_id);
            })
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
                'date',
                'before_or_equal:today',
            ],

            'weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
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
            ->route('weight-records.index')
            ->with(
                'success',
                'Weight record deleted successfully.'
            );
    }
}