<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\Swine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthRecordController extends Controller
{
    /**
     * Display a listing of health records.
     */
    public function index(): View
    {
        $latestRecordIds = HealthRecord::query()
            ->selectRaw('MAX(id) as id')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('health_records as hr2')
                    ->whereColumn(
                        'hr2.swine_id',
                        'health_records.swine_id'
                    )
                    ->whereRaw(
                        'hr2.record_date = (
                        SELECT MAX(hr3.record_date)
                        FROM health_records as hr3
                        WHERE hr3.swine_id = health_records.swine_id
                    )'
                    );
            })
            ->groupBy('swine_id');

        $healthRecords = HealthRecord::query()
            ->whereIn('id', $latestRecordIds)
            ->with([
                'swine',
                'swine.farm',
                'swine.currentLocation',
                'recordedBy',
            ])
            ->latest('record_date')
            ->paginate(10);

        return view('health-records.index', compact(
            'healthRecords'
        ));
    }

    /**
     * Show the form for creating a new health record.
     */
    public function create(): View
    {
        $swine = Swine::query()
            ->where('status', 'active')
            ->orderBy('tag_number')
            ->get();

        return view(
            'health-records.create',
            compact('swine')
        );
    }

    /**
     * Store a newly created health record.
     */
    public function store(Request $request): RedirectResponse
    {
        $vaccineNameRule = $request->record_type === 'Vaccination'
            ? ['required', 'string', 'max:255']
            : ['nullable', 'string', 'max:255'];

        $validated = $request->validate([
            'swine_id' => [
                'required',
                'exists:swine,id',
            ],

            'record_date' => [
                'required',
                'date',
            ],

            'record_type' => [
                'required',
                'string',
                'max:255',
            ],

            'vaccine_name' => $vaccineNameRule,

            'dose' => [
                'nullable',
                'string',
                'max:255',
            ],

            'batch_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'next_due_date' => [
                'nullable',
                'date',
                'after_or_equal:record_date',
            ],

            'symptoms' => [
                'nullable',
                'string',
            ],

            'diagnosis' => [
                'nullable',
                'string',
            ],

            'treatment' => [
                'nullable',
                'string',
            ],

            'observations' => [
                'nullable',
                'string',
            ],

            'veterinary_assessment' => [
                'nullable',
                'string',
            ],

            'health_status' => [
                'required',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['recorded_by'] = auth()->id();

        HealthRecord::create($validated);

        return redirect()
            ->route('health-records.index')
            ->with(
                'success',
                'Health record added successfully.'
            );
    }

    /**
     * Display the specified health record.
     */
    public function show(HealthRecord $healthRecord): View
    {
        $healthRecord->load([
            'swine',
            'swine.farm',
            'swine.currentLocation',
            'recordedBy',
        ]);

        $swine = $healthRecord->swine;

        return view(
            'health-records.show',
            compact(
                'healthRecord',
                'swine'
            )
        );
    }

    /**
     * Show the form for editing the specified health record.
     */
    public function edit(
        HealthRecord $healthRecord
    ): View {

        $swine = Swine::query()
            ->where('status', 'active')
            ->orderBy('tag_number')
            ->get();

        return view(
            'health-records.edit',
            compact(
                'healthRecord',
                'swine'
            )
        );
    }

    /**
     * Update the specified health record.
     */
    public function update(
        Request $request,
        HealthRecord $healthRecord
    ): RedirectResponse {

        $vaccineNameRule = $request->record_type === 'Vaccination'
            ? ['required', 'string', 'max:255']
            : ['nullable', 'string', 'max:255'];

        $validated = $request->validate([
            'swine_id' => [
                'required',
                'exists:swine,id',
            ],

            'record_date' => [
                'required',
                'date',
            ],

            'record_type' => [
                'required',
                'string',
                'max:255',
            ],

            'vaccine_name' => $vaccineNameRule,

            'dose' => [
                'nullable',
                'string',
                'max:255',
            ],

            'batch_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'next_due_date' => [
                'nullable',
                'date',
                'after_or_equal:record_date',
            ],

            'symptoms' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'veterinary_assessment' => ['nullable', 'string'],

            'health_status' => [
                'required',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        if ($validated['record_type'] !== 'Vaccination') {
            $validated['vaccine_name'] = null;
            $validated['dose'] = null;
            $validated['batch_number'] = null;
            $validated['next_due_date'] = null;
        }

        $healthRecord->update($validated);

        return redirect()
            ->route('health-records.index')
            ->with(
                'success',
                'Health record updated successfully.'
            );
    }

    /**
     * Remove the specified health record.
     */
    public function destroy(
        HealthRecord $healthRecord
    ): RedirectResponse {

        $healthRecord->delete();

        return redirect()
            ->route('health-records.index')
            ->with(
                'success',
                'Health record deleted successfully.'
            );
    }

    public function history(Swine $swine): View
    {
        $swine->load([
            'farm',
            'currentLocation',
        ]);

        $healthRecords = HealthRecord::query()
            ->where('swine_id', $swine->id)
            ->with('recordedBy')
            ->latest('record_date')
            ->latest('id')
            ->get();

        return view(
            'health-records.history',
            compact(
                'swine',
                'healthRecords'
            )
        );
    }

    public function historyIndex(): View
    {
        $swine = Swine::query()
            ->with('farm')
            ->orderBy('tag_number')
            ->get();

        return view('health-records.history-index', compact('swine'));
    }
    
}