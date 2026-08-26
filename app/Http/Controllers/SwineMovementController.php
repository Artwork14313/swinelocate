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
        /*
        |--------------------------------------------------------------------------
        | Movement Records
        |--------------------------------------------------------------------------
        */

        $movements = SwineMovement::query()
            ->with([
                'swine',
                'fromLocation',
                'toLocation',
                'recordedBy',
            ])

            /*
             * Search by swine tag number or name.
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
             * Filter by starting date.
             */
            ->when($request->filled('from_date'), function ($query) use ($request) {

                $query->whereDate(
                    'movement_date',
                    '>=',
                    $request->from_date
                );

            })

            /*
             * Filter by ending date.
             */
            ->when($request->filled('to_date'), function ($query) use ($request) {

                $query->whereDate(
                    'movement_date',
                    '<=',
                    $request->to_date
                );

            })

            /*
             * Newest movement first.
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

        // All recorded movements
        $totalMovements = SwineMovement::count();


        // Movements recorded today
        $todayMovements = SwineMovement::query()
            ->whereDate('movement_date', today())
            ->count();


        // Movements recorded this month
        $thisMonthMovements = SwineMovement::query()
            ->whereMonth('movement_date', now()->month)
            ->whereYear('movement_date', now()->year)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('swine-movements.index', compact(
            'movements',
            'totalMovements',
            'todayMovements',
            'thisMonthMovements'
        ));
    }


    /**
     * Display the form for recording a swine movement.
     */
    public function create(?Swine $swine = null): View
    {
        $swines = Swine::query()
            ->where('status', 'active')
            ->with([
                'farm',
                'currentLocation',
            ])
            ->orderBy('tag_number')
            ->get();

        $locations = collect();

        if ($swine) {
            $locations = FarmLocation::where('farm_id', $swine->farm_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        return view('swine-movements.create', [
            'swine' => $swine,
            'swines' => $swines,
            'locations' => $locations,
        ]);
    }


    /**
     * Store a new swine movement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'swine_id' => [
                'required',
                'integer',
                'exists:swine,id',
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
        ]);

        $swine = Swine::findOrFail($validated['swine_id']);

        $destination = FarmLocation::where('id', $validated['to_location_id'])
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

        DB::transaction(function () use ($swine, $destination, $validated) {

            $fromLocationId = $swine->current_location_id;

            SwineMovement::create([
                'swine_id' => $swine->id,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $destination->id,
                'movement_date' => $validated['movement_date'],
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => auth()->id(),
            ]);

            $swine->update([
                'current_location_id' => $destination->id,
            ]);
        });

        return redirect()
            ->route('swine-movements.index')
            ->with(
                'success',
                'Swine movement recorded successfully.'
            );
    }

    public function locations(Swine $swine)
    {
        $locations = FarmLocation::query()
            ->where('farm_id', $swine->farm_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'location_code',
            ]);

        $currentLocation = $swine->currentLocation;

        return response()->json([
            'current_location' => $currentLocation
                ? [
                    'id' => $currentLocation->id,
                    'name' => $currentLocation->name,
                    'location_code' => $currentLocation->location_code,
                ]
                : null,

            'locations' => $locations,
        ]);
    }

    public function show(SwineMovement $swineMovement): View
    {
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