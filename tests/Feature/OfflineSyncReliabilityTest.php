<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineSyncReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_retried_offline_health_sync_does_not_create_duplicates(): void
    {
        $this->actingAs($this->userWithPermissions('manage-health'));
        $farm = $this->createFarm('HEALTH-01');
        $swine = $this->createSwine($farm, 'SW-HEALTH-01');
        $payload = [
            'local_id' => 'b34632a7-d4f1-4a2a-9ce2-6590ad6e93b2',
            'swine_id' => $swine->id,
            'record_date' => now()->toDateString(),
            'record_type' => 'Routine Examination',
            'health_status' => 'healthy',
        ];

        $this->postJson(route('health-records.sync'), $payload)
            ->assertCreated()
            ->assertJsonPath('already_synced', false);

        $this->postJson(route('health-records.sync'), $payload)
            ->assertOk()
            ->assertJsonPath('already_synced', true);

        $this->assertSame(1, HealthRecord::where('local_id', $payload['local_id'])->count());
    }

    public function test_health_record_edits_accept_json_for_offline_sync(): void
    {
        $this->actingAs($this->userWithPermissions('manage-health'));
        $farm = $this->createFarm('HEALTH-02');
        $swine = $this->createSwine($farm, 'SW-HEALTH-02');
        $record = HealthRecord::create([
            'swine_id' => $swine->id,
            'recorded_by' => auth()->id(),
            'record_date' => now()->subDay()->toDateString(),
            'record_type' => 'Routine Examination',
            'health_status' => 'healthy',
        ]);

        $this->get(route('health-records.edit', $record))
            ->assertOk()
            ->assertSee('id="health-record-edit-form"', false)
            ->assertSee('data-record-id="'.$record->id.'"', false);

        $this->putJson(route('health-records.update', $record), [
            'swine_id' => $swine->id,
            'record_date' => now()->toDateString(),
            'record_type' => 'Illness',
            'health_status' => 'under_observation',
            'symptoms' => 'Reduced appetite',
            'notes' => 'Updated while offline, then synced.',
        ])
            ->assertOk()
            ->assertJsonPath('health_record_id', $record->id)
            ->assertJsonPath('swine_id', $swine->id);

        $this->assertDatabaseHas('health_records', [
            'id' => $record->id,
            'record_type' => 'Illness',
            'health_status' => 'under_observation',
            'symptoms' => 'Reduced appetite',
        ]);
    }

    public function test_weight_record_edits_accept_json_for_offline_sync(): void
    {
        $this->actingAs($this->userWithPermissions('record-weight'));
        $farm = $this->createFarm('WEIGHT-01');
        $swine = $this->createSwine($farm, 'SW-WEIGHT-01');
        $record = WeightRecord::create([
            'swine_id' => $swine->id,
            'recorded_by' => auth()->id(),
            'record_date' => now()->subDay()->toDateString(),
            'weight' => 42.50,
            'notes' => 'Initial note',
        ]);

        $this->get(route('weight-records.edit', $record))
            ->assertOk()
            ->assertSee('id="weight-record-edit-form"', false)
            ->assertSee('data-record-id="'.$record->id.'"', false);

        $this->putJson(route('weight-records.update', $record), [
            'record_date' => now()->toDateString(),
            'weight' => 47.25,
            'notes' => 'Updated while offline, then synced.',
        ])
            ->assertOk()
            ->assertJsonPath('weight_record_id', $record->id);

        $this->assertDatabaseHas('weight_records', [
            'id' => $record->id,
            'weight' => 47.25,
            'notes' => 'Updated while offline, then synced.',
        ]);
    }

    public function test_offline_movement_can_transfer_swine_to_a_different_farm(): void
    {
        $this->actingAs($this->userWithPermissions('manage-movements'));
        $sourceFarm = $this->createFarm('MOVE-01');
        $destinationFarm = $this->createFarm('MOVE-02');
        $sourceLocation = $this->createLocation($sourceFarm, 'SOURCE-01');
        $destinationLocation = $this->createLocation($destinationFarm, 'DEST-01');
        $destinationFarmer = $this->createFarmer($destinationFarm, 'Destination Farmer');
        $swine = $this->createSwine($sourceFarm, 'SW-MOVE-01', $sourceLocation);

        $this->get(route('swine.movements.create', $swine))
            ->assertOk()
            ->assertSee('Destination Farm')
            ->assertSee('Destination Farmer');

        $this->postJson(route('swine-movements.sync'), [
            'swine_id' => $swine->id,
            'local_id' => 'offline-movement-1',
            'from_farm_id' => $sourceFarm->id,
            'original_farm_id' => $sourceFarm->id,
            'to_farm_id' => $destinationFarm->id,
            'to_farmer_id' => $destinationFarmer->id,
            'from_location_id' => $sourceLocation->id,
            'original_location_id' => $sourceLocation->id,
            'to_location_id' => $destinationLocation->id,
            'movement_date' => now()->toDateTimeString(),
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('swine', [
            'id' => $swine->id,
            'farm_id' => $destinationFarm->id,
            'farmer_id' => $destinationFarmer->id,
            'current_location_id' => $destinationLocation->id,
        ]);
        $this->assertDatabaseHas('swine_movements', [
            'swine_id' => $swine->id,
            'to_location_id' => $destinationLocation->id,
            'to_farmer_id' => $destinationFarmer->id,
        ]);
    }

    public function test_legacy_offline_movement_without_destination_farm_still_syncs(): void
    {
        $this->actingAs($this->userWithPermissions('manage-movements'));
        $farm = $this->createFarm('MOVE-08');
        $sourceLocation = $this->createLocation($farm, 'SOURCE-05');
        $destinationLocation = $this->createLocation($farm, 'DEST-04');
        $swine = $this->createSwine($farm, 'SW-MOVE-04', $sourceLocation);

        $this->postJson(route('swine-movements.sync'), [
            'swine_id' => $swine->id,
            'from_location_id' => $sourceLocation->id,
            'original_location_id' => $sourceLocation->id,
            'to_location_id' => $destinationLocation->id,
            'movement_date' => now()->toDateTimeString(),
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('swine', [
            'id' => $swine->id,
            'farm_id' => $farm->id,
            'current_location_id' => $destinationLocation->id,
        ]);
    }

    public function test_offline_movement_rejects_a_farmer_from_another_farm(): void
    {
        $this->actingAs($this->userWithPermissions('manage-movements'));
        $sourceFarm = $this->createFarm('MOVE-03');
        $destinationFarm = $this->createFarm('MOVE-04');
        $otherFarm = $this->createFarm('MOVE-05');
        $sourceLocation = $this->createLocation($sourceFarm, 'SOURCE-02');
        $destinationLocation = $this->createLocation($destinationFarm, 'DEST-02');
        $wrongFarmer = $this->createFarmer($otherFarm, 'Wrong Farm Farmer');
        $swine = $this->createSwine($sourceFarm, 'SW-MOVE-02', $sourceLocation);

        $this->postJson(route('swine-movements.sync'), [
            'swine_id' => $swine->id,
            'from_farm_id' => $sourceFarm->id,
            'to_farm_id' => $destinationFarm->id,
            'to_farmer_id' => $wrongFarmer->id,
            'from_location_id' => $sourceLocation->id,
            'original_location_id' => $sourceLocation->id,
            'to_location_id' => $destinationLocation->id,
            'movement_date' => now()->toDateTimeString(),
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'A farmer registered to the destination farm is required.');

        $this->assertDatabaseHas('swine', [
            'id' => $swine->id,
            'farm_id' => $sourceFarm->id,
            'current_location_id' => $sourceLocation->id,
        ]);
    }

    public function test_keep_offline_conflict_resolution_applies_cross_farm_assignment(): void
    {
        $this->actingAs($this->userWithPermissions('manage-movements'));
        $sourceFarm = $this->createFarm('MOVE-06');
        $destinationFarm = $this->createFarm('MOVE-07');
        $originalLocation = $this->createLocation($sourceFarm, 'SOURCE-03');
        $serverLocation = $this->createLocation($sourceFarm, 'SOURCE-04');
        $destinationLocation = $this->createLocation($destinationFarm, 'DEST-03');
        $destinationFarmer = $this->createFarmer($destinationFarm, 'Conflict Destination Farmer');
        $swine = $this->createSwine($sourceFarm, 'SW-MOVE-03', $serverLocation);
        $serverMovement = SwineMovement::create([
            'swine_id' => $swine->id,
            'from_location_id' => $originalLocation->id,
            'to_location_id' => $serverLocation->id,
            'movement_date' => now()->subMinute(),
        ]);

        $this->postJson(route('swine-movements.sync'), [
            'swine_id' => $swine->id,
            'local_id' => 'offline-movement-conflict-1',
            'from_farm_id' => $sourceFarm->id,
            'original_farm_id' => $sourceFarm->id,
            'to_farm_id' => $destinationFarm->id,
            'to_farmer_id' => $destinationFarmer->id,
            'from_location_id' => $originalLocation->id,
            'original_location_id' => $originalLocation->id,
            'to_location_id' => $destinationLocation->id,
            'movement_date' => now()->toDateTimeString(),
            'server_movement_id' => $serverMovement->id,
            'force' => true,
        ])
            ->assertOk()
            ->assertJsonPath('resolution', 'offline');

        $this->assertDatabaseHas('swine', [
            'id' => $swine->id,
            'farm_id' => $destinationFarm->id,
            'farmer_id' => $destinationFarmer->id,
            'current_location_id' => $destinationLocation->id,
        ]);
        $this->assertDatabaseHas('swine_movements', [
            'swine_id' => $swine->id,
            'to_location_id' => $destinationLocation->id,
            'to_farmer_id' => $destinationFarmer->id,
            'status' => 'completed',
            'conflict_resolution' => 'offline',
        ]);
    }

    private function createFarm(string $code): Farm
    {
        return Farm::create([
            'farm_code' => $code,
            'name' => "Farm {$code}",
        ]);
    }

    private function createLocation(Farm $farm, string $code): FarmLocation
    {
        return FarmLocation::create([
            'farm_id' => $farm->id,
            'location_code' => $code,
            'name' => "Location {$code}",
            'status' => 'active',
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

    private function createSwine(
        Farm $farm,
        string $tag,
        ?FarmLocation $location = null,
    ): Swine {
        return Swine::create([
            'farm_id' => $farm->id,
            'current_location_id' => $location?->id,
            'tag_number' => $tag,
            'sex' => 'female',
            'status' => 'active',
        ]);
    }

    private function userWithPermissions(string ...$permissionSlugs): User
    {
        $role = Role::create([
            'name' => 'Offline Sync Test Role',
            'slug' => 'offline-sync-test-'.bin2hex(random_bytes(4)),
        ]);
        $permissions = collect($permissionSlugs)->map(function ($slug) {
            return Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst(str_replace('-', ' ', $slug))],
            );
        });
        $role->permissions()->attach($permissions->pluck('id'));

        return User::factory()->create(['role_id' => $role->id]);
    }
}
