<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmController extends Controller
{
    /**
     * Display all farms.
     */
    public function index(Request $request): View
    {
        $farms = Farm::query()
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('farms.index', compact('farms'));
    }

    /**
     * Show the farm registration form.
     */
    public function create(): View
    {
        return view('farms.create');
    }

    /**
     * Store a new farm.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farm_code' => [
                'required',
                'string',
                'max:50',
                'unique:farms,farm_code',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'municipality' => [
                'nullable',
                'string',
                'max:100',
            ],

            'province' => [
                'nullable',
                'string',
                'max:100',
            ],

            'region' => [
                'nullable',
                'string',
                'max:100',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
        ]);

        // Every newly registered farm starts as active.
        $validated['status'] = 'active';

        Farm::create($validated);

        return redirect()
            ->route('farms.index')
            ->with('success', 'Farm successfully registered.');
    }

    /**
     * Display a farm.
     */
    public function show(Farm $farm): View
    {
        $farm->load([
            'locations',
            'swine',
            'users',
        ]);

        return view('farms.show', compact('farm'));
    }

    /**
     * Show the farm edit form.
     */
    public function edit(Farm $farm): View
    {
        return view('farms.edit', compact('farm'));
    }

    /**
     * Update farm information.
     *
     * Farm status is intentionally not updated here.
     * Status is controlled through activate() and destroy().
     */
    public function update(
        Request $request,
        Farm $farm
    ): RedirectResponse {
        $validated = $request->validate([
            'farm_code' => [
                'required',
                'string',
                'max:50',
                'unique:farms,farm_code,' . $farm->id,
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'municipality' => [
                'nullable',
                'string',
                'max:100',
            ],

            'province' => [
                'nullable',
                'string',
                'max:100',
            ],

            'region' => [
                'nullable',
                'string',
                'max:100',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
        ]);

        $farm->update($validated);

        return redirect()
            ->route('farms.show', $farm)
            ->with('success', 'Farm successfully updated.');
    }

    /**
     * Deactivate a farm.
     *
     * The farm is not deleted so historical and
     * traceability records remain available.
     */
    public function destroy(Farm $farm): RedirectResponse
    {
        if ($farm->status === 'inactive') {
            return redirect()
                ->route('farms.index')
                ->with('error', 'Farm is already inactive.');
        }

        $farm->update([
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('farms.index')
            ->with('success', 'Farm deactivated successfully.');
    }

    /**
     * Activate a farm.
     */
    public function activate(Farm $farm): RedirectResponse
    {
        if ($farm->status === 'active') {
            return redirect()
                ->route('farms.index')
                ->with('error', 'Farm is already active.');
        }

        $farm->update([
            'status' => 'active',
        ]);

        return redirect()
            ->route('farms.index')
            ->with('success', 'Farm activated successfully.');
    }
}
