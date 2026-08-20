<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\FarmLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmLocationController extends Controller
{
    /**
     * Display all locations.
     */
    public function index(Farm $farm): View
    {
        $locations = $farm->locations()
            ->latest()
            ->paginate(10);

        return view('farm-locations.index', compact(
            'farm',
            'locations'
        ));
    }

    /**
     * Show the create location form.
     */
    public function create(Farm $farm): View
    {
        return view('farm-locations.create', compact('farm'));
    }

    /**
     * Store a new location.
     */
    public function store(
        Request $request,
        Farm $farm
    ): RedirectResponse {
        $validated = $request->validate([
            'location_code' => [
                'required',
                'string',
                'max:50',
                'unique:farm_locations,location_code,NULL,id,farm_id,' . $farm->id,
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'capacity' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $validated['status'] = 'active';

        $farm->locations()->create($validated);

        return redirect()
            ->route('farms.locations.index', $farm)
            ->with(
                'success',
                'Farm location successfully registered.'
            );
    }

    /**
     * Display a location.
     */
    public function show(
        Farm $farm,
        FarmLocation $location
    ): View {
        $this->ensureLocationBelongsToFarm($farm, $location);

        $location->load('farm');

        return view('farm-locations.show', compact(
            'farm',
            'location'
        ));
    }

    /**
     * Show the edit form.
     */
    public function edit(
        Farm $farm,
        FarmLocation $location
    ): View {
        $this->ensureLocationBelongsToFarm($farm, $location);

        return view('farm-locations.edit', compact(
            'farm',
            'location'
        ));
    }

    /**
     * Update a location.
     */
    public function update(
        Request $request,
        Farm $farm,
        FarmLocation $location
    ): RedirectResponse {
        $this->ensureLocationBelongsToFarm($farm, $location);

        $validated = $request->validate([
            'location_code' => [
                'required',
                'string',
                'max:50',
                'unique:farm_locations,location_code,'
                    . $location->id
                    . ',id,farm_id,'
                    . $farm->id,
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'capacity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        $location->update($validated);

        return redirect()
            ->route(
                'farms.locations.show',
                [$farm, $location]
            )
            ->with(
                'success',
                'Farm location successfully updated.'
            );
    }

    /**
     * Deactivate a location.
     */
    public function destroy(
        Farm $farm,
        FarmLocation $location
    ): RedirectResponse {
        $this->ensureLocationBelongsToFarm($farm, $location);

        $location->update([
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('farms.locations.index', $farm)
            ->with(
                'success',
                'Farm location successfully deactivated.'
            );
    }

    /**
     * Make sure the requested location belongs to the farm.
     */
    private function ensureLocationBelongsToFarm(
        Farm $farm,
        FarmLocation $location
    ): void {
        abort_unless(
            $location->farm_id === $farm->id,
            404
        );
    }
}