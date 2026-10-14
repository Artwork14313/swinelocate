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
            ->assertSee(route('farmers.nearby'))
            ->assertDontSee('Open navigation menu')
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

    public function test_authenticated_visitors_see_navigation_on_public_farmer_pages(): void
    {
        $role = Role::create([
            'name' => 'Public Page Test Role',
            'slug' => 'public-page-test-'.bin2hex(random_bytes(4)),
        ]);
        $permission = Permission::firstOrCreate(
            ['slug' => 'manage-farmers'],
            ['name' => 'Manage Farmers']
        );
        $role->permissions()->attach($permission->id);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $farmer = $this->createFarmer($this->createFarm('F-PUBLIC-AUTH'));

        $this->get(route('farmers.public-profile', $farmer->qr_token))
            ->assertOk()
            ->assertSee('Open navigation menu')
            ->assertSee('Dashboard');

        $this->get(route('farmers.nearby'))
            ->assertOk()
            ->assertSee('Open navigation menu')
            ->assertSee('Dashboard');
    }

    public function test_buyer_can_find_available_farmers_sorted_by_distance_without_scanning_their_qr_codes(): void
    {
        $nearFarm = $this->createFarm('F-NEARBY-1', 14.5995, 120.9842);
        $nearFarmer = $this->createFarmer($nearFarm, 'Nearby Farmer');
        $this->createSaleListing($nearFarm, $nearFarmer, 'Large White');

        $farFarm = $this->createFarm('F-NEARBY-2', 15.5995, 120.9842);
        $farFarmer = $this->createFarmer($farFarm, 'Farther Farmer');
        $this->createSaleListing($farFarm, $farFarmer, 'Duroc');

        $unlistedFarm = $this->createFarm('F-NEARBY-3', 14.6000, 120.9842);
        $unlistedFarmer = $this->createFarmer($unlistedFarm, 'Unlisted Farmer');
        Swine::create([
            'farm_id' => $unlistedFarm->id,
            'farmer_id' => $unlistedFarmer->id,
            'tag_number' => 'SW-NEARBY-UNLISTED',
            'sex' => 'female',
            'status' => 'active',
            'is_for_sale' => false,
        ]);

        $html = $this->post(route('farmers.nearby.search'), [
            'latitude' => 14.5995,
            'longitude' => 120.9842,
        ])
            ->assertOk()
            ->assertSee('Find swine nearby')
            ->assertSee('About 0 km away')
            ->assertSee('Large White')
            ->assertSee('Duroc')
            ->assertSee(route('farmers.public-profile', $nearFarmer->qr_token), false)
            ->assertDontSee('Unlisted Farmer')
            ->assertDontSee('Private farm street address')
            ->getContent();

        $this->assertLessThan(
            strpos($html, 'Farther Farmer'),
            strpos($html, 'Nearby Farmer')
        );
    }

    public function test_buyer_can_search_listings_by_municipality_without_sharing_location(): void
    {
        $matchingFarm = $this->createFarm('F-NEARBY-TOWN');
        $matchingFarmer = $this->createFarmer($matchingFarm, 'Town Farmer');
        $this->createSaleListing($matchingFarm, $matchingFarmer, 'Landrace');

        $otherFarm = Farm::create([
            'farm_code' => 'F-NEARBY-OTHER',
            'name' => 'Other Town Farm',
            'municipality' => 'Other Municipality',
            'province' => 'Sample Province',
            'status' => 'active',
        ]);
        $otherFarmer = $this->createFarmer($otherFarm, 'Other Town Farmer');
        $this->createSaleListing($otherFarm, $otherFarmer, 'Duroc');

        $this->post(route('farmers.nearby.search'), [
            'municipality' => 'Sample Municipality',
        ])
            ->assertOk()
            ->assertSee('Town Farmer')
            ->assertSee('Landrace')
            ->assertDontSee('Other Town Farmer');
    }

    public function test_nearby_search_requires_a_location_or_town(): void
    {
        $this->from(route('farmers.nearby'))
            ->post(route('farmers.nearby.search'))
            ->assertRedirect(route('farmers.nearby'))
            ->assertSessionHasErrors('municipality');
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

    private function createFarm(string $code, ?float $latitude = null, ?float $longitude = null): Farm
    {
        return Farm::create([
            'farm_code' => $code,
            'name' => 'Public Farm',
            'address' => 'Private farm street address',
            'municipality' => 'Sample Municipality',
            'province' => 'Sample Province',
            'region' => 'Sample Region',
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    private function createFarmer(Farm $farm, string $name = 'Public Farmer'): Farmer
    {
        return Farmer::create([
            'farm_id' => $farm->id,
            'name' => $name,
            'contact_number' => '09171234567',
            'email' => 'private@example.test',
            'address' => 'Private home address',
        ]);
    }

    private function createSaleListing(Farm $farm, Farmer $farmer, string $breed): Swine
    {
        return Swine::create([
            'farm_id' => $farm->id,
            'farmer_id' => $farmer->id,
            'tag_number' => 'SW-NEARBY-'.strtoupper(str_replace(' ', '-', $breed)),
            'sex' => 'female',
            'breed' => $breed,
            'status' => 'active',
            'is_for_sale' => true,
            'asking_price' => 15000,
        ]);
    }
}
