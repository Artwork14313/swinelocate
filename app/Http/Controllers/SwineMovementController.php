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

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------------------
            | From Date
            |--------------------------------------------------------------------------
            */
            ->when($request->filled('from_date'), function ($query) use ($request) {

                $query->whereDate(
                    'movement_date',
                    '>=',
                    $request->from_date
                );

            })

            /*
            |--------------------------------------------------------------------------
            | To Date
            |--------------------------------------------------------------------------
            */
            ->when($request->filled('to_date'), function ($query) use ($request) {

                $query->whereDate(
                    'movement_date',
                    '<=',
                    $request->to_date
                );

            })

            /*
            |--------------------------------------------------------------------------
            | Ordering
            |--------------------------------------------------------------------------
            */
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
     */
    public function create(Swine $swine): View
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Swine Status
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
        | Load Current Location
        |--------------------------------------------------------------------------
        */

        $swine->load('currentLocation');


        /*
        |--------------------------------------------------------------------------
        | Get Active Farm Locations
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
     * Store a normal ONLINE swine movement.
     */
    public function store(
        Request $request,
        Swine $swine
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Validate Swine Status
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
        | Validate Request
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
        | Validate Destination
        |--------------------------------------------------------------------------
        */

        $destination = FarmLocation::query()
            ->where(
                'id',
                $validated['to_location_id']
            )
            ->where(
                'farm_id',
                $swine->farm_id
            )
            ->where(
                'status',
                'active'
            )
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
        | Prevent Same Location
        |--------------------------------------------------------------------------
        */

        if (
            $swine->current_location_id !== null &&
            (int) $swine->current_location_id ===
            (int) $destination->id
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
        | Create Movement
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($swine, $destination, $validated) {

            $fromLocationId =
                $swine->current_location_id;


            SwineMovement::create([

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

                /*
                |--------------------------------------------------------------------------
                | Normal movement
                |--------------------------------------------------------------------------
                */

                'status' =>
                    'completed',

                /*
                |--------------------------------------------------------------------------
                | NULL means no conflict occurred.
                |--------------------------------------------------------------------------
                */

                'conflict_resolution' =>
                    null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Update Current Swine Location
            |--------------------------------------------------------------------------
            */

            $swine->update([

                'current_location_id' =>
                    $destination->id,

            ]);
        });


        return redirect()
            ->route('swine-movements.index')
            ->with(
                'success',
                'Swine movement recorded successfully.'
            );
    }


    /**
     * Synchronize an OFFLINE swine movement.
     *
     * Handles:
     *
     * 1. Normal offline synchronization
     * 2. Conflict detection
     * 3. Keep Offline Version
     */
    public function syncStore(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'swine_id' => [
                'required',
                'integer',
                'exists:swine,id',
            ],

            'local_id' => [
                'nullable',
                'string',
                'max:255',
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

            'original_location_id' => [
                'nullable',
                'integer',
            ],

            'force' => [
                'nullable',
                'boolean',
            ],

            'server_movement_id' => [
                'nullable',
                'integer',
                'exists:swine_movements,id',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Get Swine
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
            ->where(
                'id',
                $validated['to_location_id']
            )
            ->where(
                'farm_id',
                $swine->farm_id
            )
            ->where(
                'status',
                'active'
            )
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
        | KEEP OFFLINE VERSION
        |--------------------------------------------------------------------------
        |
        | User selected:
        |
        | "Keep Offline Version"
        |
        | Example:
        |
        | Server movement:
        |     73: PEN-003 -> PEN-002
        |
        | Offline movement:
        |     PEN-002 -> PEN-001
        |
        | Result:
        |
        |     73 = superseded / offline
        |     74 = completed / offline
        |
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('force')) {

            return DB::transaction(function () use ($swine, $destination, $validated) {

                /*
                |--------------------------------------------------------------------------
                | Lock Swine Record
                |--------------------------------------------------------------------------
                |
                | Prevent another request from changing the swine location while
                | this conflict is being resolved.
                |
                |--------------------------------------------------------------------------
                */

                $swine = Swine::query()
                    ->where('id', $swine->id)
                    ->lockForUpdate()
                    ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Find Exact Server Movement
                |--------------------------------------------------------------------------
                */

                $serverMovement = null;

                if (
                    !empty(
                    $validated['server_movement_id']
                )
                ) {

                    $serverMovement = SwineMovement::query()
                        ->where(
                            'id',
                            $validated['server_movement_id']
                        )
                        ->where(
                            'swine_id',
                            $swine->id
                        )
                        ->lockForUpdate()
                        ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | Fallback
                |--------------------------------------------------------------------------
                */

                if (!$serverMovement) {

                    $serverMovement = SwineMovement::query()
                        ->where(
                            'swine_id',
                            $swine->id
                        )
                        ->where(
                            'to_location_id',
                            $swine->current_location_id
                        )
                        ->where(
                            'status',
                            '!=',
                            'superseded'
                        )
                        ->latest('movement_date')
                        ->latest('id')
                        ->lockForUpdate()
                        ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | Get Server Movement ID
                |--------------------------------------------------------------------------
                */

                $serverMovementId =
                    $serverMovement?->id;


                /*
                |--------------------------------------------------------------------------
                | Check Whether Offline Movement Was Already Applied
                |--------------------------------------------------------------------------
                */

                $existingOfflineMovement = SwineMovement::query()
                    ->where(
                        'swine_id',
                        $swine->id
                    )
                    ->where(
                        'to_location_id',
                        $destination->id
                    )
                    ->where(
                        'status',
                        'completed'
                    )
                    ->where(
                        'conflict_resolution',
                        'offline'
                    )
                    ->where(
                        'movement_date',
                        $validated['movement_date']
                    )
                    ->latest('id')
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Already Applied
                |--------------------------------------------------------------------------
                */

                if ($existingOfflineMovement) {

                    /*
                    |------------------------------------------------------------------
                    | Make sure the swine is at the accepted offline destination.
                    |------------------------------------------------------------------
                    */

                    $swine->update([

                        'current_location_id' =>
                            $destination->id,

                    ]);


                    return response()->json([

                        'success' =>
                            true,

                        'conflict' =>
                            false,

                        'resolved' =>
                            true,

                        'resolution' =>
                            'offline',

                        'already_applied' =>
                            true,

                        'movement_id' =>
                            $existingOfflineMovement->id,

                        'superseded_movement_id' =>
                            $serverMovementId,

                        'message' =>
                            'Offline movement was already applied.',

                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Get Current Authoritative Server Location
                |--------------------------------------------------------------------------
                */

                $serverFromLocationId =
                    $swine->current_location_id;


                /*
                |--------------------------------------------------------------------------
                | Mark Server Movement as Superseded
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | Use the query builder directly here so the database receives
                | the exact status and conflict resolution values.
                |
                |--------------------------------------------------------------------------
                */

                if ($serverMovementId) {

                    DB::table('swine_movements')
                        ->where(
                            'id',
                            $serverMovementId
                        )
                        ->where(
                            'swine_id',
                            $swine->id
                        )
                        ->update([

                            'status' =>
                                'superseded',

                            'conflict_resolution' =>
                                'offline',

                            'updated_at' =>
                                now(),

                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Create Accepted Offline Movement
                |--------------------------------------------------------------------------
                |
                | The accepted offline movement begins from the current SERVER
                | location, not the stale offline location.
                |
                |--------------------------------------------------------------------------
                */

                $movementId = DB::table('swine_movements')
                    ->insertGetId([

                        'swine_id' =>
                            $swine->id,

                        'from_location_id' =>
                            $serverFromLocationId,

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

                        'status' =>
                            'completed',

                        'conflict_resolution' =>
                            'offline',

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Update Current Swine Location
                |--------------------------------------------------------------------------
                */

                DB::table('swine')
                    ->where(
                        'id',
                        $swine->id
                    )
                    ->update([

                        'current_location_id' =>
                            $destination->id,

                        'updated_at' =>
                            now(),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Verify Database Values
                |--------------------------------------------------------------------------
                |
                | This is intentionally checked before returning success.
                |
                |--------------------------------------------------------------------------
                */

                $acceptedMovement =
                    DB::table('swine_movements')
                        ->where(
                            'id',
                            $movementId
                        )
                        ->first();


                $supersededMovement = null;

                if ($serverMovementId) {

                    $supersededMovement =
                        DB::table('swine_movements')
                            ->where(
                                'id',
                                $serverMovementId
                            )
                            ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | Safety Verification
                |--------------------------------------------------------------------------
                */

                if (
                    !$acceptedMovement ||
                    $acceptedMovement->status !== 'completed' ||
                    $acceptedMovement->conflict_resolution !== 'offline'
                ) {

                    throw new \RuntimeException(
                        'The accepted offline movement could not be saved correctly.'
                    );
                }


                if ($serverMovementId) {

                    if (
                        !$supersededMovement ||
                        $supersededMovement->status !== 'superseded' ||
                        $supersededMovement->conflict_resolution !== 'offline'
                    ) {

                        throw new \RuntimeException(
                            'The online movement could not be marked as superseded.'
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Return Success
                |--------------------------------------------------------------------------
                */

                return response()->json([

                    'success' =>
                        true,

                    'conflict' =>
                        false,

                    'resolved' =>
                        true,

                    'resolution' =>
                        'offline',

                    'movement_id' =>
                        $movementId,

                    'superseded_movement_id' =>
                        $serverMovementId,

                    'message' =>
                        'Offline movement kept. The online movement was marked as superseded.',

                ]);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | CONFLICT DETECTION
        |--------------------------------------------------------------------------
        */

        $originalLocationId =
            $validated['original_location_id'] ?? null;


        if ($originalLocationId !== null) {

            $serverLocationId =
                $swine->current_location_id;


            /*
            |--------------------------------------------------------------------------
            | Detect Location Conflict
            |--------------------------------------------------------------------------
            */

            if (
                (int) $serverLocationId !==
                (int) $originalLocationId
            ) {

                /*
                |--------------------------------------------------------------------------
                | Find Movement That Changed Server Location
                |--------------------------------------------------------------------------
                */

                $serverMovement = null;

                if ($serverLocationId !== null) {

                    $serverMovement = SwineMovement::query()
                        ->where(
                            'swine_id',
                            $swine->id
                        )
                        ->where(
                            'to_location_id',
                            $serverLocationId
                        )
                        ->where(
                            'status',
                            '!=',
                            'superseded'
                        )
                        ->latest('movement_date')
                        ->latest('id')
                        ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | Return Conflict
                |--------------------------------------------------------------------------
                */

                return response()->json([

                    'success' =>
                        false,

                    'conflict' =>
                        true,

                    'message' =>
                        'This swine was moved by another user while this device was offline.',

                    'swine_id' =>
                        $swine->id,

                    'server_movement_id' =>
                        $serverMovement?->id,

                    'offline_data' => [

                        'local_id' =>
                            $validated['local_id'] ?? null,

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

                    'server_data' => [

                        'movement_id' =>
                            $serverMovement?->id,

                        'swine_id' =>
                            $swine->id,

                        'current_location_id' =>
                            $swine->current_location_id,

                        'from_location_id' =>
                            $serverMovement?->from_location_id,

                        'to_location_id' =>
                            $serverMovement?->to_location_id,

                        'movement_date' =>
                            $serverMovement?->movement_date?->toISOString(),

                        'reason' =>
                            $serverMovement?->reason,

                        'notes' =>
                            $serverMovement?->notes,

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
        | Prevent Duplicate Destination
        |--------------------------------------------------------------------------
        */

        if (
            $swine->current_location_id !== null &&
            (int) $swine->current_location_id ===
            (int) $destination->id
        ) {

            return response()->json([

                'success' =>
                    false,

                'conflict' =>
                    false,

                'already_applied' =>
                    true,

                'message' =>
                    'The swine is already assigned to this location.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | NORMAL OFFLINE SYNCHRONIZATION
        |--------------------------------------------------------------------------
        */

        return DB::transaction(function () use ($swine, $destination, $validated) {

            $fromLocationId =
                $swine->current_location_id;


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

                'status' =>
                    'completed',

                'conflict_resolution' =>
                    null,

            ]);


            $swine->update([

                'current_location_id' =>
                    $destination->id,

            ]);


            return response()->json([

                'success' =>
                    true,

                'conflict' =>
                    false,

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
     * Resolve an offline movement conflict.
     *
     * This method can be used by the conflict-resolution interface.
     *
     * NOTE:
     * The primary movement synchronization flow uses syncStore()
     * with force=true. This method is retained for compatibility.
     */
    public function resolveConflict(
        Request $request,
        SwineMovement $swineMovement
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validate Resolution
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'resolution' => [
                'required',
                'in:keep_online,keep_offline',
            ],

            'server_movement_id' => [
                'required',
                'integer',
                'exists:swine_movements,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | These are only required when KEEP OFFLINE is selected.
            |--------------------------------------------------------------------------
            */

            'to_location_id' => [
                'required_if:resolution,keep_offline',
                'nullable',
                'exists:farm_locations,id',
            ],

            'movement_date' => [
                'required_if:resolution,keep_offline',
                'nullable',
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

        $swine = $swineMovement->swine;

        /*
        |--------------------------------------------------------------------------
        | Find Exact Server Movement
        |--------------------------------------------------------------------------
        */

        $serverMovement = SwineMovement::query()
            ->where(
                'id',
                $validated['server_movement_id']
            )
            ->where(
                'swine_id',
                $swine->id
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Server Movement Must Exist
        |--------------------------------------------------------------------------
        */

        if (!$serverMovement) {

            return response()->json([

                'success' => false,

                'message' =>
                    'The server movement could not be found for this swine.',

            ], 404);
        }


        /*
|--------------------------------------------------------------------------
| KEEP ONLINE VERSION
|--------------------------------------------------------------------------
*/

        if ($validated['resolution'] === 'keep_online') {

            $serverMovement->update([
                'status' => 'completed',
                'conflict_resolution' => 'online',
            ]);

            $swine->update([
                'current_location_id' => $serverMovement->to_location_id,
            ]);

            return response()->json([
                'success' => true,
                'conflict' => false,
                'resolved' => true,
                'resolution' => 'online',
                'movement_id' => $serverMovement->id,
                'current_location_id' => $serverMovement->to_location_id,
                'message' =>
                    'Online movement was kept. The offline version was not applied.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Offline Destination
        |--------------------------------------------------------------------------
        */

        $destination = FarmLocation::query()
            ->where(
                'id',
                $validated['to_location_id']
            )
            ->where(
                'farm_id',
                $swine->farm_id
            )
            ->where(
                'status',
                'active'
            )
            ->first();


        if (!$destination) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'The selected destination does not belong to the swine farm.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | KEEP OFFLINE VERSION
        |--------------------------------------------------------------------------
        */

        return DB::transaction(function () use ($swine, $serverMovement, $destination, $validated) {

            /*
            |--------------------------------------------------------------------------
            | Mark Online Movement as Superseded
            |--------------------------------------------------------------------------
            */

            $serverMovement->update([

                'status' =>
                    'superseded',

                'conflict_resolution' =>
                    'offline',

            ]);


            /*
            |--------------------------------------------------------------------------
            | Check for Existing Accepted Offline Movement
            |--------------------------------------------------------------------------
            */

            $existingMovement = SwineMovement::query()
                ->where(
                    'swine_id',
                    $swine->id
                )
                ->where(
                    'to_location_id',
                    $destination->id
                )
                ->where(
                    'status',
                    'completed'
                )
                ->where(
                    'conflict_resolution',
                    'offline'
                )
                ->where(
                    'movement_date',
                    $validated['movement_date']
                )
                ->latest('id')
                ->first();


            if ($existingMovement) {

                $swine->update([

                    'current_location_id' =>
                        $destination->id,

                ]);


                return response()->json([

                    'success' =>
                        true,

                    'conflict' =>
                        false,

                    'resolved' =>
                        true,

                    'resolution' =>
                        'offline',

                    'already_applied' =>
                        true,

                    'movement_id' =>
                        $existingMovement->id,

                    'superseded_movement_id' =>
                        $serverMovement->id,

                    'message' =>
                        'Offline movement was already applied.',

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Create Accepted Offline Movement
            |--------------------------------------------------------------------------
            */

            $movement =
                $swine->movements()->create([

                    /*
                    |--------------------------------------------------------------------------
                    | Start from the current authoritative server location
                    |--------------------------------------------------------------------------
                    */

                    'from_location_id' =>
                        $swine->current_location_id,

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

                    'status' =>
                        'completed',

                    'conflict_resolution' =>
                        'offline',

                ]);


            /*
            |--------------------------------------------------------------------------
            | Update Current Location
            |--------------------------------------------------------------------------
            */

            $swine->update([

                'current_location_id' =>
                    $destination->id,

            ]);


            /*
            |--------------------------------------------------------------------------
            | Return Result
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' =>
                    true,

                'conflict' =>
                    false,

                'resolved' =>
                    true,

                'resolution' =>
                    'offline',

                'movement_id' =>
                    $movement->id,

                'superseded_movement_id' =>
                    $serverMovement->id,

                'current_location_id' =>
                    $destination->id,

                'message' =>
                    'Offline movement was kept and the online movement was superseded.',

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

            'movement' =>
                $swineMovement,

        ]);
    }
}