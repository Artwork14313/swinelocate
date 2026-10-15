<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Farmer;
use App\Models\FarmLocation;
use App\Models\HealthRecord;
use App\Models\Role;
use App\Models\Swine;
use App\Models\SwineMovement;
use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        if ($user->hasPermission('view-reports')) {
            $data['modules'][] = [
                'id' => 'reports',
                'label' => 'Reports',
            ];
        }

        return response()->json(['data' => $data]);
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
                'movements' => fn (Builder $movementQuery) => $movementQuery
                    ->latest('movement_date')
                    ->limit(1),
            ]);
        } else {
            $query->with($relations);
        }

        if ($resource === 'vaccinations') {
            $query->where('record_type', 'Vaccination');
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
            $relations = ['weightRecords' => fn (Builder $query) => $query->latest('record_date')->limit(10)];
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
