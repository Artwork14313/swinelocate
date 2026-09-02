<?php

namespace App\Http\Controllers;

use App\Models\FarmLocation;
use App\Models\Swine;
use App\Models\SwineMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SwineMovementController extends Controller
{
    /**
     * Display all swine movements.
     */
    public function index(Request $request): View
    {
        $movements = SwineMovement::query()
            ->with([
                'swine',
                'fromLocation',
                'toLocation',
                'recordedBy',
            ])

            ->when($request->filled('search'), function ($query) use ($request) {

                $search = trim($request->search);

                $query->whereHas('swine', function ($swineQuery) use ($search) {

                    $swineQuery->where(function ($query) use ($search) {

                        $query
                            ->where('tag_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");

                    });

                });

            })

            ->when($request->filled('from_date'), function ($query) use ($request) {

                $query->whereDate(
                    'movement_date',
                    '>=',
                    $request->from_date
                );

            })

            ->when($request->filled('to_date'), function ($query) use ($request) {

                $query->whereDate(
                    'movement_date',
                    '<=',
                    $request->to_date
                );

            })

            ->latest('movement_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $totalMovements = SwineMovement::count();

        $todayMovements = SwineMovement::query()
            ->whereDate('movement_date', today())
            ->count();

        $thisMonthMovements = SwineMovement::query()
            ->whereMonth('movement_date', now()->month)
            ->whereYear('movement_date', now()->year)
            ->count();


        return view('swine-movements.index', compact(
            'movements',
            'totalMovements',
            'todayMovements',
            'thisMonthMovements'
        ));
    }


    /**
     * Display the form for recording a movement.
     *
     * The swine is selected from the Swine Index.
     */
    public function create(Swine $swine): View
    {
        /*
        |--------------------------------------------------------------------------
        | Only active swine can be moved.
        |--------------------------------------------------------------------------
        */

        if ($swine->status !== 'active') {

            abort(
                403,
                'Only active swine can be moved.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Load current location.
        |--------------------------------------------------------------------------
        */

        $swine->load('currentLocation');


        /*
        |--------------------------------------------------------------------------
        | Get active locations belonging to
        | the same farm as the swine.
        |--------------------------------------------------------------------------
        */

        $locations = FarmLocation::query()
            ->where('farm_id', $swine->farm_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();


        return view('swine-movements.create', [
            'swine' => $swine,
            'locations' => $locations,
        ]);
    }


    /**
     * Store a new swine movement.
     */
    public function store(
        Request $request,
        Swine $swine
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Prevent movement of inactive, sold,
        | or deceased swine.
        |--------------------------------------------------------------------------
        */

        if ($swine->status !== 'active') {

            return redirect()
                ->route('swine-movements.index')
                ->with(
                    'error',
                    'Only active swine can be moved.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate movement information.
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'to_location_id' => [
                'required',
                'integer',
                'exists:farm_locations,id',
            ],

            'movement_date' => [
                'required',
                'date',
            ],

            'reason' => [
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
        | Validate destination.
        |--------------------------------------------------------------------------
        |
        | Destination must:
        | 1. Exist
        | 2. Belong to the same farm
        | 3. Be active
        |--------------------------------------------------------------------------
        */

        $destination = FarmLocation::query()
            ->where('id', $validated['to_location_id'])
            ->where('farm_id', $swine->farm_id)
            ->where('status', 'active')
            ->first();


        if (!$destination) {

            return back()
                ->withErrors([
                    'to_location_id' =>
                        'The selected destination is not a valid location for this farm.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent movement to the current location.
        |--------------------------------------------------------------------------
        */

        if (
            $swine->current_location_id !== null &&
            (int) $swine->current_location_id === (int) $destination->id
        ) {

            return back()
                ->withErrors([
                    'to_location_id' =>
                        'The swine is already assigned to this location.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Record movement and update swine location.
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($swine, $destination, $validated) {

            /*
             * Save the swine's current location
             * before changing it.
             */
            $fromLocationId = $swine->current_location_id;


            /*
             * Create movement history.
             */
            SwineMovement::create([
                'swine_id' => $swine->id,

                'from_location_id' => $fromLocationId,

                'to_location_id' => $destination->id,

                'movement_date' => $validated['movement_date'],

                'reason' => $validated['reason'] ?? null,

                'notes' => $validated['notes'] ?? null,

                'recorded_by' => auth()->id(),
            ]);


            /*
             * Update the swine's current location.
             */
            $swine->update([
                'current_location_id' => $destination->id,
            ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Return to Movement History.
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('swine-movements.index')
            ->with(
                'success',
                'Swine movement recorded successfully.'
            );
    }

    /**
     * Synchronize an offline swine movement.
     *
     * Detects whether the swine was moved by another user
     * while this device was offline.
     */
    public function syncStore(Request $request)
    {
        $validated = $request->validate([
            'swine_id' => [
                'required',
                'integer',
                'exists:swine,id',
            ],

            'from_location_id' => [
                'nullable',
                'integer',
                'exists:farm_locations,id',
            ],

            'to_location_id' => [
                'required',
                'integer',
                'exists:farm_locations,id',
            ],

            'movement_date' => [
                'required',
                'date',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            /*
             * Location that the device saw BEFORE
             * creating the offline movement.
             */
            'original_location_id' => [
                'nullable',
                'integer',
            ],

            /*
             * Used only after the user explicitly chooses
             * "Keep Offline Version".
             */
            'force' => [
                'nullable',
                'boolean',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Load Swine
        |--------------------------------------------------------------------------
        */

        $swine = Swine::findOrFail(
            $validated['swine_id']
        );


        /*
        |--------------------------------------------------------------------------
        | Validate Swine Status
        |--------------------------------------------------------------------------
        */

        if ($swine->status !== 'active') {

            return response()->json([
                'success' => false,

                'message' =>
                    'Only active swine can be moved.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Destination
        |--------------------------------------------------------------------------
        */

        $destination = FarmLocation::query()
            ->where('id', $validated['to_location_id'])
            ->where('farm_id', $swine->farm_id)
            ->where('status', 'active')
            ->first();


        if (!$destination) {

            return response()->json([
                'success' => false,

                'message' =>
                    'The selected destination is not a valid location for this farm.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | FORCE / KEEP OFFLINE VERSION
        |--------------------------------------------------------------------------
        |
        | The user has explicitly chosen:
        |
        | "Keep Offline Version"
        |
        | At this point we allow the offline movement to replace
        | the server's current location.
        |
        */

        if ($request->boolean('force')) {

            return DB::transaction(function () use ($swine, $destination, $validated) {

                /*
                 * IMPORTANT:
                 *
                 * The from location shown in the movement history
                 * should be the ACTUAL current server location,
                 * not the old offline location.
                 */
                $fromLocationId =
                    $swine->current_location_id;


                /*
                 * Create movement history.
                 */
                $movement = SwineMovement::create([

                    'swine_id' =>
                        $swine->id,

                    'from_location_id' =>
                        $fromLocationId,

                    'to_location_id' =>
                        $destination->id,

                    'movement_date' =>
                        $validated['movement_date'],

                    'reason' =>
                        $validated['reason'] ?? null,

                    'notes' =>
                        $validated['notes'] ?? null,

                    'recorded_by' =>
                        auth()->id(),

                ]);


                /*
                 * Explicitly overwrite the server location.
                 */
                $swine->update([

                    'current_location_id' =>
                        $destination->id,

                ]);


                return response()->json([

                    'success' => true,

                    'conflict' => false,

                    'forced' => true,

                    'movement_id' =>
                        $movement->id,

                    'swine_id' =>
                        $swine->id,

                    'message' =>
                        'Offline movement version was saved to the server.',

                ]);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | CONFLICT DETECTION
        |--------------------------------------------------------------------------
        |
        | The offline device sends the location that existed when
        | the movement was originally created.
        |
        | Example:
        |
        | Offline original location = 2
        | Server current location    = 3
        |
        | Therefore another user changed the swine.
        |
        */

        $originalLocationId =
            $validated['original_location_id'] ?? null;


        if ($originalLocationId !== null) {

            $serverLocationId =
                $swine->current_location_id;


            if (
                (int) $serverLocationId !==
                (int) $originalLocationId
            ) {

                return response()->json([

                    'success' => false,

                    'conflict' => true,

                    'message' =>
                        'This swine was moved by another user while this device was offline.',

                    'swine_id' =>
                        $swine->id,

                    /*
                     * Useful for the conflict-resolution UI.
                     */
                    'offline_data' => [

                        'swine_id' =>
                            $swine->id,

                        'original_location_id' =>
                            $originalLocationId,

                        'from_location_id' =>
                            $validated['from_location_id'] ?? null,

                        'to_location_id' =>
                            $destination->id,

                        'movement_date' =>
                            $validated['movement_date'],

                        'reason' =>
                            $validated['reason'] ?? null,

                        'notes' =>
                            $validated['notes'] ?? null,

                    ],

                    /*
                     * Current authoritative server state.
                     */
                    'server_data' => [

                        'swine_id' =>
                            $swine->id,

                        'current_location_id' =>
                            $swine->current_location_id,

                        'tag_number' =>
                            $swine->tag_number,

                        'status' =>
                            $swine->status,

                        'updated_at' =>
                            $swine->updated_at?->toISOString(),

                    ],

                ], 409);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PREVENT MOVING TO CURRENT SERVER LOCATION
        |--------------------------------------------------------------------------
        */

        if (
            $swine->current_location_id !== null &&
            (int) $swine->current_location_id ===
            (int) $destination->id
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    'The swine is already assigned to this location.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | NORMAL SYNCHRONIZATION
        |--------------------------------------------------------------------------
        |
        | No conflict was detected.
        | It is safe to create the movement.
        |
        */

        return DB::transaction(function () use ($swine, $destination, $validated) {

            /*
             * Use the server's actual current location
             * as the movement's from_location.
             */
            $fromLocationId =
                $swine->current_location_id;


            /*
             * Create movement history.
             */
            $movement = SwineMovement::create([

                'swine_id' =>
                    $swine->id,

                'from_location_id' =>
                    $fromLocationId,

                'to_location_id' =>
                    $destination->id,

                'movement_date' =>
                    $validated['movement_date'],

                'reason' =>
                    $validated['reason'] ?? null,

                'notes' =>
                    $validated['notes'] ?? null,

                'recorded_by' =>
                    auth()->id(),

            ]);


            /*
             * Update server location.
             */
            $swine->update([

                'current_location_id' =>
                    $destination->id,

            ]);


            return response()->json([

                'success' => true,

                'conflict' => false,

                'movement_id' =>
                    $movement->id,

                'swine_id' =>
                    $swine->id,

                'message' =>
                    'Offline movement synchronized successfully.',

            ]);
        });
    }

    /**
     * Display a single movement record.
     */
    public function show(
        SwineMovement $swineMovement
    ): View {

        $swineMovement->load([
            'swine.farm',
            'fromLocation',
            'toLocation',
            'recordedBy',
        ]);


        return view('swine-movements.show', [
            'movement' => $swineMovement,
        ]);
    }
}