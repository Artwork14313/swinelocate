<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserVerificationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_user_is_marked_verified_without_email_verification(): void
    {
        $role = Role::create([
            'name' => 'User Manager',
            'slug' => 'user-manager',
        ]);
        $permission = Permission::create([
            'name' => 'Manage Users',
            'slug' => 'manage-users',
        ]);
        $role->permissions()->attach($permission);
        $admin = User::factory()->create(['role_id' => $role->getKey()]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Farm Staff',
                'email' => 'staff@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => $role->getKey(),
                'status' => 'active',
            ])
            ->assertRedirect(route('users.index'));

        $staff = User::query()->where('email', 'staff@example.test')->firstOrFail();

        $this->assertNotNull($staff->email_verified_at);
        $this->assertTrue($staff->hasVerifiedEmail());
    }
}
