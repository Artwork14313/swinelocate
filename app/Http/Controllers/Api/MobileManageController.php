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
use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MobileManageController extends Controller
{
    private const PERMISSIONS = [
        'farms' => 'manage-farms',
        'farmers' => 'manage-farmers',
        'locations' => 'manage-locations',
        'users' => 'manage-users',
        'roles' => 'manage-roles',
        'swine' => 'manage-swine',
        'health-records' => 'manage-health',
        'vaccinations' => 'manage-health',
        'weight-records' => 'record-weight',
    ];

    public function store(Request $request, string $resource): JsonResponse
    {
        $this->authorizeResource($request, $resource);

        $record = match ($resource) {
            'farms' => Farm::query()->create(
                $this->farmRules($request) + ['status' => 'active']
            ),
            'farmers' => $this->saveFarmer($request),
            'locations' => $this->saveLocation($request),
            'users' => $this->saveUser($request),
            default => abort(404),
        };

        return response()->json(['data' => $record], 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($request, $resource);

        $record = match ($resource) {
            'farms' => $this->updateFarm($request, $id),
            'farmers' => $this->saveFarmer($request, Farmer::query()->findOrFail($id)),
            'locations' => $this->saveLocation($request, FarmLocation::query()->findOrFail($id)),
            'users' => $this->saveUser($request, $this->editableUser($id)),
            'roles' => $this->updateRole($request, $id),
            'swine' => $this->updateSwine($request, $id),
            'health-records', 'vaccinations' => $this->updateHealth($request, $id),
            'weight-records' => $this->updateWeight($request, $id),
            default => abort(404),
        };

        return response()->json(['data' => $record]);
    }

    public function activate(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($request, $resource);

        switch ($resource) {
            case 'farms':
                Farm::query()->findOrFail($id)->update(['status' => 'active']);
                break;
            case 'locations':
                $location = FarmLocation::query()->with('farm')->findOrFail($id);
                abort_unless($location->farm && $location->farm->status === 'active', 422,
                    'This location cannot be activated because its farm is inactive.');
                $location->update(['status' => 'active']);
                break;
            case 'swine':
                $swine = Swine::query()->with('farm')->findOrFail($id);
                abort_unless($swine->status === 'inactive', 422, 'Only inactive swine can be activated.');
                abort_unless($swine->farm && $swine->farm->status === 'active', 422,
                    'This swine cannot be activated because its farm is inactive.');
                $swine->update(['status' => 'active', 'is_for_sale' => false, 'asking_price' => null]);
                break;
            default:
                abort(404);
        }

        return response()->json(['message' => 'Done.']);
    }

    public function destroy(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($request, $resource);

        switch ($resource) {
            case 'farms':
                Farm::query()->findOrFail($id)->update(['status' => 'inactive']);
                break;
            case 'locations':
                FarmLocation::query()->findOrFail($id)->update(['status' => 'inactive']);
                break;
            case 'swine':
                $swine = Swine::query()->findOrFail($id);
                abort_unless($swine->status === 'active', 422, 'Only active swine can be deactivated.');
                $swine->update([
                    'status' => 'inactive',
                    'is_for_sale' => false,
                    'asking_price' => null,
                ]);
                break;
            case 'health-records':
            case 'vaccinations':
                HealthRecord::query()->findOrFail($id)->delete();
                break;
            case 'weight-records':
                WeightRecord::query()->findOrFail($id)->delete();
                break;
            case 'users':
                $user = $this->editableUser($id);
                abort_if($user->getKey() === $request->user()->getKey(), 422, 'You cannot delete your own account.');
                $user->tokens()->delete();
                $user->delete();
                break;
            default:
                abort(404);
        }

        return response()->json(['message' => 'Done.']);
    }

    public function options(Request $request, string $resource): ?array
    {
        if ($resource === 'farmers' || $resource === 'locations') {
            return [
                'farms' => Farm::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'farm_code']),
            ];
        }

        if ($resource === 'users') {
            return [
                'roles' => Role::query()->where('slug', '!=', 'superadmin')->orderBy('name')->get(['id', 'name']),
                'farms' => Farm::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'farm_code']),
            ];
        }

        return null;
    }

    private function authorizeResource(Request $request, string $resource): void
    {
        abort_unless(isset(self::PERMISSIONS[$resource]), 404);
        abort_unless($request->user()->hasPermission(self::PERMISSIONS[$resource]), 403);
    }

    private function farmRules(Request $request, ?Farm $farm = null): array
    {
        return $request->validate([
            'farm_code' => ['required', 'string', 'max:50', Rule::unique('farms', 'farm_code')->ignore($farm?->getKey())],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
    }

    private function updateFarm(Request $request, int $id): Farm
    {
        $farm = Farm::query()->findOrFail($id);
        $farm->update($this->farmRules($request, $farm));

        return $farm;
    }

    private function saveFarmer(Request $request, ?Farmer $farmer = null): Farmer
    {
        $validated = $request->validate([
            'farm_id' => ['required', 'integer', Rule::exists('farms', 'id')->where('status', 'active')],
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        if ($farmer) {
            abort_if(
                (int) $farmer->farm_id !== (int) $validated['farm_id'] && $farmer->swine()->exists(),
                422,
                'A farmer with registered swine cannot be reassigned to another farm.'
            );
            $farmer->update($validated);

            return $farmer;
        }

        return Farmer::query()->create($validated);
    }

    private function saveLocation(Request $request, ?FarmLocation $location = null): FarmLocation
    {
        $farmId = $location?->farm_id ?? $request->input('farm_id');

        $validated = $request->validate([
            'farm_id' => [$location ? 'sometimes' : 'required', 'integer', Rule::exists('farms', 'id')->where('status', 'active')],
            'location_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('farm_locations', 'location_code')
                    ->where('farm_id', $farmId)
                    ->ignore($location?->getKey()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($location) {
            unset($validated['farm_id']);
            $location->update($validated);

            return $location;
        }

        return FarmLocation::query()->create($validated + ['status' => 'active']);
    }

    private function editableUser(int $id): User
    {
        $user = User::query()->findOrFail($id);
        abort_if($user->isSuperadmin(), 404);

        return $user;
    }

    private function saveUser(Request $request, ?User $user = null): User
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->getKey())],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role_id' => ['required', Rule::exists('roles', 'id')->where(fn ($q) => $q->where('slug', '!=', 'superadmin'))],
            'farm_id' => ['nullable', 'exists:farms,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($user) {
            $user->update($validated);
        } else {
            $user = User::query()->create($validated);
        }

        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        if ($user->status === 'inactive') {
            $user->tokens()->delete();
        }

        return $user->load(['role', 'farm']);
    }

    private function updateRole(Request $request, int $id): Role
    {
        $role = Role::query()->findOrFail($id);
        abort_if($role->slug === 'superadmin', 403, 'Superadmin permissions are protected.');

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'slug')->where(fn ($q) => $q->where('slug', '!=', 'superadmin'))],
        ]);

        $ids = Permission::query()->whereIn('slug', $validated['permissions'] ?? [])->pluck('id');
        $role->permissions()->sync($ids);

        return $role->load('permissions');
    }

    private function updateSwine(Request $request, int $id): Swine
    {
        $swine = Swine::query()->with('farm')->findOrFail($id);
        abort_if($swine->farm && $swine->farm->status !== 'active', 403, 'The farm is inactive.');

        $validated = $request->validate([
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
            'farmer_id' => ['required', 'integer', 'exists:farmers,id'],
            'current_location_id' => ['nullable', 'integer', 'exists:farm_locations,id'],
            'tag_number' => ['required', 'string', 'max:255', Rule::unique('swine', 'tag_number')->ignore($swine->getKey())],
            'sex' => ['required', Rule::in(['male', 'female'])],
            'breed' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'acquisition_date' => ['nullable', 'date', 'before_or_equal:today'],
            'source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_for_sale' => ['sometimes', 'boolean'],
            'asking_price' => ['nullable', 'numeric', 'min:0.01', 'max:99999999.99'],
        ]);

        abort_unless(
            Farmer::query()->whereKey($validated['farmer_id'])->where('farm_id', $validated['farm_id'])->exists(),
            422,
            'Select a farmer registered to the selected farm.'
        );

        if (! empty($validated['current_location_id'])) {
            abort_unless(
                FarmLocation::query()->whereKey($validated['current_location_id'])
                    ->where('farm_id', $validated['farm_id'])->where('status', 'active')->exists(),
                422,
                'Select an active location in the selected farm.'
            );
        }

        $forSale = $request->boolean('is_for_sale') && $swine->status === 'active';
        abort_if($forSale && empty($validated['asking_price']), 422, 'Enter an asking price for swine marked for sale.');

        $validated['is_for_sale'] = $forSale;
        $validated['asking_price'] = $forSale ? $validated['asking_price'] : null;
        $swine->update($validated);

        return $swine->load(['farm', 'farmer', 'currentLocation']);
    }

    private function updateHealth(Request $request, int $id): HealthRecord
    {
        $record = HealthRecord::query()->findOrFail($id);

        $validated = $request->validate([
            'record_date' => ['required', 'date', 'before_or_equal:today'],
            'record_type' => ['required', Rule::in([
                'Routine Examination', 'Vaccination', 'Illness', 'Treatment', 'Injury', 'Follow-up', 'Other',
            ])],
            'vaccine_name' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $request->input('record_type') === 'Vaccination')],
            'dose' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'next_due_date' => ['nullable', 'date', 'after_or_equal:record_date'],
            'symptoms' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'veterinary_assessment' => ['nullable', 'string'],
            'health_status' => ['required', Rule::in(['healthy', 'under_observation', 'sick', 'recovering'])],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['record_type'] !== 'Vaccination') {
            $validated['vaccine_name'] = $validated['dose'] = $validated['batch_number'] = $validated['next_due_date'] = null;
        }

        $record->update($validated);

        return $record->load(['swine', 'recordedBy']);
    }

    private function updateWeight(Request $request, int $id): WeightRecord
    {
        $record = WeightRecord::query()->findOrFail($id);

        $record->update($request->validate([
            'record_date' => ['required', 'date', 'before_or_equal:today'],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]));

        return $record->load(['swine', 'recordedBy']);
    }
}
