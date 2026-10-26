<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Farmer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Swine;
use App\Models\SwineSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwineSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_swine_sold_records_buyer_details_and_shows_sale_history(): void
    {
        $user = $this->userWithManageSwinePermission();
        $this->actingAs($user);
        $swine = $this->createActiveSwine();

        $this->get(route('swine.index'))
            ->assertOk()
            ->assertSee('Record swine sale')
            ->assertSee('Buyer contact number')
            ->assertSee(route('sale-history.index'));

        $this->patch(route('swine.sold', $swine), [
            'buyer_name' => 'Jordan Buyer',
            'buyer_contact' => '09171234567',
            'sale_price' => '12500.00',
            'sold_at' => '2026-10-25',
            'notes' => 'Paid in full',
        ])->assertRedirect(route('sale-history.index'));

        $this->assertSame('sold', $swine->fresh()->status);
        $this->assertFalse($swine->fresh()->is_for_sale);
        $this->assertDatabaseHas('swine_sales', [
            'swine_id' => $swine->id,
            'buyer_name' => 'Jordan Buyer',
            'buyer_contact' => '09171234567',
            'recorded_by' => $user->id,
        ]);
        $sale = SwineSale::query()->where('swine_id', $swine->id)->firstOrFail();
        $this->assertSame(12500.0, (float) $sale->sale_price);
        $this->assertSame('2026-10-25', $sale->sold_at->toDateString());

        $this->get(route('sale-history.index'))
            ->assertOk()
            ->assertSee('Jordan Buyer')
            ->assertSee('09171234567')
            ->assertSee('SW-SALE-1')
            ->assertSee('₱12,500.00')
            ->assertSee('Paid in full');
    }

    public function test_swine_cannot_be_marked_sold_without_buyer_details(): void
    {
        $this->actingAs($this->userWithManageSwinePermission());
        $swine = $this->createActiveSwine();

        $this->from(route('swine.index'))
            ->patch(route('swine.sold', $swine), [
                'buyer_name' => '',
                'buyer_contact' => '',
                'sale_price' => '',
                'sold_at' => '',
            ])
            ->assertRedirect(route('swine.index'))
            ->assertSessionHasErrors(['buyer_name', 'buyer_contact', 'sale_price', 'sold_at']);

        $this->assertSame('active', $swine->fresh()->status);
        $this->assertDatabaseCount('swine_sales', 0);
    }

    public function test_farmer_pages_show_sold_swine_and_total_income(): void
    {
        $role = Role::create(['name' => 'Farmer Sale Role', 'slug' => 'farmer-sale-'.bin2hex(random_bytes(4))]);
        $role->permissions()->attach(
            Permission::create(['name' => 'Manage Farmers', 'slug' => 'manage-farmers'])
        );
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $farm = Farm::create(['farm_code' => 'F-INC-1', 'name' => 'Income Farm']);
        $farmer = Farmer::create([
            'farm_id' => $farm->id,
            'name' => 'Income Farmer',
            'contact_number' => '09170000000',
            'address' => 'Somewhere',
        ]);
        $otherFarmer = Farmer::create([
            'farm_id' => $farm->id,
            'name' => 'Other Farmer',
            'contact_number' => '09170000001',
            'address' => 'Elsewhere',
        ]);

        foreach ([['SW-INC-1', $farmer, 10000], ['SW-INC-2', $farmer, 2500.50], ['SW-OTHER', $otherFarmer, 999]] as [$tag, $owner, $price]) {
            $swine = Swine::create([
                'farm_id' => $farm->id,
                'farmer_id' => $owner->id,
                'tag_number' => $tag,
                'sex' => 'male',
                'status' => 'sold',
            ]);
            SwineSale::create([
                'swine_id' => $swine->id,
                'buyer_name' => 'Buyer '.$tag,
                'buyer_contact' => '09180000000',
                'sale_price' => $price,
                'sold_at' => '2026-10-20',
            ]);
        }

        $this->get(route('farmers.show', $farmer))
            ->assertOk()
            ->assertSee('Sold Swine')
            ->assertSee('Buyer SW-INC-1')
            ->assertSee('Buyer SW-INC-2')
            ->assertDontSee('Buyer SW-OTHER')
            ->assertSee('₱12,500.50');

        $this->get(route('farmers.index'))
            ->assertOk()
            ->assertSee('₱12,500.50')
            ->assertSee('₱999.00');
    }

    private function userWithManageSwinePermission(): User
    {
        $role = Role::create([
            'name' => 'Sale Test Role',
            'slug' => 'sale-test-'.bin2hex(random_bytes(4)),
        ]);
        $permission = Permission::create([
            'name' => 'Manage Swine',
            'slug' => 'manage-swine',
        ]);
        $role->permissions()->attach($permission);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createActiveSwine(): Swine
    {
        $farm = Farm::create([
            'farm_code' => 'F-SALE-1',
            'name' => 'Sale Test Farm',
        ]);

        return Swine::create([
            'farm_id' => $farm->id,
            'tag_number' => 'SW-SALE-1',
            'sex' => 'female',
            'status' => 'active',
            'is_for_sale' => true,
            'asking_price' => 15000,
        ]);
    }
}
