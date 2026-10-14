<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class EnsureSuperadmin extends Command
{
    protected $signature = 'swinelocate:ensure-superadmin';

    protected $description = 'Create or restore a protected SwineLocate superadmin account';

    public function handle(): int
    {
        $permissions = Permission::query()->pluck('id');

        if ($permissions->isEmpty()) {
            $this->error('No permissions are configured. Run php artisan db:seed --class=RolePermissionSeeder first.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Superadmin name'));
        $email = Str::lower(trim((string) $this->ask('Superadmin email')));

        $identityValidator = Validator::make(
            ['name' => $name, 'email' => $email],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
            ]
        );

        if ($identityValidator->fails()) {
            foreach ($identityValidator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::withTrashed()->where('email', $email)->first();

        if ($user && !$user->isSuperadmin()) {
            $this->error('That email belongs to an account that is not a superadmin. Choose a separate email address.');

            return self::FAILURE;
        }

        if (
            $user &&
            !$this->confirm("Restore or reset the protected superadmin account for {$email}?", false)
        ) {
            $this->info('No changes made.');

            return self::SUCCESS;
        }

        $password = $this->secret('Superadmin password (at least 12 characters)');
        $passwordConfirmation = $this->secret('Confirm superadmin password');

        $passwordValidator = Validator::make(
            ['password' => $password, 'password_confirmation' => $passwordConfirmation],
            [
                'password' => ['required', 'confirmed', Password::min(12)],
            ]
        );

        if ($passwordValidator->fails()) {
            foreach ($passwordValidator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($name, $email, $password, $permissions, &$user) {
            $role = Role::query()->firstOrCreate(
                ['slug' => 'superadmin'],
                [
                    'name' => 'Superadmin',
                    'description' => 'Protected break-glass account with full system access',
                ]
            );
            $role->permissions()->sync($permissions);

            $user ??= new User();

            if ($user->trashed()) {
                $user->restore();
            }

            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
                'role_id' => $role->getKey(),
                'farm_id' => null,
                'status' => 'active',
            ])->save();
        });

        $this->info("Superadmin account is ready for {$email}.");

        return self::SUCCESS;
    }
}
