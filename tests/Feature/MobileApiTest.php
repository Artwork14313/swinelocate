<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_active_user_can_sign_in_and_revoke_the_mobile_token(): void
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

    public function test_mobile_login_rejects_inactive_and_unverified_users(): void
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
        ])->assertUnprocessable();
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
}
