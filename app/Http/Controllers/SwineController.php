<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\FarmLocation;
use App\Models\Swine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;


class SwineController extends Controller
{
    /**
     * Display a listing of swine.
     */
    public function index(): View
    {
        $swine = Swine::with([
            'farm',
            'currentLocation',
        ])
            ->latest()
            ->paginate(10);

        return view('swine.index', compact('swine'));
    }

    /**
     * Show the form for registering a new swine.
     */
    public function create(): View
    {
        $farms = Farm::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $locations = FarmLocation::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('swine.create', compact(
            'farms',
            'locations'
        ));
    }

    /**
     * Store a newly registered swine.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farm_id' => [
                'required',
                'exists:farms,id',
            ],

            'current_location_id' => [
                'nullable',
                'exists:farm_locations,id',
            ],

            'tag_number' => [
                'required',
                'string',
                'max:255',
                'unique:swine,tag_number',
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sex' => [
                'required',
                'in:male,female',
            ],

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'acquisition_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Farm Location
        |--------------------------------------------------------------------------
        |
        | Make sure the selected location actually belongs to the
        | selected farm.
        |
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where('id', $validated['current_location_id'])
                ->where('farm_id', $validated['farm_id'])
                ->exists();

            if (!$validLocation) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'current_location_id' =>
                            'The selected location does not belong to the selected farm.',
                    ]);
            }
        }

        $validated['status'] = 'active';
        $validated['qr_token'] = Str::uuid()->toString();

        $swine = Swine::create($validated);


        return redirect()
            ->route('swine.index')
            ->with('success', 'Swine registered successfully.');
    }

    /**
     * Display the specified swine.
     */
    public function show(Swine $swine): View
    {
        $swine->load([
            'farm',
            'currentLocation',
            'movements.fromLocation',
            'movements.toLocation',
            'movements.recordedBy',
        ]);

        $qrCode = QrCode::size(220)->generate(
            route('swine.scan', [
                'qr_token' => $swine->qr_token,
            ])
        );

        return view('swine.show', compact('swine', 'qrCode'));
    }


    /**
     * Show the form for editing the specified swine.
     */
    public function edit(Swine $swine): View
    {
        $farms = Farm::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $locations = FarmLocation::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('swine.edit', compact(
            'swine',
            'farms',
            'locations'
        ));
    }

