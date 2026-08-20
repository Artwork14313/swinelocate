<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Full system access',
            ],
            [
                'name' => 'Farm Manager',
                'slug' => 'farm-manager',
                'description' => 'Manages farm and swine operations',
            ],
            [
                'name' => 'Veterinarian',
                'slug' => 'veterinarian',
                'description' => 'Manages swine health and veterinary records',
            ],
            [
                'name' => 'Farm Staff',
                'slug' => 'farm-staff',
                'description' => 'Performs farm-level recording and scanning',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            [
                'name' => 'Manage Users',
                'slug' => 'manage-users',
                'description' => 'Create, update, and manage users',
            ],
            [
                'name' => 'Manage Roles',
                'slug' => 'manage-roles',
                'description' => 'Manage system roles and permissions',
            ],
            [
                'name' => 'Manage Farms',
                'slug' => 'manage-farms',
                'description' => 'Create and manage farms',
            ],
            [
                'name' => 'Manage Locations',
                'slug' => 'manage-locations',
                'description' => 'Manage farm locations and pens',
            ],
            [
                'name' => 'Register Swine',
                'slug' => 'register-swine',
                'description' => 'Register new swine',
            ],
            [
                'name' => 'Manage Swine',
                'slug' => 'manage-swine',
                'description' => 'Manage swine profiles',
            ],
            [
                'name' => 'Manage Health Records',
                'slug' => 'manage-health',
                'description' => 'Manage veterinary and health records',
            ],
            [
                'name' => 'Manage Vaccinations',
                'slug' => 'manage-vaccinations',
                'description' => 'Manage vaccination records',
            ],
            [
                'name' => 'Record Weight',
                'slug' => 'record-weight',
                'description' => 'Record swine weight',
            ],
            [
                'name' => 'Manage Movements',
                'slug' => 'manage-movements',
                'description' => 'Record swine movements',
            ],
            [
                'name' => 'Generate QR Codes',
                'slug' => 'generate-qr',
                'description' => 'Generate QR codes for swine',
            ],
            [
                'name' => 'Scan QR Codes',
                'slug' => 'scan-qr',
                'description' => 'Scan swine QR codes',
            ],
            [
                'name' => 'View Traceability',
                'slug' => 'view-traceability',
                'description' => 'View swine traceability information',
            ],
            [
                'name' => 'View Reports',
                'slug' => 'view-reports',
                'description' => 'View system reports',
            ],
            [
                'name' => 'Manage Backups',
                'slug' => 'manage-backups',
                'description' => 'Manage system backups',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        $administrator = Role::where(
            'slug',
            'administrator'
        )->firstOrFail();

        $administrator->permissions()->sync(
            Permission::pluck('id')
        );

        /*
        |--------------------------------------------------------------------------
        | Farm Manager
        |--------------------------------------------------------------------------
        */

        $farmManager = Role::where(
            'slug',
            'farm-manager'
        )->firstOrFail();

        $farmManager->permissions()->sync(
            Permission::whereIn('slug', [
                'manage-farms',
                'manage-locations',
                'register-swine',
                'manage-swine',
                'record-weight',
                'manage-movements',
                'generate-qr',
                'scan-qr',
                'view-traceability',
                'view-reports',
            ])->pluck('id')
        );

        /*
        |--------------------------------------------------------------------------
        | Veterinarian
        |--------------------------------------------------------------------------
        */

        $veterinarian = Role::where(
            'slug',
            'veterinarian'
        )->firstOrFail();

        $veterinarian->permissions()->sync(
            Permission::whereIn('slug', [
                'manage-swine',
                'manage-health',
                'manage-vaccinations',
                'view-traceability',
                'view-reports',
            ])->pluck('id')
        );

        /*
        |--------------------------------------------------------------------------
        | Farm Staff
        |--------------------------------------------------------------------------
        */

        $farmStaff = Role::where(
            'slug',
            'farm-staff'
        )->firstOrFail();

        $farmStaff->permissions()->sync(
            Permission::whereIn('slug', [
                'scan-qr',
                'record-weight',
                'manage-movements',
                'view-traceability',
            ])->pluck('id')
        );
    }
}