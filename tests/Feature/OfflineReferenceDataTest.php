<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Farmer;
use App\Models\FarmLocation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Swine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineReferenceDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_users_receive_minimal_active_reference_data(): void
    {
        $this->actingAs($this->userWithPermissions('register-swine'));

        $farm = $this->createFarm('ACTIVE-01', 'Active Farm');
        $inactiveFarm = $this->createFarm('INACTIVE-01', 'Inactive Farm', 'inactive');
        $farmer = Farmer::create([
            'farm_id' => $farm->id,
            'name' => 'Offline Farmer',
            'contact_number' => '09171112222',
            'email' => 'private@example.test',
            'address' => 'Private address',
        ]);
        $location = FarmLocation::create([
            'farm_id' => $farm->id,
            'location_code' => 'PEN-01',
            'name' => 'Active Pen',
            'status' => 'active',
        ]);
        FarmLocation::create([
            'farm_id' => $inactiveFarm->id,
            'location_code' => 'PEN-02',
            'name' => 'Inactive Pen',
            'status' => 'inactive',
        ]);
        $swine = Swine::create([
            'farm_id' => $farm->id,
            'farmer_id' => $farmer->id,
            'current_location_id' => $location->id,
            'tag_number' => 'SW-REF-01',
            'sex' => 'female',
            'status' => 'active',
        ]);
        Swine::create([
            'farm_id' => $farm->id,
            'tag_number' => 'SW-REF-INACTIVE',
            'sex' => 'female',
            'status' => 'inactive',
        ]);

        $this->getJson(route('offline.reference-data'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonCount(1, 'farms')
            ->assertJsonPath('farms.0.id', $farm->id)
            ->assertJsonCount(1, 'farmers')
            ->assertJsonPath('farmers.0.id', $farmer->id)
            ->assertJsonPath('farmers.0.farm_name', 'Active Farm')
            ->assertJsonMissingPath('farmers.0.contact_number')
            ->assertJsonMissingPath('farmers.0.email')
            ->assertJsonMissingPath('farmers.0.address')
            ->assertJsonCount(1, 'locations')
            ->assertJsonPath('locations.0.id', $location->id)
            ->assertJsonCount(1, 'swine')
            ->assertJsonPath('swine.0.id', $swine->id)
            ->assertJsonPath('swine.0.tag_number', 'SW-REF-01')
            ->assertJsonStructure(['synced_at']);
    }

    public function test_reference_data_is_empty_for_users_without_related_permissions(): void
    {
        $response = $this->actingAs($this->userWithPermissions('view-reports'))
            ->getJson(route('offline.reference-data'))
            ->assertOk()
            ->assertJsonCount(0, 'farms')
            ->assertJsonCount(0, 'farmers')
            ->assertJsonCount(0, 'locations')
            ->assertJsonCount(0, 'swine');

        $this->assertNotEmpty($response->json('synced_at'));
    }

    public function test_reference_data_requires_authentication(): void
    {
        $this->get(route('offline.reference-data'))
            ->assertRedirect(route('login'));
    }

    private function createFarm(
        string $code,
        string $name,
        string $status = 'active',
    ): Farm {
        return Farm::create([
            'farm_code' => $code,
            'name' => $name,
            'status' => $status,
        ]);
    }

    private function userWithPermissions(string ...$permissionSlugs): User
    {
        $role = Role::create([
            'name' => 'Offline Reference Test Role',
            'slug' => 'offline-reference-test-'.bin2hex(random_bytes(4)),
        ]);
        $permissions = collect($permissionSlugs)->map(function ($slug) {
            return Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst(str_replace('-', ' ', $slug))]
            );
        });
        $role->permissions()->attach($permissions->pluck('id'));

        return User::factory()->create(['role_id' => $role->id]);
    }
}
