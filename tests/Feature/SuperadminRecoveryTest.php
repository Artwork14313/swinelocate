<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_recovery_command_creates_a_verified_active_superadmin_with_all_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->artisan('swinelocate:ensure-superadmin')
            ->expectsQuestion('Superadmin name', 'Recovery Account')
            ->expectsQuestion('Superadmin email', 'recovery@example.test')
            ->expectsQuestion('Superadmin password (at least 12 characters)', 'a-very-long-test-password')
            ->expectsQuestion('Confirm superadmin password', 'a-very-long-test-password')
            ->expectsOutput('Superadmin account is ready for recovery@example.test.')
            ->assertExitCode(0);

        $superadmin = User::where('email', 'recovery@example.test')->firstOrFail();

        $this->assertSame('active', $superadmin->status);
        $this->assertNotNull($superadmin->email_verified_at);
        $this->assertTrue($superadmin->isSuperadmin());
        $this->assertTrue($superadmin->hasPermission('manage-users'));
        $this->assertSame(Permission::count(), $superadmin->role->permissions()->count());

        $this->post(route('login'), [
            'email' => 'recovery@example.test',
            'password' => 'a-very-long-test-password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_recovery_command_accepts_secret_environment_variables_without_interactive_prompts(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $environment = [
            'SWINELOCATE_SUPERADMIN_NAME' => 'Cloud Recovery',
            'SWINELOCATE_SUPERADMIN_EMAIL' => 'cloud-recovery@example.test',
            'SWINELOCATE_SUPERADMIN_PASSWORD' => 'a-very-long-cloud-password',
            'SWINELOCATE_SUPERADMIN_PASSWORD_CONFIRM' => 'a-very-long-cloud-password',
        ];

        foreach ($environment as $key => $value) {
            putenv("{$key}={$value}");
        }

        try {
            $this->artisan('swinelocate:ensure-superadmin', ['--no-interaction' => true])
                ->expectsOutput('Superadmin account is ready for cloud-recovery@example.test.')
                ->assertExitCode(0);
        } finally {
            foreach (array_keys($environment) as $key) {
                putenv($key);
            }
        }

        $superadmin = User::where('email', 'cloud-recovery@example.test')->firstOrFail();
        $this->assertTrue($superadmin->isSuperadmin());
        $this->assertSame('active', $superadmin->status);
    }

    public function test_superadmin_cannot_be_deleted_deactivated_or_demoted_from_user_management(): void
    {
        $managerRole = Role::create([
            'name' => 'User Manager',
            'slug' => 'user-manager-superadmin-test',
        ]);
        $manageUsers = Permission::create([
            'name' => 'Manage Users',
            'slug' => 'manage-users',
        ]);
        $manageRoles = Permission::create([
            'name' => 'Manage Roles',
            'slug' => 'manage-roles',
        ]);
        $managerRole->permissions()->attach([$manageUsers->id, $manageRoles->id]);

        $superadminRole = Role::create([
            'name' => 'Superadmin',
            'slug' => 'superadmin',
        ]);
        $superadminRole->permissions()->attach($manageUsers);

        $manager = User::factory()->create([
            'role_id' => $managerRole->id,
        ]);
        $superadmin = User::factory()->create([
            'role_id' => $superadminRole->id,
        ]);

        $this->actingAs($manager);

        $this->get(route('users.index'))
            ->assertOk()
            ->assertDontSee($superadmin->email)
            ->assertDontSee($superadmin->name);
        $this->get(route('users.show', $superadmin))
            ->assertNotFound();

        $this->delete(route('users.destroy', $superadmin))
            ->assertRedirect(route('users.index'));
        $this->patch(route('users.toggle-status', $superadmin))
            ->assertRedirect(route('users.index'));
        $this->put(route('users.update', $superadmin), [
            'name' => $superadmin->name,
            'email' => $superadmin->email,
            'role_id' => $managerRole->id,
            'status' => 'active',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('users.index'));
        $this->get(route('users.edit', $superadmin))
            ->assertNotFound();
        $this->put(route('roles.update', $superadminRole), [
            'permissions' => [],
        ])->assertRedirect(route('roles.show', $superadminRole));

        $this->assertDatabaseHas('users', [
            'id' => $superadmin->id,
            'role_id' => $superadminRole->id,
            'status' => 'active',
            'deleted_at' => null,
        ]);
        $this->assertTrue($superadmin->fresh()->hasPermission('manage-users'));

        $this->actingAs($superadmin)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $superadmin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_superadmin_permission_is_hidden_and_cannot_be_assigned_to_other_roles(): void
    {
        $manageRoles = Permission::create([
            'name' => 'Manage Roles',
            'slug' => 'manage-roles',
        ]);
        $superadminPermission = Permission::create([
            'name' => 'Superadmin Access',
            'slug' => 'superadmin',
        ]);
        $regularPermission = Permission::create([
            'name' => 'View Reports',
            'slug' => 'view-reports',
        ]);

        $managerRole = Role::create([
            'name' => 'Role Manager',
            'slug' => 'role-manager-superadmin-permission-test',
        ]);
        $managerRole->permissions()->attach($manageRoles);

        $otherRole = Role::create([
            'name' => 'Other Role',
            'slug' => 'other-role-superadmin-permission-test',
        ]);
        $otherRole->permissions()->attach([
            $superadminPermission->id,
            $regularPermission->id,
        ]);

        $this->actingAs(User::factory()->create([
            'role_id' => $managerRole->id,
        ]));

        $this->get(route('roles.edit', $otherRole))
            ->assertOk()
            ->assertSee('View Reports')
            ->assertDontSee('Superadmin Access');

        $this->get(route('roles.show', $otherRole))
            ->assertOk()
            ->assertSee('View Reports')
            ->assertDontSee('Superadmin Access');

        $this->put(route('roles.update', $otherRole), [
            'permissions' => [$superadminPermission->id],
        ])->assertSessionHasErrors('permissions.0');
    }

    public function test_superadmin_role_is_not_available_in_regular_user_creation(): void
    {
        $superadminRole = Role::create([
            'name' => 'Superadmin',
            'slug' => 'superadmin',
        ]);
        $manageUsers = Permission::create([
            'name' => 'Manage Users',
            'slug' => 'manage-users',
        ]);
        $managerRole = Role::create([
            'name' => 'User Manager',
            'slug' => 'user-manager-superadmin-create-test',
        ]);
        $managerRole->permissions()->attach($manageUsers);

        $this->actingAs(User::factory()->create([
            'role_id' => $managerRole->id,
        ]));

        $this->get(route('users.create'))
            ->assertOk()
            ->assertDontSee('value="'.$superadminRole->id.'"', false);

        $this->post(route('users.store'), [
            'name' => 'Escalation Attempt',
            'email' => 'escalation@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $superadminRole->id,
            'status' => 'active',
        ])->assertSessionHasErrors('role_id');
    }
}
