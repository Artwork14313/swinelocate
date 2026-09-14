<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\FarmLocation;
use App\Models\Swine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SwineController extends Controller
{
    /**
     * Display a listing of swine.
     */
    public function index(Request $request): View
    {
        $swine = Swine::query()
            ->with([
                'farm',
                'currentLocation',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($query) use ($search) {
                    $query->where('tag_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('breed', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

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
        | Validate Farm
        |--------------------------------------------------------------------------
        */

        $farm = Farm::query()
            ->where('id', $validated['farm_id'])
            ->where('status', 'active')
            ->first();

        if (!$farm) {
            return back()
                ->withInput()
                ->withErrors([
                    'farm_id' =>
                        'The selected farm is inactive and cannot receive new swine.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Farm Location
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where('id', $validated['current_location_id'])
                ->where('farm_id', $validated['farm_id'])
                ->where('status', 'active')
                ->exists();

            if (!$validLocation) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'current_location_id' =>
                            'The selected location is invalid, inactive, or does not belong to the selected farm.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Set Default Status
        |--------------------------------------------------------------------------
        */

        $validated['status'] = 'active';

        /*
        |--------------------------------------------------------------------------
        | Create Swine
        |--------------------------------------------------------------------------
        |
        | The Swine model automatically generates the QR token.
        |
        */

        Swine::create($validated);

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
        ]);

        $qrCode = QrCode::size(220)->generate(
            route('swine.scan', [
                'qr_token' => $swine->qr_token,
            ])
        );

        return view('swine.show', compact(
            'swine',
            'qrCode'
        ));
    }

    /**
     * Show the form for editing the specified swine.
     */
    public function edit(Swine $swine): View
    {
        if ($swine->farm && $swine->farm->status !== 'active') {
            abort(
                403,
                'This swine cannot be edited because its farm is inactive.'
            );
        }

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
    public function update(Request $request, Swine $swine): RedirectResponse
    {
        if ($swine->farm && $swine->farm->status !== 'active') {
            abort(
                403,
                'This action is not available because the farm is inactive.'
            );
        }

        $validated = $request->validate([
            'farm_id' => ['required', 'exists:farms,id'],
            'current_location_id' => ['nullable', 'exists:farm_locations,id'],
            'tag_number' => [
                'required',
                'string',
                'max:100',
                'unique:swine,tag_number,' . $swine->id,
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'sex' => ['required', 'in:male,female'],
            'breed' => ['nullable', 'string', 'max:100'],
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
            'source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $farm = Farm::findOrFail($validated['farm_id']);

        if ($farm->status !== 'active') {
            return back()
                ->withInput()
                ->withErrors([
                    'farm_id' => 'The selected farm is inactive.',
                ]);
        }

        if (!empty($validated['current_location_id'])) {
            $location = FarmLocation::findOrFail(
                $validated['current_location_id']
            );

            if (
                $location->farm_id !== $farm->id ||
                $location->status !== 'active'
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'current_location_id' =>
                            'The selected location is invalid or inactive.',
                    ]);
            }
        }

        $swine->update($validated);

        return redirect()
            ->route('swine.show', $swine)
            ->with('success', 'Swine record updated successfully.');
    }

    /**
     * Deactivate the specified swine.
     */
    public function destroy(Swine $swine): RedirectResponse
    {
        if ($swine->status === 'inactive') {
            return redirect()
                ->route('swine.index')
                ->with(
                    'error',
                    'Swine is already inactive.'
                );
        }

        $swine->update([
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('swine.index')
            ->with(
                'success',
                'Swine deactivated successfully.'
            );
    }

    /**
     * Activate the specified swine.
     */
    public function activate(Swine $swine): RedirectResponse
    {
        if ($swine->status === 'active') {
            return redirect()
                ->route('swine.index')
                ->with(
                    'error',
                    'Swine is already active.'
                );
        }

        if (
            !$swine->farm ||
            $swine->farm->status !== 'active'
        ) {
            return redirect()
                ->route('swine.index')
                ->with(
                    'error',
                    'This swine cannot be activated because its farm is inactive.'
                );
        }

        $swine->update([
            'status' => 'active',
        ]);

        return redirect()
            ->route('swine.index')
            ->with(
                'success',
                'Swine activated successfully.'
            );
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
    ): JsonResponse {

        if ($swine->farm && $swine->farm->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' =>
                    'This swine cannot be updated because its farm is inactive.',
            ], 422);
        }

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

            'notes' => [
                'nullable',
                'string',
            ],

            'original_updated_at' => [
                'required',
                'date',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verify Swine ID
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
        | Verify Farm
        |--------------------------------------------------------------------------
        */

        $farm = Farm::query()
            ->where('id', $validated['farm_id'])
            ->where('status', 'active')
            ->first();

        if (!$farm) {
            return response()->json([
                'success' => false,
                'message' =>
                    'The selected farm is inactive and cannot receive this swine.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Location
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where('id', $validated['current_location_id'])
                ->where('farm_id', $validated['farm_id'])
                ->where('status', 'active')
                ->exists();

            if (!$validLocation) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'The selected location is invalid, inactive, or does not belong to the selected farm.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Detect Conflict
        |--------------------------------------------------------------------------
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
            return response()->json([
                'success' => false,

                'conflict' => true,

                'message' =>
                    'This swine was modified by another user while this device was offline.',

                'swine_id' =>
                    $swine->id,

                'server_updated_at' =>
                    $swine->updated_at->toISOString(),

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
                        ? $swine->birth_date->toDateString()
                        : null,

                    'acquisition_date' =>
                        $swine->acquisition_date
                        ? $swine->acquisition_date->toDateString()
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
                        $swine->updated_at->toISOString(),
                ],
            ], 409);
        }

        /*
        |--------------------------------------------------------------------------
        | Apply Update
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

* Display the swine traceability page from a QR code.
*
* The QR token identifies the swine record.
* Inactive swine are still accessible so their historical
* traceability records remain available.
  */
    public function scan(string $qr_token): View
    {
        $swine = Swine::query()
            ->where('qr_token', $qr_token)
            ->with([
                'farm',
                'currentLocation',
                'movements.fromLocation',
                'movements.toLocation',
                'healthRecords',
                'weightRecords',
            ])
            ->firstOrFail();

        return view('swine.scan', compact('swine'));
    }


    /**
     * Store an offline swine registration
     * synchronized from the browser.
     */
    public function syncStore(Request $request): JsonResponse
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
        | Validate Farm
        |--------------------------------------------------------------------------
        */

        $farm = Farm::query()
            ->where('id', $validated['farm_id'])
            ->where('status', 'active')
            ->first();

        if (!$farm) {
            return response()->json([
                'success' => false,
                'message' =>
                    'The selected farm is inactive and cannot receive new swine.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Farm Location
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where('id', $validated['current_location_id'])
                ->where('farm_id', $validated['farm_id'])
                ->where('status', 'active')
                ->exists();

            if (!$validLocation) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'The selected location is invalid, inactive, or does not belong to the selected farm.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Set Default Status
        |--------------------------------------------------------------------------
        */

        $validated['status'] = 'active';

        /*
        |--------------------------------------------------------------------------
        | Create Swine
        |--------------------------------------------------------------------------
        */

        $swine = Swine::create($validated);

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
     * Applies the offline version after the user
     * chooses "Keep Offline Version".
     */
    public function resolveConflict(
        Request $request,
        Swine $swine
    ): JsonResponse {

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
                'nullable',
                'in:active,inactive',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Farm
        |--------------------------------------------------------------------------
        */

        $farm = Farm::query()
            ->where('id', $validated['farm_id'])
            ->where('status', 'active')
            ->first();

        if (!$farm) {
            return response()->json([
                'success' => false,
                'message' =>
                    'The selected farm is inactive and cannot receive this swine.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Location
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where('id', $validated['current_location_id'])
                ->where('farm_id', $validated['farm_id'])
                ->where('status', 'active')
                ->exists();

            if (!$validLocation) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'The selected location is invalid, inactive, or does not belong to the selected farm.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Preserve QR Token
        |--------------------------------------------------------------------------
        */

        $validated['qr_token'] = $swine->qr_token;

        /*
        |--------------------------------------------------------------------------
        | Apply Offline Version
        |--------------------------------------------------------------------------
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
