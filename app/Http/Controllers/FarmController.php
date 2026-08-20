<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmController extends Controller
{
    public function index(): View
    {
        $farms = Farm::latest()->paginate(10);

        return view('farms.index', compact('farms'));
    }

    public function create(): View
    {
        return view('farms.create');
    }

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

        $validated['status'] = 'active';

        Farm::create($validated);

        return redirect()
            ->route('farms.index')
            ->with('success', 'Farm successfully registered.');
    }

    public function show(Farm $farm): View
    {
        $farm->load([
            'locations',
            'swine',
            'users',
        ]);

        return view('farms.show', compact('farm'));
    }

    public function edit(Farm $farm): View
    {
        return view('farms.edit', compact('farm'));
    }

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
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        $farm->update($validated);

        return redirect()
            ->route('farms.index')
            ->with('success', 'Farm successfully updated.');
    }

    public function destroy(Farm $farm): RedirectResponse
    {
        $farm->update([
            'status' => 'inactive',
        ]);

        $farm->delete();

        return redirect()
            ->route('farms.index')
            ->with('success', 'Farm successfully deactivated.');
    }
}