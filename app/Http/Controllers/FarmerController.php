<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Farmer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FarmerController extends Controller
{
    public function index(Request $request): View
    {
        $farmers = Farmer::query()
            ->with('farm')
            ->withCount('swine')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('farmers.index', compact('farmers'));
    }

    public function create(Request $request): View
    {
        $farms = Farm::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedFarm = $farms->firstWhere(
            'id',
            $request->integer('farm_id')
        );

        return view('farmers.create', [
            'farmer' => new Farmer([
                'farm_id' => $selectedFarm?->id,
            ]),
            'farms' => $farms,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        Farmer::create($validated);

        return redirect()
            ->route('farmers.index')
            ->with('success', 'Farmer registered successfully.');
    }

    public function show(Farmer $farmer): View
    {
        $farmer->load([
            'farm',
            'swine' => fn ($query) => $query
                ->with('currentLocation')
                ->orderBy('tag_number'),
        ]);

        $qrCode = QrCode::size(220)->generate(
            route('farmers.public-profile', [
                'qr_token' => $farmer->qr_token,
            ])
        );

        return view('farmers.show', compact('farmer', 'qrCode'));
    }

    public function publicProfile(string $qr_token): View
    {
        $farmer = Farmer::query()
            ->where('qr_token', $qr_token)
            ->with([
                'farm:id,name,address,municipality,province,region',
                'swine' => fn ($query) => $query
                    ->where('status', 'active')
                    ->where('is_for_sale', true)
                    ->select([
                        'id',
                        'farmer_id',
                        'sex',
                        'breed',
                        'birth_date',
                        'asking_price',
                    ])
                    ->with([
                        'latestWeightRecord' => fn ($query) => $query->select([
                            'weight_records.id',
                            'weight_records.swine_id',
                            'weight_records.record_date',
                            'weight_records.weight',
                        ]),
                    ])
                    ->orderBy('breed')
                    ->orderBy('sex'),
            ])
            ->firstOrFail();

        return view('farmers.public-profile', compact('farmer'));
    }

    public function nearby(): View
    {
        return view('farmers.nearby');
    }

    public function searchNearby(Request $request): View
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'municipality' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $latitude = isset($validated['latitude']) ? (float) $validated['latitude'] : null;
        $longitude = isset($validated['longitude']) ? (float) $validated['longitude'] : null;
        $municipality = trim($validated['municipality'] ?? '');
        $province = trim($validated['province'] ?? '');

        if (
            $latitude === null &&
            $longitude === null &&
            $municipality === '' &&
            $province === ''
        ) {
            throw ValidationException::withMessages([
                'municipality' => 'Share your location or enter a municipality or province to find nearby listings.',
            ]);
        }

        $farmers = Farmer::query()
            ->whereHas('farm', function ($query) use ($municipality, $province) {
                $query->where('status', 'active')
                    ->when($municipality !== '', fn ($query) => $query
                        ->where('municipality', 'like', "%{$municipality}%"))
                    ->when($province !== '', fn ($query) => $query
                        ->where('province', 'like', "%{$province}%"));
            })
            ->whereHas('swine', fn ($query) => $query
                ->where('status', 'active')
                ->where('is_for_sale', true))
            ->with([
                'farm:id,name,municipality,province,latitude,longitude',
                'swine' => fn ($query) => $query
                    ->where('status', 'active')
                    ->where('is_for_sale', true)
                    ->select([
                        'id',
                        'farmer_id',
                        'sex',
                        'breed',
                        'asking_price',
                    ])
                    ->orderBy('breed')
                    ->orderBy('sex'),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Farmer $farmer) use ($latitude, $longitude) {
                $distance = null;

                if (
                    $latitude !== null &&
                    $longitude !== null &&
                    $farmer->farm->latitude !== null &&
                    $farmer->farm->longitude !== null
                ) {
                    $distance = $this->distanceInKilometers(
                        $latitude,
                        $longitude,
                        (float) $farmer->farm->latitude,
                        (float) $farmer->farm->longitude
                    );
                }

                $farmer->setAttribute('distance_km', $distance);

                return $farmer;
            });

        if ($latitude !== null && $longitude !== null) {
            $farmers = $farmers
                ->sortBy(fn (Farmer $farmer) => $farmer->distance_km ?? INF)
                ->values();
        }

        $perPage = 12;
        $page = (int) ($validated['page'] ?? 1);
        $results = new LengthAwarePaginator(
            $farmers->forPage($page, $perPage)->values(),
            $farmers->count(),
            $perPage,
            $page,
            ['path' => route('farmers.nearby')]
        );

        return view('farmers.nearby', [
            'results' => $results,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'municipality' => $municipality,
            'province' => $province,
        ]);
    }

    private function distanceInKilometers(
        float $fromLatitude,
        float $fromLongitude,
        float $toLatitude,
        float $toLongitude
    ): float {
        $earthRadius = 6371;
        $latitudeDelta = deg2rad($toLatitude - $fromLatitude);
        $longitudeDelta = deg2rad($toLongitude - $fromLongitude);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($fromLatitude))
            * cos(deg2rad($toLatitude))
            * sin($longitudeDelta / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }

    public function edit(Farmer $farmer): View
    {
        $farms = Farm::query()
            ->where('status', 'active')
            ->orWhere('id', $farmer->farm_id)
            ->orderBy('name')
            ->get();

        return view('farmers.edit', compact('farmer', 'farms'));
    }

    public function update(Request $request, Farmer $farmer): RedirectResponse
    {
        $validated = $this->validatedData($request);

        if (
            (int) $farmer->farm_id !== (int) $validated['farm_id'] &&
            $farmer->swine()->exists()
        ) {
            throw ValidationException::withMessages([
                'farm_id' => 'A farmer with registered swine cannot be reassigned to another farm.',
            ]);
        }

        $farmer->update($validated);

        return redirect()
            ->route('farmers.show', $farmer)
            ->with('success', 'Farmer information updated successfully.');
    }

    /**
     * @return array{farm_id: int, name: string, contact_number: string, email?: string|null, address: string}
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        $activeFarm = Farm::query()
            ->whereKey($validated['farm_id'])
            ->where('status', 'active')
            ->exists();

        if (!$activeFarm) {
            throw ValidationException::withMessages([
                'farm_id' => 'The selected farm is inactive.',
            ]);
        }

        return $validated;
    }
}
