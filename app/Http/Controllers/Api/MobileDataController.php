<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\FarmLocation;
use App\Models\HealthRecord;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Swine;
use App\Models\SwineMovement;
use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MobileDataController extends Controller
{
    private const RESOURCES = [
        'farms' => [Farm::class, 'manage-farms', ['locations', 'swine']],
        'farmers' => [Farmer::class, 'manage-farmers', ['farm']],
        'locations' => [FarmLocation::class, 'manage-locations', ['farm']],
        'swine' => [Swine::class, 'manage-swine', ['farm', 'farmer', 'currentLocation', 'latestWeightRecord']],
        'users' => [User::class, 'manage-users', ['role', 'farm']],
        'roles' => [Role::class, 'manage-roles', ['permissions']],
        'health-records' => [HealthRecord::class, 'manage-health', ['swine', 'recordedBy']],
        'vaccinations' => [HealthRecord::class, 'manage-health', ['swine', 'recordedBy']],
        'weight-records' => [WeightRecord::class, 'record-weight', ['swine', 'recordedBy']],
        'movements' => [SwineMovement::class, 'manage-movements', ['swine', 'fromLocation', 'toLocation', 'toFarmer', 'recordedBy']],
        'growth' => [Swine::class, 'record-weight', ['weightRecords']],
        'traceability' => [Swine::class, 'view-traceability', ['farm', 'farmer', 'currentLocation', 'movements.toLocation', 'movements.toFarmer']],
    ];

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['role.permissions', 'farm']);
        $permissions = $user->role?->permissions->pluck('slug')->all() ?? [];

        $data = [
            'user' => [
                'name' => $user->name,
                'role' => $user->role?->name,
                'farm' => $user->farm?->name,
                'permissions' => $permissions,
            ],
            'stats' => [],
            'modules' => [],
        ];

        if (\in_array('manage-swine', $permissions, true)) {
            $data['stats']['swine'] = Swine::query()->count();
            $data['stats']['active_swine'] = Swine::query()
                ->where('status', 'active')
                ->count();
        }

        if (\in_array('manage-farms', $permissions, true)) {
            $data['stats']['farms'] = Farm::query()->count();
        }

        if (\in_array('manage-health', $permissions, true)) {
            $data['stats']['health_records'] = HealthRecord::query()->count();
            $data['stats']['vaccinations_due'] = HealthRecord::query()
                ->where('record_type', 'Vaccination')
                ->whereNotNull('next_due_date')
                ->whereDate('next_due_date', '<=', now()->addDays(7))
                ->count();
        }

        foreach (self::RESOURCES as $resource => [, $permission]) {
            if ($user->hasPermission($permission)) {
                $data['modules'][] = [
                    'id' => $resource,
                    'label' => $this->label($resource),
                ];
            }
        }

        if (
            $user->hasPermission('register-swine')
            && ! $user->hasPermission('manage-swine')
        ) {
            $data['modules'][] = [
                'id' => 'register-swine',
                'label' => 'Register Swine',
            ];
        }

        if ($user->hasPermission('view-reports')) {
            $data['modules'][] = [
                'id' => 'reports',
                'label' => 'Reports',
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function publicNearby(): JsonResponse
    {
        $farmers = Farmer::query()
            ->select(['id', 'farm_id', 'name', 'qr_token'])
            ->whereHas('farm', fn (Builder $query) => $query
                ->where('status', 'active')
                ->whereRaw('LOWER(TRIM(municipality)) IN (?, ?)', ['sagay', 'sagay city'])
                ->whereRaw('LOWER(TRIM(province)) = ?', ['negros occidental']))
            ->whereHas('swine', fn (Builder $query) => $query
                ->where('status', 'active')
                ->where('is_for_sale', true))
            ->with('farm:id,name,latitude,longitude')
            ->withCount([
                'swine' => fn (Builder $query) => $query
                    ->where('status', 'active')
                    ->where('is_for_sale', true),
            ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $farmers->map(fn (Farmer $farmer) => [
                'id' => $farmer->getKey(),
                'farmer_name' => $farmer->name,
                'farm_name' => $farmer->farm->name,
                'latitude' => $farmer->farm->latitude === null
                    ? null
                    : (float) $farmer->farm->latitude,
                'longitude' => $farmer->farm->longitude === null
                    ? null
                    : (float) $farmer->farm->longitude,
                'swine_count' => $farmer->swine_count,
                'profile_url' => route('farmers.public-profile', $farmer->qr_token),
            ])->values(),
        ]);
    }

    public function publicFarmer(string $token): JsonResponse
    {
        $farmer = Farmer::query()
            ->where('qr_token', $token)
            ->with([
                'farm:id,name,address,municipality,province,region',
                'swine' => fn ($query) => $query
                    ->where('status', 'active')
                    ->where('is_for_sale', true)
                    ->with('latestWeightRecord:weight_records.id,weight_records.swine_id,weight_records.record_date,weight_records.weight')
                    ->orderBy('breed')
                    ->orderBy('sex'),
            ])
            ->firstOrFail();

        return response()->json(['data' => [
            'name' => $farmer->name,
            'farm' => $farmer->farm?->only(['name', 'address', 'municipality', 'province', 'region']),
            'listings' => $farmer->swine->map(fn ($swine) => [
                'breed' => $swine->breed,
                'sex' => $swine->sex,
                'birth_date' => $swine->birth_date?->toDateString(),
                'asking_price' => $swine->asking_price,
                'latest_weight' => $swine->latestWeightRecord?->weight,
                'weight_date' => $swine->latestWeightRecord?->record_date?->toDateString(),
            ])->values(),
        ]]);
    }

    public function index(Request $request, string $resource): JsonResponse
    {
        if ($resource === 'reports') {
            return $this->reports($request);
        }

        [$model, , $relations] = $this->resource($request, $resource);
        $query = $model::query();

        if ($resource === 'farms') {
            $query->withCount(['locations', 'swine']);
        } elseif ($resource === 'traceability') {
            $query->with([
                'farm',
                'farmer',
                'currentLocation',
                'movements' => fn ($movementQuery) => $movementQuery
                    ->latest('movement_date')
                    ->limit(1),
            ]);
        } else {
            $query->with($relations);
        }

        if ($resource === 'vaccinations') {
            $query->where('record_type', 'Vaccination');
        }

        if ($resource === 'health-records') {
            if ($request->filled('swine_id')) {
                $query->where('swine_id', (int) $request->query('swine_id'));
            } else {
                // Only the most recent record of each swine; full history is per swine.
                $query->whereRaw('health_records.id = (select h2.id from health_records h2 '
                    .'where h2.swine_id = health_records.swine_id '
                    .'order by h2.record_date desc, h2.id desc limit 1)');
            }
        }

        if ($resource === 'users') {
            $query->whereDoesntHave('role', fn (Builder $role) => $role->where('slug', 'superadmin'));
        } elseif ($resource === 'roles') {
            $query->where('slug', '!=', 'superadmin');
        }

        $this->applySearch($query, $request, $resource);

        $records = $query->latest()->paginate(25);

        return response()->json([
            'data' => $records->items(),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
        ]);
    }

    public function show(Request $request, string $resource, int $id): JsonResponse
    {
        [$model, , $relations] = $this->resource($request, $resource);
        $record = $model::query()->with($relations)->findOrFail($id);

        if ($resource === 'vaccinations' && $record->record_type !== 'Vaccination') {
            abort(404);
        }

        return response()->json(['data' => $record]);
    }

    public function options(Request $request, string $resource): JsonResponse
    {
        $permissions = [
            'swine' => 'register-swine',
            'health-records' => 'manage-health',
            'weight-records' => 'record-weight',
            'movements' => 'manage-movements',
        ];

        $extra = [
            'farmers' => 'manage-farmers',
            'locations' => 'manage-locations',
            'users' => 'manage-users',
        ];
        if (isset($extra[$resource])) {
            abort_unless($request->user()->hasPermission($extra[$resource]), 403);

            return response()->json(['data' => app(MobileManageController::class)->options($request, $resource)]);
        }

        if ($resource === 'roles') {
            abort_unless($request->user()->hasPermission('manage-roles'), 403);

            return response()->json(['data' => [
                'permissions' => Permission::query()
                    ->where('slug', '!=', 'superadmin')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']),
            ]]);
        }

        abort_unless(isset($permissions[$resource]), 404);
        $allowed = $request->user()->hasPermission($permissions[$resource])
            || ($resource === 'swine' && $request->user()->hasPermission('manage-swine'));
        abort_unless($allowed, 403);

        return response()->json([
            'data' => [
                'swine' => Swine::query()
                    ->where('status', 'active')
                    ->orderBy('tag_number')
                    ->get(['id', 'tag_number', 'name', 'farm_id', 'farmer_id', 'current_location_id']),
                'farms' => Farm::query()
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name', 'farm_code']),
                'farmers' => Farmer::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'farm_id']),
                'locations' => FarmLocation::query()
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name', 'farm_id', 'location_code']),
            ],
        ]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        return match ($resource) {
            'swine' => $this->storeSwine($request),
            'health-records' => $this->storeHealthRecord($request),
            'vaccinations' => $this->storeHealthRecord($request, true),
            'weight-records' => $this->storeWeightRecord($request),
            'movements' => $this->storeMovement($request),
            default => app(MobileManageController::class)->store($request, $resource),
        };
    }

    private function storeSwine(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('register-swine'), 403);

        $validated = $request->validate([
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
            'farmer_id' => ['required', 'integer', 'exists:farmers,id'],
            'current_location_id' => ['nullable', 'integer', 'exists:farm_locations,id'],
            'tag_number' => ['required', 'string', 'max:255', 'unique:swine,tag_number'],
            'sex' => ['required', Rule::in(['male', 'female'])],
            'breed' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'acquisition_date' => ['nullable', 'date', 'before_or_equal:today'],
            'source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_for_sale' => ['sometimes', 'boolean'],
            'asking_price' => ['nullable', 'numeric', 'min:0.01', 'max:99999999.99'],
        ]);

        $farm = Farm::query()
            ->whereKey($validated['farm_id'])
            ->where('status', 'active')
            ->first();
        abort_if(! $farm, 422, 'The selected farm is inactive.');

        $farmerBelongsToFarm = Farmer::query()
            ->whereKey($validated['farmer_id'])
            ->where('farm_id', $farm->getKey())
            ->exists();
        abort_unless($farmerBelongsToFarm, 422, 'Select a farmer registered to the selected farm.');

        if (! empty($validated['current_location_id'])) {
            $validLocation = FarmLocation::query()
                ->whereKey($validated['current_location_id'])
                ->where('farm_id', $farm->getKey())
                ->where('status', 'active')
                ->exists();
            abort_unless($validLocation, 422, 'Select an active location in the selected farm.');
        }

        $validated['status'] = 'active';
        $validated['is_for_sale'] = $request->boolean('is_for_sale');
        $validated['asking_price'] = $validated['is_for_sale']
            ? ($validated['asking_price'] ?? null)
            : null;
        $swine = Swine::query()->create($validated);

        return response()->json(['data' => $swine->load(['farm', 'farmer', 'currentLocation'])], 201);
    }

    private function storeHealthRecord(Request $request, bool $vaccinationOnly = false): JsonResponse
    {
        abort_unless($request->user()->hasPermission('manage-health'), 403);

        $validated = $request->validate([
            'swine_id' => [
                'required',
                Rule::exists('swine', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'record_date' => ['required', 'date', 'before_or_equal:today'],
            'record_type' => [
                'required',
                Rule::in($vaccinationOnly ? ['Vaccination'] : [
                    'Routine Examination',
                    'Vaccination',
                    'Illness',
                    'Treatment',
                    'Injury',
                    'Follow-up',
                    'Other',
                ]),
            ],
            'vaccine_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $request->input('record_type') === 'Vaccination'),
            ],
            'dose' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:record_date'],
            'symptoms' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'veterinary_assessment' => ['nullable', 'string'],
            'health_status' => [
                'required',
                Rule::in(['healthy', 'under_observation', 'sick', 'recovering']),
            ],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['record_type'] !== 'Vaccination') {
            $validated['vaccine_name'] = null;
            $validated['dose'] = null;
            $validated['batch_number'] = null;
            $validated['next_due_date'] = null;
        }

        $validated['recorded_by'] = $request->user()->getKey();
        $record = HealthRecord::query()->create($validated);

        return response()->json(['data' => $record->load(['swine', 'recordedBy'])], 201);
    }

    private function storeWeightRecord(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('record-weight'), 403);

        $validated = $request->validate([
            'swine_id' => [
                'required',
                Rule::exists('swine', 'id')->where('status', 'active'),
            ],
            'record_date' => ['required', 'date', 'before_or_equal:today'],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['recorded_by'] = $request->user()->getKey();
        $record = WeightRecord::query()->create($validated);

        return response()->json(['data' => $record->load(['swine', 'recordedBy'])], 201);
    }

    private function storeMovement(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('manage-movements'), 403);

        $validated = $request->validate([
            'swine_id' => ['required', 'integer', 'exists:swine,id'],
            'to_farm_id' => ['required', 'integer', 'exists:farms,id'],
            'to_location_id' => ['required', 'integer', 'exists:farm_locations,id'],
            'to_farmer_id' => ['nullable', 'integer', 'exists:farmers,id'],
            'movement_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $swine = Swine::query()->with('farm')->findOrFail($validated['swine_id']);
        abort_unless($swine->status === 'active', 403, 'Only active swine can be moved.');
        abort_unless($swine->farm?->status === 'active', 403, 'The current farm is inactive.');

        $destinationFarm = Farm::query()
            ->whereKey($validated['to_farm_id'])
            ->where('status', 'active')
            ->first();
        $destination = FarmLocation::query()
            ->whereKey($validated['to_location_id'])
            ->where('farm_id', $validated['to_farm_id'])
            ->where('status', 'active')
            ->first();
        abort_unless($destinationFarm && $destination, 422, 'Select an active location in the destination farm.');

        $isFarmTransfer = (int) $destinationFarm->getKey() !== (int) $swine->farm_id;
        $destinationFarmerId = $isFarmTransfer
            ? ($validated['to_farmer_id'] ?? null)
            : $swine->farmer_id;
        $farmerBelongsToFarm = $destinationFarmerId !== null
            && Farmer::query()
                ->whereKey($destinationFarmerId)
                ->where('farm_id', $destinationFarm->getKey())
                ->exists();
        abort_if(
            $isFarmTransfer && ! $farmerBelongsToFarm,
            422,
            'Select a farmer registered to the destination farm.',
        );
        abort_if(
            ! $isFarmTransfer && $destinationFarmerId !== null && ! $farmerBelongsToFarm,
            422,
            'The selected farmer is not registered to the destination farm.',
        );
        abort_if(
            $swine->current_location_id !== null
            && (int) $swine->current_location_id === (int) $destination->getKey(),
            422,
            'The swine is already assigned to this location.',
        );

        $movement = DB::transaction(function () use ($request, $swine, $destination, $destinationFarm, $destinationFarmerId, $validated) {
            $movement = SwineMovement::query()->create([
                'swine_id' => $swine->getKey(),
                'from_location_id' => $swine->current_location_id,
                'to_location_id' => $destination->getKey(),
                'to_farmer_id' => $destinationFarmerId,
                'movement_date' => $validated['movement_date'],
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => $request->user()->getKey(),
                'status' => 'completed',
                'conflict_resolution' => null,
            ]);

            $swine->update([
                'farm_id' => $destinationFarm->getKey(),
                'farmer_id' => $destinationFarmerId,
                'current_location_id' => $destination->getKey(),
            ]);

            return $movement;
        });

        return response()->json([
            'data' => $movement->load(['swine', 'fromLocation', 'toLocation', 'toFarmer', 'recordedBy']),
        ], 201);
    }

    private function reports(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('view-reports'), 403);

        return response()->json([
            'data' => [
                'total_swine' => Swine::query()->count(),
                'active_swine' => Swine::query()->where('status', 'active')->count(),
                'farms' => Farm::query()->count(),
                'health_records' => HealthRecord::query()->count(),
                'weight_records' => WeightRecord::query()->count(),
                'movements' => SwineMovement::query()->count(),
                'swine_by_status' => Swine::query()
                    ->select('status')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('status')
                    ->orderByDesc('total')
                    ->get(),
                'swine_by_breed' => Swine::query()
                    ->select('breed')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('breed')
                    ->orderByDesc('total')
                    ->get(),
            ],
        ]);
    }

    public function scan(Request $request, string $token): JsonResponse
    {
        abort_unless($request->user()->hasPermission('scan-qr'), 403);

        $swine = Swine::query()
            ->with(['farm', 'farmer', 'currentLocation', 'healthRecords', 'weightRecords', 'movements'])
            ->where('qr_token', $token)
            ->first();

        if (! $swine) {
            throw ValidationException::withMessages([
                'token' => ['No swine record matches this QR code.'],
            ]);
        }

        return response()->json(['data' => $swine]);
    }

    private function resource(Request $request, string $resource): array
    {
        if (! isset(self::RESOURCES[$resource])) {
            abort(404);
        }

        [$model, $permission, $relations] = self::RESOURCES[$resource];
        abort_unless($request->user()->hasPermission($permission), 403);

        if ($resource === 'growth') {
            $relations = ['weightRecords' => fn ($query) => $query->latest('record_date')];
        }

        return [$model, $permission, $relations];
    }

    private function applySearch(Builder $query, Request $request, string $resource): void
    {
        $search = trim((string) $request->query('search', ''));

        if ($search === '') {
            return;
        }

        $searchFields = match ($resource) {
            'farms' => ['name', 'farm_code'],
            'farmers' => ['name', 'contact_number'],
            'locations' => ['name', 'location_code'],
            'swine', 'traceability', 'growth' => ['tag_number', 'name'],
            'users' => ['name', 'email', 'phone'],
            'health-records', 'vaccinations' => ['record_type', 'vaccine_name', 'health_status'],
            'weight-records' => ['record_date'],
            'movements' => ['reason'],
            default => [],
        };

        if ($searchFields === []) {
            return;
        }

        $query->where(function (Builder $query) use ($searchFields, $search): void {
            foreach ($searchFields as $index => $field) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $query->{$method}($field, 'like', "%{$search}%");
            }
        });
    }

    private function label(string $resource): string
    {
        return match ($resource) {
            'locations' => 'Farm Locations',
            'health-records' => 'Health Records',
            'weight-records' => 'Weight Records',
            'movements' => 'Swine Movements',
            'traceability' => 'Traceability',
            default => str($resource)->replace('-', ' ')->title()->toString(),
        };
    }
}
