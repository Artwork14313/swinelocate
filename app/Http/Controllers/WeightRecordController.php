<?php

namespace App\Http\Controllers;

use App\Models\Swine;
use App\Models\WeightRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeightRecordController extends Controller
{
    /**
     * Display weight records.
     */
    public function index(Request $request): View
    {
        $weightRecords = WeightRecord::query()
            ->with(['swine', 'recordedBy'])
            ->latest('record_date')
            ->latest('id')
            ->paginate(15);

        return view('weight-records.index', compact(
            'weightRecords'
        ));
    }


    /**
     * Show form for creating a weight record.
     */
    public function create(Request $request): View
    {
        $swines = Swine::query()
            ->orderBy('tag_number')
            ->get();

        $selectedSwine = null;

        if ($request->filled('swine_id')) {
            $selectedSwine = Swine::find($request->swine_id);
        }

        return view('weight-records.create', compact(
            'swines',
            'selectedSwine'
        ));
    }


    /**
     * Store a new weight record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'swine_id' => [
                'required',
                'exists:swine,id',
            ],

            'record_date' => [
                'required',
                'date',
            ],

            'weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $validated['recorded_by'] = auth()->id();

        WeightRecord::create($validated);

        return redirect()
            ->route('weight-records.index')
            ->with('success', 'Weight record added successfully.');
    }


    /**
     * Display a weight record.
     */
    public function show(WeightRecord $weightRecord): View
    {
        $weightRecord->load([
            'swine',
            'recordedBy',
        ]);

        return view('weight-records.show', compact(
            'weightRecord'
        ));
    }


    /**
     * Show form for editing a weight record.
     */
    public function edit(WeightRecord $weightRecord): View
    {
        $swines = Swine::query()
            ->orderBy('tag_number')
            ->get();

        return view('weight-records.edit', compact(
            'weightRecord',
            'swines'
        ));
    }


    /**
     * Update a weight record.
     */
    public function update(
        Request $request,
        WeightRecord $weightRecord
    ): RedirectResponse {

        $validated = $request->validate([
            'record_date' => [
                'required',
                'date',
            ],

            'weight' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $weightRecord->update($validated);

        return redirect()
            ->route('weight-records.show', $weightRecord)
            ->with('success', 'Weight record updated successfully.');
    }

    /**
     * Delete a weight record.
     */
    public function destroy(
        WeightRecord $weightRecord
    ): RedirectResponse {

        $weightRecord->delete();

        return redirect()
            ->route('weight-records.index')
            ->with('success', 'Weight record deleted successfully.');
    }
}