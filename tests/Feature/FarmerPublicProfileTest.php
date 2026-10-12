<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Farmer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Swine;
use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_only_sees_active_swine_listed_for_sale_without_private_identifiers(): void
    {
        $farm = $this->createFarm('F-PUBLIC-1');
        $farmer = $this->createFarmer($farm);

        Swine::create([
            'farm_id' => $farm->id,
            'farmer_id' => $farmer->id,
            'tag_number' => 'SW-PUBLIC-101',
            'name' => 'Sample Pig',
            'sex' => 'female',
            'breed' => 'Large White',
            'birth_date' => '2026-01-10',
            'status' => 'active',
            'is_for_sale' => true,
            'asking_price' => 15000,
            'notes' => 'Private internal note',
        ]);
        $listedSwine = Swine::where('tag_number', 'SW-PUBLIC-101')->firstOrFail();
        WeightRecord::create([
            'swine_id' => $listedSwine->id,
            'record_date' => '2026-10-01',
            'weight' => 82.5,
        ]);
        Swine::create([
            'farm_id' => $farm->id,
            'farmer_id' => $farmer->id,
            'tag_number' => 'SW-PUBLIC-102',
            'sex' => 'male',
            'status' => 'sold',
            'is_for_sale' => true,
            'asking_price' => 12000,
        ]);
        Swine::create([
            'farm_id' => $farm->id,
            'farmer_id' => $farmer->id,
            'tag_number' => 'SW-PUBLIC-103',
            'sex' => 'female',
            'status' => 'active',
        ]);

        $this->get(route('farmers.public-profile', $farmer->qr_token))
            ->assertOk()
            ->assertSee('Public Farmer')
            ->assertSee('Public Farm')
            ->assertSee('Sample Municipality')
            ->assertSee('Large White')
            ->assertSee('Female')
            ->assertSee('months old')
            ->assertSee('82.50 kg')
            ->assertSee('₱15,000.00')
            ->assertDontSee('SW-PUBLIC-101')
            ->assertDontSee('SW-PUBLIC-102')
            ->assertDontSee('SW-PUBLIC-103')
            ->assertDontSee('Sample Pig')
            ->assertDontSee('Jan 10, 2026')
            ->assertDontSee('Sold')
            ->assertDontSee('09171234567')
            ->assertDontSee('private@example.test')
            ->assertDontSee('Private home address')
            ->assertDontSee('Private internal note');
    }

    public function test_unknown_public_farmer_token_returns_not_found(): void
    {
        $this->get(route('farmers.public-profile', str_repeat('x', 48)))
            ->assertNotFound();
    }

    public function test_authorized_farmer_page_displays_printable_public_qr(): void
    {
        $role = Role::create([
            'name' => 'Farmer Manager',
            'slug' => 'farmer-manager-public-'.bin2hex(random_bytes(4)),
        ]);
        $permission = Permission::firstOrCreate(
            ['slug' => 'manage-farmers'],
            ['name' => 'Manage Farmers']
        );
        $role->permissions()->attach($permission->id);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $farmer = $this->createFarmer($this->createFarm('F-PUBLIC-2'));

        $this->get(route('farmers.show', $farmer))
            ->assertOk()
            ->assertSee('Public Farmer QR Code')
            ->assertSee('Print Farmer QR')
            ->assertSee(route('farmers.public-profile', $farmer->qr_token), false);
    }

    private function createFarm(string $code): Farm
    {
        return Farm::create([
            'farm_code' => $code,
            'name' => 'Public Farm',
            'address' => 'Private farm street address',
            'municipality' => 'Sample Municipality',
            'province' => 'Sample Province',
            'region' => 'Sample Region',
        ]);
    }

    private function createFarmer(Farm $farm): Farmer
    {
        return Farmer::create([
            'farm_id' => $farm->id,
            'name' => 'Public Farmer',
            'contact_number' => '09171234567',
            'email' => 'private@example.test',
            'address' => 'Private home address',
        ]);
    }
}
