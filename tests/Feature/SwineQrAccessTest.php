<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwineQrAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_swine_qr_routes_require_authentication(): void
    {
        foreach (['swine.scan', 'swine.scan.alt'] as $routeName) {
            $this->get(route($routeName, ['qr_token' => str_repeat('x', 48)]))
                ->assertRedirect(route('login'));
        }
    }

    public function test_swine_qr_routes_require_scan_permission(): void
    {
        $this->actingAs($this->userWithPermission('view-reports'));

        foreach (['swine.scan', 'swine.scan.alt'] as $routeName) {
            $this->get(route($routeName, ['qr_token' => str_repeat('x', 48)]))
                ->assertForbidden();
        }
    }

    public function test_user_with_scan_permission_can_reach_swine_qr_lookup(): void
    {
        $this->actingAs($this->userWithPermission('scan-qr'));

        $this->get(route('swine.scan', ['qr_token' => str_repeat('x', 48)]))
            ->assertNotFound();
    }

    public function test_qr_scanner_accepts_swine_and_farmer_qr_codes(): void
    {
        $this->actingAs($this->userWithPermission('scan-qr'));

        $this->get(route('qr.scanner'))
            ->assertOk()
            ->assertSee('public/farmers', false)
            ->assertSee('qr/scan', false)
            ->assertSee('swine or farmer QR code');
    }

    private function userWithPermission(string $permissionSlug): User
    {
        $role = Role::create([
            'name' => 'Swine QR Test Role',
            'slug' => 'swine-qr-test-'.bin2hex(random_bytes(4)),
        ]);
        $permission = Permission::firstOrCreate(
            ['slug' => $permissionSlug],
            ['name' => ucfirst(str_replace('-', ' ', $permissionSlug))]
        );
        $role->permissions()->attach($permission->id);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
