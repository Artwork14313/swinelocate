<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Farmer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Swine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_register_a_farmer_profile(): void
    {
        $this->actingAs($this->userWithPermissions('manage-farmers'));
        $farm = $this->createFarm('F-101');

        $this->get(route('farmers.create'))
            ->assertOk()
            ->assertSee('Contact Number');

        $this->post(route('farmers.store'), [
            'farm_id' => $farm->id,
            'name' => 'Alex Farmer',
            'contact_number' => '09171234567',
            'email' => 'alex@example.test',
            'address' => 'Barangay Sample, Example City',
        ])
            ->assertRedirect(route('farmers.index'));

        $farmer = Farmer::query()->where('name', 'Alex Farmer')->firstOrFail();

        $this->assertDatabaseHas('farmers', [
            'farm_id' => $farm->id,
            'name' => 'Alex Farmer',
            'contact_number' => '09171234567',
            'address' => 'Barangay Sample, Example City',
        ]);

        $this->get(route('farmers.show', $farmer))
            ->assertOk()
            ->assertSee('Alex Farmer')
            ->assertSee('Example City');
    }

    public function test_swine_registration_requires_a_farmer_from_its_farm(): void
    {
        $this->actingAs($this->userWithPermissions('register-swine'));
        $farm = $this->createFarm('F-102');
        $otherFarm = $this->createFarm('F-103');
        $farmer = $this->createFarmer($otherFarm, 'Taylor Farmer');

        $this->from(route('swine.create'))
            ->post(route('swine.store'), [
                'farm_id' => $farm->id,
                'farmer_id' => $farmer->id,
                'tag_number' => 'SW-101',
                'sex' => 'female',
            ])
            ->assertRedirect(route('swine.create'))
            ->assertSessionHasErrors('farmer_id');

        $this->assertDatabaseMissing('swine', [
            'tag_number' => 'SW-101',
        ]);
    }

    public function test_swine_registration_lists_farmers_and_shows_empty_farm_guidance(): void
    {
        $this->actingAs($this->userWithPermissions('register-swine'));
        $farm = $this->createFarm('F-107');
        $farmer = $this->createFarmer($farm, 'Riley Farmer');

        $this->get(route('swine.create'))
            ->assertOk()
            ->assertSee('Riley Farmer')
            ->assertSee('data-farm-id="'.$farm->id.'"', false)
            ->assertSee('Register a farmer for this farm')
            ->assertSee('List this swine as available for sale')
            ->assertSee('Asking Price (optional)');
    }

    public function test_swine_can_be_listed_for_sale_with_an_optional_asking_price(): void
    {
        $this->actingAs($this->userWithPermissions('register-swine'));
        $farm = $this->createFarm('F-109');
        $farmer = $this->createFarmer($farm, 'Morgan Farmer');

        $this->post(route('swine.store'), [
            'farm_id' => $farm->id,
            'farmer_id' => $farmer->id,
            'tag_number' => 'SW-109',
            'sex' => 'female',
            'is_for_sale' => '1',
            'asking_price' => '15000.00',
        ])->assertRedirect(route('swine.index'));

        $this->assertDatabaseHas('swine', [
            'tag_number' => 'SW-109',
            'is_for_sale' => true,
            'asking_price' => '15000.00',
        ]);
    }

    public function test_farmer_registration_can_preselect_the_farm_from_swine_registration(): void
    {
        $this->actingAs($this->userWithPermissions('manage-farmers'));
        $farm = $this->createFarm('F-108');

        $this->get(route('farmers.create', ['farm_id' => $farm->id]))
            ->assertOk()
            ->assertSee('value="'.$farm->id.'" selected', false);
    }

    public function test_a_farmer_can_be_associated_with_multiple_swine(): void
    {
        $this->actingAs($this->userWithPermissions('register-swine'));
        $farm = $this->createFarm('F-104');
        $farmer = $this->createFarmer($farm, 'Jordan Farmer');

        foreach (['SW-201', 'SW-202'] as $tagNumber) {
            $this->post(route('swine.store'), [
                'farm_id' => $farm->id,
                'farmer_id' => $farmer->id,
                'tag_number' => $tagNumber,
                'sex' => 'male',
            ])->assertRedirect(route('swine.index'));
        }

        $this->assertSame(2, $farmer->swine()->count());
        $this->assertNotSame(
            Swine::where('tag_number', 'SW-201')->value('qr_token'),
            Swine::where('tag_number', 'SW-202')->value('qr_token')
        );
    }

    public function test_retried_offline_swine_sync_does_not_create_duplicates(): void
    {
        $this->actingAs($this->userWithPermissions('register-swine'));
        $farm = $this->createFarm('F-105');
        $farmer = $this->createFarmer($farm, 'Casey Farmer');
        $localId = '1d47c873-6ca3-4e92-b019-c9c6015df7f4';

        $payload = [
            'local_id' => $localId,
            'farm_id' => $farm->id,
            'farmer_id' => $farmer->id,
            'tag_number' => 'SW-OFFLINE-301',
            'sex' => 'female',
            'qr_token' => 'SWL-OFFLINE-301',
        ];

        $this->postJson(route('swine.sync.store'), $payload)
            ->assertCreated()
            ->assertJsonPath('swine_id', 1);

        $this->postJson(route('swine.sync.store'), $payload)
            ->assertOk()
            ->assertJsonPath('already_synced', true);

        $this->assertSame(1, Swine::where('local_id', $localId)->count());
    }

    public function test_traceability_lookup_displays_the_swine_farmer(): void
    {
        $this->actingAs($this->userWithPermissions('view-traceability'));
        $farm = $this->createFarm('F-106');
        $farmer = $this->createFarmer($farm, 'Morgan Farmer');

        Swine::create([
            'farm_id' => $farm->id,
            'farmer_id' => $farmer->id,
            'tag_number' => 'SW-TRACE-401',
            'sex' => 'female',
            'status' => 'active',
        ]);

        $this->get(route('traceability.index'))
            ->assertOk()
            ->assertSee('Morgan Farmer')
            ->assertSee('SW-TRACE-401')
            ->assertSee('View Traceability');
    }

    private function createFarm(string $code): Farm
    {
        return Farm::create([
            'farm_code' => $code,
            'name' => "Farm {$code}",
        ]);
    }

    private function createFarmer(Farm $farm, string $name): Farmer
    {
        return Farmer::create([
            'farm_id' => $farm->id,
            'name' => $name,
            'contact_number' => '09170000000',
            'address' => 'Registered farmer address',
        ]);
    }

    private function userWithPermissions(string ...$permissionSlugs): User
    {
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role-'.bin2hex(random_bytes(4)),
        ]);

        $permissions = collect($permissionSlugs)->map(function ($slug) {
            return Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst(str_replace('-', ' ', $slug))]
            );
        });

        $role->permissions()->attach($permissions->pluck('id'));

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }
}
