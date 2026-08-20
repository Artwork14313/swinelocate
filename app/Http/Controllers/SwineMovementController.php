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
     * Display the form for moving a swine.
     */
    public function create(Swine $swine): View
    {
        $locations = FarmLocation::where('farm_id', $swine->farm_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('swine.movements.create', [
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
         * Make sure the destination belongs
         * to the same farm as the swine.
         */
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

        /*
         * Prevent moving a swine to its
         * current location.
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
         * Everything inside this transaction
         * must succeed together.
         */
        DB::transaction(function () use (
            $swine,
            $destination,
            $validated
        ) {

            // Save the old location.
            $fromLocationId = $swine->current_location_id;

            // Create movement history.
            SwineMovement::create([
                'swine_id' => $swine->id,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $destination->id,
                'movement_date' => $validated['movement_date'],
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => auth()->id(),
            ]);

            // Update current location.
            $swine->update([
                'current_location_id' => $destination->id,
            ]);
        });

        return redirect()
            ->route('swine.show', $swine)
            ->with(
                'success',
                'Swine movement recorded successfully.'
            );
    }
}