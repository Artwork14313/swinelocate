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

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_sign_in_and_revoke_the_mobile_token(): void
    {
        $user = $this->userWithPermissions('manage-farms');

        $login = $this->postJson('/api/mobile/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.permissions.0', 'manage-farms');

        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/mobile/dashboard')
            ->assertOk()
            ->assertJsonPath('data.modules.0.id', 'farms');

        $this->withToken($token)
            ->postJson('/api/mobile/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_mobile_login_allows_admin_provisioned_users_without_email_verification(): void
    {
        $inactive = $this->userWithPermissions();
        $inactive->update(['status' => 'inactive']);

        $this->postJson('/api/mobile/login', [
            'email' => $inactive->email,
            'password' => 'password',
        ])->assertUnprocessable();

        $unverified = User::factory()->unverified()->create([
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/mobile/login', [
            'email' => $unverified->email,
            'password' => 'password',
        ])->assertOk();
    }

    public function test_mobile_data_is_limited_to_the_users_permissions(): void
    {
        $user = $this->userWithPermissions('manage-farms');
        $farm = Farm::create([
            'farm_code' => 'MOBILE-01',
            'name' => 'Mobile Farm',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/mobile/data/farms')
            ->assertOk()
            ->assertJsonPath('data.0.id', $farm->getKey());

        $this->withToken($token)
            ->getJson('/api/mobile/data/swine')
            ->assertForbidden();
    }

    public function test_mobile_api_requires_bearer_authentication(): void
    {
        $this->getJson('/api/mobile/dashboard')
            ->assertUnauthorized();
    }

    public function test_public_mobile_map_returns_only_sagay_sale_farmers_with_saved_coordinates(): void
    {
        $sagayFarm = Farm::create([
            'farm_code' => 'MAP-SAGAY-01',
            'name' => 'Sagay Test Farm',
            'municipality' => 'Sagay City',
            'province' => 'Negros Occidental',
            'latitude' => 10.89765,
            'longitude' => 123.41456,
            'status' => 'active',
        ]);
        $sagayFarmer = $this->createFarmer($sagayFarm, 'Sagay Map Farmer');
        Swine::create([
            'farm_id' => $sagayFarm->getKey(),
            'farmer_id' => $sagayFarmer->getKey(),
            'tag_number' => 'MAP-SAGAY-PIG-01',
            'sex' => 'female',
            'status' => 'active',
            'is_for_sale' => true,
        ]);

        $outsideFarm = Farm::create([
            'farm_code' => 'MAP-OUTSIDE-01',
            'name' => 'Outside Test Farm',
            'municipality' => 'Victorias City',
            'province' => 'Negros Occidental',
            'latitude' => 10.89765,
            'longitude' => 123.41456,
            'status' => 'active',
        ]);
        $outsideFarmer = $this->createFarmer($outsideFarm, 'Outside Map Farmer');
        Swine::create([
            'farm_id' => $outsideFarm->getKey(),
            'farmer_id' => $outsideFarmer->getKey(),
            'tag_number' => 'MAP-OUTSIDE-PIG-01',
            'sex' => 'female',
            'status' => 'active',
            'is_for_sale' => true,
        ]);

        $this->getJson('/api/mobile/public/farmers-nearby')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.farmer_name', 'Sagay Map Farmer')
            ->assertJsonPath('data.0.farm_name', 'Sagay Test Farm')
            ->assertJsonPath('data.0.latitude', 10.89765)
            ->assertJsonPath('data.0.longitude', 123.41456)
            ->assertJsonPath('data.0.swine_count', 1)
            ->assertJsonPath('data.0.profile_url', route('farmers.public-profile', $sagayFarmer->qr_token));
    }

    public function test_deactivated_users_lose_access_and_their_token_is_revoked(): void
    {
        $user = $this->userWithPermissions('manage-farms');
        $token = $user->createToken('android')->plainTextToken;
        $user->update(['status' => 'inactive']);

        $this->withToken($token)
            ->getJson('/api/mobile/dashboard')
            ->assertForbidden();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_mobile_can_register_swine_and_record_health_and_weight(): void
    {
        $user = $this->userWithPermissions(
            'register-swine',
            'manage-health',
            'record-weight',
        );
        $farm = Farm::create([
            'farm_code' => 'MOB-01',
            'name' => 'Mobile Farm',
            'status' => 'active',
        ]);
        $farmer = $this->createFarmer($farm, 'Mobile Farmer');
        $token = $user->createToken('android')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/mobile/options/swine')
            ->assertOk()
            ->assertJsonPath('data.farms.0.id', $farm->getKey())
            ->assertJsonMissingPath('data.farmers.0.contact_number');

        $swineResponse = $this->withToken($token)
            ->postJson('/api/mobile/data/swine', [
                'farm_id' => $farm->getKey(),
                'farmer_id' => $farmer->getKey(),
                'tag_number' => 'MOBILE-PIG-01',
                'name' => 'Pig name should be ignored',
                'sex' => 'female',
                'breed' => 'Landrace',
            ])
            ->assertCreated()
            ->assertJsonPath('data.tag_number', 'MOBILE-PIG-01')
            ->assertJsonPath('data.name', null);

        $swineId = $swineResponse->json('data.id');

        $this->withToken($token)
            ->postJson('/api/mobile/data/health-records', [
                'swine_id' => $swineId,
                'record_date' => today()->toDateString(),
                'record_type' => 'Vaccination',
                'vaccine_name' => 'Test vaccine',
                'health_status' => 'healthy',
            ])
            ->assertCreated()
            ->assertJsonPath('data.vaccine_name', 'Test vaccine');

        $this->withToken($token)
            ->postJson('/api/mobile/data/weight-records', [
                'swine_id' => $swineId,
                'record_date' => today()->toDateString(),
                'weight' => 45.5,
            ])
            ->assertCreated()
            ->assertJsonPath('data.weight', '45.50');
    }

    public function test_mobile_movement_updates_current_farm_and_location(): void
    {
        $user = $this->userWithPermissions('manage-movements');
        $origin = Farm::create([
            'farm_code' => 'MOB-ORIGIN',
            'name' => 'Origin Farm',
            'status' => 'active',
        ]);
        $destination = Farm::create([
            'farm_code' => 'MOB-DEST',
            'name' => 'Destination Farm',
            'status' => 'active',
        ]);
        $originFarmer = $this->createFarmer($origin, 'Origin Farmer');
        $destinationFarmer = $this->createFarmer($destination, 'Destination Farmer');
        $originLocation = FarmLocation::create([
            'farm_id' => $origin->getKey(),
            'location_code' => 'ORIGIN-01',
            'name' => 'Origin Pen',
            'status' => 'active',
        ]);
        $destinationLocation = FarmLocation::create([
            'farm_id' => $destination->getKey(),
            'location_code' => 'DEST-01',
            'name' => 'Destination Pen',
            'status' => 'active',
        ]);
        $swine = Swine::create([
            'farm_id' => $origin->getKey(),
            'farmer_id' => $originFarmer->getKey(),
            'current_location_id' => $originLocation->getKey(),
            'tag_number' => 'MOBILE-MOVE-01',
            'sex' => 'male',
            'status' => 'active',
        ]);
        $token = $user->createToken('android')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/mobile/data/movements', [
                'swine_id' => $swine->getKey(),
                'to_farm_id' => $destination->getKey(),
                'to_location_id' => $destinationLocation->getKey(),
                'to_farmer_id' => $destinationFarmer->getKey(),
                'movement_date' => now()->toDateTimeString(),
                'reason' => 'Transfer',
            ])
            ->assertCreated()
            ->assertJsonPath('data.to_location_id', $destinationLocation->getKey());

        $this->assertDatabaseHas('swine', [
            'id' => $swine->getKey(),
            'farm_id' => $destination->getKey(),
            'farmer_id' => $destinationFarmer->getKey(),
            'current_location_id' => $destinationLocation->getKey(),
        ]);
    }

    public function test_mobile_admin_can_manage_core_records_and_swine_sale(): void
    {
        $user = $this->userWithPermissions(
            'manage-farms', 'manage-farmers', 'manage-locations', 'manage-users',
            'manage-swine', 'record-weight',
        );
        $token = $user->createToken('android')->plainTextToken;

        $farmId = $this->withToken($token)->postJson('/api/mobile/data/farms', [
            'farm_code' => 'ADM-01', 'name' => 'Admin Farm', 'latitude' => 10.9, 'longitude' => 123.4,
        ])->assertCreated()->json('data.id');

        $this->withToken($token)->putJson("/api/mobile/data/farms/{$farmId}", [
            'farm_code' => 'ADM-01', 'name' => 'Renamed Farm',
        ])->assertOk()->assertJsonPath('data.name', 'Renamed Farm');

        $farmerId = $this->withToken($token)->postJson('/api/mobile/data/farmers', [
            'farm_id' => $farmId, 'name' => 'F', 'contact_number' => '0917', 'address' => 'A',
        ])->assertCreated()->json('data.id');

        $locationId = $this->withToken($token)->postJson('/api/mobile/data/locations', [
            'farm_id' => $farmId, 'location_code' => 'PEN-1', 'name' => 'Pen 1',
        ])->assertCreated()->json('data.id');

        $swine = Swine::create([
            'farm_id' => $farmId, 'farmer_id' => $farmerId, 'tag_number' => 'SALE-1',
            'sex' => 'male', 'status' => 'active',
        ]);

        $payload = [
            'farm_id' => $farmId, 'farmer_id' => $farmerId, 'tag_number' => 'SALE-1',
            'sex' => 'male', 'current_location_id' => $locationId,
        ];

        $this->withToken($token)->putJson("/api/mobile/data/swine/{$swine->getKey()}", $payload + ['is_for_sale' => true])
            ->assertStatus(422);

        $this->withToken($token)->putJson("/api/mobile/data/swine/{$swine->getKey()}", $payload + [
            'is_for_sale' => true, 'asking_price' => 5000,
        ])->assertOk();
        $this->assertDatabaseHas('swine', ['id' => $swine->getKey(), 'is_for_sale' => true, 'asking_price' => 5000]);

        $this->withToken($token)->deleteJson("/api/mobile/data/swine/{$swine->getKey()}")->assertOk();
        $this->assertDatabaseHas('swine', ['id' => $swine->getKey(), 'status' => 'inactive', 'is_for_sale' => false]);

        $role = Role::first();
        $this->withToken($token)->postJson('/api/mobile/data/users', [
            'name' => 'New User', 'email' => 'new@example.com', 'password' => 'password123',
            'role_id' => $role->getKey(), 'status' => 'active',
        ])->assertCreated();
        $this->assertNotNull(User::where('email', 'new@example.com')->first()->email_verified_at);
    }

    public function test_retried_write_with_same_idempotency_key_creates_one_record(): void
    {
        $user = $this->userWithPermissions('manage-farms');
        $token = $user->createToken('android')->plainTextToken;
        $payload = ['farm_code' => 'IDEM-1', 'name' => 'Idempotent Farm'];

        $first = $this->withToken($token)->withHeader('Idempotency-Key', 'abc-123')
            ->postJson('/api/mobile/data/farms', $payload)->assertCreated();
        $second = $this->withToken($token)->withHeader('Idempotency-Key', 'abc-123')
            ->postJson('/api/mobile/data/farms', $payload)->assertCreated();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertSame(1, Farm::where('farm_code', 'IDEM-1')->count());
    }

    public function test_traceability_and_growth_lists_load(): void
    {
        $user = $this->userWithPermissions('view-traceability', 'record-weight');
        $farm = Farm::create(['farm_code' => 'TG-1', 'name' => 'TG Farm', 'status' => 'active']);
        $farmer = $this->createFarmer($farm, 'TG Farmer');
        $swine = Swine::create([
            'farm_id' => $farm->getKey(), 'farmer_id' => $farmer->getKey(),
            'tag_number' => 'TG-PIG', 'sex' => 'male', 'status' => 'active',
        ]);
        $token = $user->createToken('android')->plainTextToken;

        foreach (['traceability', 'growth'] as $resource) {
            $this->withToken($token)->getJson("/api/mobile/data/{$resource}?page=1")
                ->assertOk()->assertJsonPath('data.0.tag_number', 'TG-PIG');
            $this->withToken($token)->getJson("/api/mobile/data/{$resource}/{$swine->getKey()}")->assertOk();
        }
    }

    public function test_mobile_management_requires_permission(): void
    {
        $token = $this->userWithPermissions('record-weight')->createToken('android')->plainTextToken;

        $this->withToken($token)->postJson('/api/mobile/data/farms', ['farm_code' => 'X', 'name' => 'X'])
            ->assertForbidden();
        $this->withToken($token)->deleteJson('/api/mobile/data/farms/1')->assertForbidden();
    }

    private function userWithPermissions(string ...$slugs): User
    {
        $role = Role::create([
            'name' => 'Mobile Test Role',
            'slug' => 'mobile-test-'.bin2hex(random_bytes(4)),
        ]);

        $permissions = collect($slugs)->map(fn (string $slug) => Permission::create([
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
        ]));

        $role->permissions()->attach($permissions->pluck('id')->all());

        return User::factory()->create(['role_id' => $role->getKey()]);
    }

    private function createFarmer(Farm $farm, string $name): Farmer
    {
        return Farmer::create([
            'farm_id' => $farm->getKey(),
            'name' => $name,
            'contact_number' => '09170000000',
            'address' => 'Farm address',
        ]);
    }
}
