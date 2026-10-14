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

        $name = $this->valueFromEnvironmentOrPrompt(
            'SWINELOCATE_SUPERADMIN_NAME',
            'Superadmin name'
        );
        $email = $this->valueFromEnvironmentOrPrompt(
            'SWINELOCATE_SUPERADMIN_EMAIL',
            'Superadmin email'
        );

        if ($name === null || $email === null) {
            $this->error(
                'This shell cannot prompt for input. Set SWINELOCATE_SUPERADMIN_NAME and SWINELOCATE_SUPERADMIN_EMAIL, then rerun with --no-interaction.'
            );

            return self::FAILURE;
        }

        $name = trim($name);
        $email = Str::lower(trim($email));

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

        if ($user) {
            $confirmedReset = $this->input->isInteractive()
                ? $this->confirm("Restore or reset the protected superadmin account for {$email}?", false)
                : getenv('SWINELOCATE_SUPERADMIN_CONFIRM_RESET') === '1';

            if (!$confirmedReset) {
                $this->error(
                    'A superadmin already exists for that email. Confirm the reset interactively or set SWINELOCATE_SUPERADMIN_CONFIRM_RESET=1.'
                );

                return self::FAILURE;
            }
        }

        $password = $this->valueFromEnvironmentOrSecretPrompt(
            'SWINELOCATE_SUPERADMIN_PASSWORD',
            'Superadmin password (at least 12 characters)'
        );
        $passwordConfirmation = $this->valueFromEnvironmentOrSecretPrompt(
            'SWINELOCATE_SUPERADMIN_PASSWORD_CONFIRM',
            'Confirm superadmin password'
        );

        if ($password === null || $passwordConfirmation === null) {
            $this->error(
                'This shell cannot prompt for input. Set SWINELOCATE_SUPERADMIN_PASSWORD and SWINELOCATE_SUPERADMIN_PASSWORD_CONFIRM, then rerun with --no-interaction.'
            );

            return self::FAILURE;
        }

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

    private function valueFromEnvironmentOrPrompt(string $key, string $prompt): ?string
    {
        $value = getenv($key);

        if ($value !== false) {
            return $value;
        }

        return $this->input->isInteractive()
            ? $this->ask($prompt)
            : null;
    }

    private function valueFromEnvironmentOrSecretPrompt(string $key, string $prompt): ?string
    {
        $value = getenv($key);

        if ($value !== false) {
            return $value;
        }

        return $this->input->isInteractive()
            ? $this->secret($prompt)
            : null;
    }
}
