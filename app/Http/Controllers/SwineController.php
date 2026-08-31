<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\FarmLocation;
use App\Models\Swine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;


class SwineController extends Controller
{
    /**
     * Display a listing of swine.
     */
    public function index(): View
    {
        $swine = Swine::with([
            'farm',
            'currentLocation',
        ])
            ->latest()
            ->paginate(10);

        return view('swine.index', compact('swine'));
    }

    /**
     * Show the form for registering a new swine.
     */
    public function create(): View
    {
        $farms = Farm::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $locations = FarmLocation::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('swine.create', compact(
            'farms',
            'locations'
        ));
    }

    /**
     * Store a newly registered swine.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farm_id' => [
                'required',
                'exists:farms,id',
            ],

            'current_location_id' => [
                'nullable',
                'exists:farm_locations,id',
            ],

            'tag_number' => [
                'required',
                'string',
                'max:255',
                'unique:swine,tag_number',
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sex' => [
                'required',
                'in:male,female',
            ],

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'acquisition_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'source' => [
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
        | Validate Farm Location
        |--------------------------------------------------------------------------
        |
        | Make sure the selected location actually belongs to the
        | selected farm.
        |
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where('id', $validated['current_location_id'])
                ->where('farm_id', $validated['farm_id'])
                ->exists();

            if (!$validLocation) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'current_location_id' =>
                            'The selected location does not belong to the selected farm.',
                    ]);
            }
        }

        $validated['status'] = 'active';
        $validated['qr_token'] = Str::uuid()->toString();

        $swine = Swine::create($validated);


        return redirect()
            ->route('swine.index')
            ->with('success', 'Swine registered successfully.');
    }

    /**
     * Display the specified swine.
     */
    public function show(Swine $swine): View
    {
        $swine->load([
            'farm',
            'currentLocation',
            'movements.fromLocation',
            'movements.toLocation',
            'movements.recordedBy',
        ]);

        $qrCode = QrCode::size(220)->generate(
            route('swine.scan', [
                'qr_token' => $swine->qr_token,
            ])
        );

        return view('swine.show', compact('swine', 'qrCode'));
    }


    /**
     * Show the form for editing the specified swine.
     */
    public function edit(Swine $swine): View
    {
        $farms = Farm::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $locations = FarmLocation::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('swine.edit', compact(
            'swine',
            'farms',
            'locations'
        ));
    }

    /**
     * Update the specified swine.
     */
    public function update(
        Request $request,
        Swine $swine
    ): RedirectResponse {

        $validated = $request->validate([
            'farm_id' => [
                'required',
                'exists:farms,id',
            ],

            'current_location_id' => [
                'nullable',
                'exists:farm_locations,id',
            ],

            'tag_number' => [
                'required',
                'string',
                'max:255',
                'unique:swine,tag_number,' . $swine->id,
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sex' => [
                'required',
                'in:male,female',
            ],

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'acquisition_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where('id', $validated['current_location_id'])
                ->where('farm_id', $validated['farm_id'])
                ->exists();

            if (!$validLocation) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'current_location_id' =>
                            'The selected location does not belong to the selected farm.',
                    ]);
            }
        }

        $swine->update($validated);

        return redirect()
            ->route('swine.index')
            ->with('success', 'Swine information updated successfully.');
    }

    /**
     * Remove the specified swine.
     */
    public function destroy(Swine $swine): RedirectResponse
    {
        $swine->delete();

        return redirect()
            ->route('swine.index')
            ->with('success', 'Swine record deleted successfully.');
    }

    public function scan(string $qr_token): View
    {
        $swine = Swine::query()
            ->where('qr_token', $qr_token)
            ->with([
                'farm',
                'currentLocation',
                'movements.fromLocation',
                'movements.toLocation',
            ])
            ->firstOrFail();

        return view('swine.scan', compact('swine'));
    }

    /**
     * Store an offline swine registration
     * synchronized from the browser.
     */
    public function syncStore(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => [
                'required',
                'exists:farms,id',
            ],

            'current_location_id' => [
                'nullable',
                'exists:farm_locations,id',
            ],

            'tag_number' => [
                'required',
                'string',
                'max:255',
                'unique:swine,tag_number',
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sex' => [
                'required',
                'in:male,female',
            ],

            'breed' => [
                'nullable',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'acquisition_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'source' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'qr_token' => [
                'required',
                'string',
                'max:255',
                'unique:swine,qr_token',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Farm Location
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['current_location_id'])) {

            $validLocation = FarmLocation::query()
                ->where(
                    'id',
                    $validated['current_location_id']
                )
                ->where(
                    'farm_id',
                    $validated['farm_id']
                )
                ->exists();

            if (!$validLocation) {

                return response()->json([
                    'message' =>
                        'The selected location does not belong to the selected farm.',
                ], 422);

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Set defaults
        |--------------------------------------------------------------------------
        */

        $validated['status'] = 'active';


        /*
        |--------------------------------------------------------------------------
        | Create swine
        |--------------------------------------------------------------------------
        */

        $swine = Swine::create(
            $validated
        );


        /*
        |--------------------------------------------------------------------------
        | Return successful synchronization response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' =>
                'Swine synchronized successfully.',

            'swine_id' =>
                $swine->id,

        ], 201);
    }

}