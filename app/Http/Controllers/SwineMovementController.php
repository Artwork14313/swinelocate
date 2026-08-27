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

        DB::transaction(function () use (
            $swine,
            $destination,
            $validated
        ) {

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