    /**
     * Update the specified swine.
     */
    public function update(
        Request $request,
        Swine $swine
    ): RedirectResponse {

        $validated = $request->validate([
            'farm_id' => [
                'required',
                'exists:farms,id',
            ],

            'current_location_id' => [
                'nullable',
                'exists:farm_locations,id',
            ],

            'tag_number' => [
                'required',
                'string',
                'max:255',
                'unique:swine,tag_number,' . $swine->id,
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sex' => [
                'required',
                'in:male,female',
            ],

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'acquisition_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where('id', $validated['current_location_id'])
                ->where('farm_id', $validated['farm_id'])
                ->exists();

            if (!$validLocation) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'current_location_id' =>
                            'The selected location does not belong to the selected farm.',
                    ]);
            }
        }

        $swine->update($validated);

        return redirect()
            ->route('swine.index')
            ->with('success', 'Swine information updated successfully.');
    }


    /**
     * Synchronize an offline swine update.
     *
     * Detects whether the server record was modified
     * after the offline device loaded the record.
     */
    public function syncUpdate(
        Request $request,
        Swine $swine
    ): \Illuminate\Http\JsonResponse {

        $validated = $request->validate([
            'swine_id' => [
                'required',
                'integer',
            ],

            'farm_id' => [
                'required',
                'exists:farms,id',
            ],

            'current_location_id' => [
                'nullable',
                'exists:farm_locations,id',
            ],

            'tag_number' => [
                'required',
                'string',
                'max:255',
                'unique:swine,tag_number,' . $swine->id,
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sex' => [
                'required',
                'in:male,female',
            ],

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'acquisition_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'string',
                'max:255',
            ],

            'qr_token' => [
                'nullable',
                'string',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            /*
             * This is the timestamp of the version
             * the offline device originally loaded.
             */
            'original_updated_at' => [
                'required',
                'date',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Verify that the request belongs to this swine
        |--------------------------------------------------------------------------
        */

        if (
            (int) $validated['swine_id'] !==
            (int) $swine->id
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid swine record.',
            ], 422);

        }


        /*
        |--------------------------------------------------------------------------
        | Detect conflict
        |--------------------------------------------------------------------------
        |
        | If Laravel's updated_at is newer than the timestamp
        | stored by the offline device, another user modified
        | the record while this device was offline.
        |
        */

        $originalUpdatedAt =
            \Carbon\Carbon::parse(
                $validated['original_updated_at']
            );


        if (
            $swine->updated_at->gt(
                $originalUpdatedAt
            )
        ) {

            /*
             * Return the CURRENT server version.
             *
             * This allows the frontend to compare:
             *
             * Offline Version
             * VS.
             * Server Version
             */
            return response()->json([
                'success' => false,

                'conflict' => true,

                'message' =>
                    'This swine was modified by another user while this device was offline.',

                'swine_id' =>
                    $swine->id,

                'server_updated_at' =>
                    $swine->updated_at
                        ->toISOString(),

                'server_data' => [
                    'swine_id' =>
                        $swine->id,

                    'farm_id' =>
                        $swine->farm_id,

                    'current_location_id' =>
                        $swine->current_location_id,

                    'tag_number' =>
                        $swine->tag_number,

                    'name' =>
                        $swine->name,

                    'sex' =>
                        $swine->sex,

                    'breed' =>
                        $swine->breed,

                    'birth_date' =>
                        $swine->birth_date
                        ? $swine->birth_date
                            ->toDateString()
                        : null,

                    'acquisition_date' =>
                        $swine->acquisition_date
                        ? $swine->acquisition_date
                            ->toDateString()
                        : null,

                    'source' =>
                        $swine->source,

                    'status' =>
                        $swine->status,

                    'qr_token' =>
                        $swine->qr_token,

                    'notes' =>
                        $swine->notes,

                    'updated_at' =>
                        $swine->updated_at
                            ->toISOString(),
                ],

            ], 409);

        }


        /*
        |--------------------------------------------------------------------------
        | No conflict
        |--------------------------------------------------------------------------
        */

        $swine->update([
            'farm_id' =>
                $validated['farm_id'],

            'current_location_id' =>
                $validated['current_location_id'] ?? null,

            'tag_number' =>
                $validated['tag_number'],

            'name' =>
                $validated['name'] ?? null,

            'sex' =>
                $validated['sex'],

            'breed' =>
                $validated['breed'] ?? null,

            'birth_date' =>
                $validated['birth_date'] ?? null,

            'acquisition_date' =>
                $validated['acquisition_date'] ?? null,

            'source' =>
                $validated['source'] ?? null,

            'status' =>
                $validated['status'],

            'qr_token' =>
                $validated['qr_token'] ?? $swine->qr_token,

            'notes' =>
                $validated['notes'] ?? null,
        ]);


        return response()->json([
            'success' => true,

            'conflict' => false,

            'message' =>
                'Swine updated successfully.',

            'swine_id' =>
                $swine->id,

        ]);

    }


    /**
     * Remove the specified swine.
     */
    public function destroy(Swine $swine): RedirectResponse
    {
        $swine->delete();

        return redirect()
            ->route('swine.index')
            ->with('success', 'Swine record deleted successfully.');
    }

    public function scan(string $qr_token): View
    {
        $swine = Swine::query()
            ->where('qr_token', $qr_token)
            ->with([
                'farm',
                'currentLocation',
                'movements.fromLocation',
                'movements.toLocation',
            ])
            ->firstOrFail();

        return view('swine.scan', compact('swine'));
    }

    /**
     * Store an offline swine registration
     * synchronized from the browser.
     */
    public function syncStore(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => [
                'required',
                'exists:farms,id',
            ],

            'current_location_id' => [
                'nullable',
                'exists:farm_locations,id',
            ],

            'tag_number' => [
                'required',
                'string',
                'max:255',
                'unique:swine,tag_number',
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sex' => [
                'required',
                'in:male,female',
            ],

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'acquisition_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'qr_token' => [
                'required',
                'string',
                'max:255',
                'unique:swine,qr_token',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Farm Location
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where(
                    'id',
                    $validated['current_location_id']
                )
                ->where(
                    'farm_id',
                    $validated['farm_id']
                )
                ->exists();

            if (!$validLocation) {

                return response()->json([
                    'message' =>
                        'The selected location does not belong to the selected farm.',
                ], 422);

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Set defaults
        |--------------------------------------------------------------------------
        */

        $validated['status'] = 'active';


        /*
        |--------------------------------------------------------------------------
        | Create swine
        |--------------------------------------------------------------------------
        */

        $swine = Swine::create(
            $validated
        );


        /*
        |--------------------------------------------------------------------------
        | Return successful synchronization response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' =>
                'Swine synchronized successfully.',

            'swine_id' =>
                $swine->id,

        ], 201);
    }

    /**
     * Resolve an offline synchronization conflict.
     *
     * This intentionally applies the offline version
     * after the user chooses "Keep Offline Version".
     */
    public function resolveConflict(
        Request $request,
        Swine $swine
    ) {
        $validated = $request->validate([
            'farm_id' => [
                'required',
                'exists:farms,id',
            ],

            'current_location_id' => [
                'nullable',
                'exists:farm_locations,id',
            ],

            'tag_number' => [
                'required',
                'string',
                'max:255',
                'unique:swine,tag_number,' . $swine->id,
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sex' => [
                'required',
                'in:male,female',
            ],

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'acquisition_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Location Belongs To Farm
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation =
                FarmLocation::query()
                    ->where(
                        'id',
                        $validated['current_location_id']
                    )
                    ->where(
                        'farm_id',
                        $validated['farm_id']
                    )
                    ->exists();

            if (!$validLocation) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'The selected location does not belong to the selected farm.',
                ], 422);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Update Only Editable Fields
        |--------------------------------------------------------------------------
        |
        | QR TOKEN IS NOT INCLUDED.
        |
        */

        $swine->update($validated);


        return response()->json([
            'success' => true,
            'message' =>
                'Offline version successfully applied.',
            'swine_id' =>
                $swine->id,
            'qr_token' =>
                $swine->qr_token,
        ]);
    }
}