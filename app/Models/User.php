<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * User belongs to a role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * User belongs to a farm.
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'farm_id',
        'phone',
        'status',
    ];

    /**
     * Check if the user has a specific role.
     *
     * Example:
     * $user->hasRole('administrator')
     */
    public function hasRole(string $role): bool
    {
        return $this->role?->slug === $role;
    }

    /**
     * Check if the user has any of the given roles.
     *
     * Example:
     * $user->hasAnyRole([
     *     'administrator',
     *     'farm-manager',
     * ])
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->role
            && in_array($this->role->slug, $roles, true);
    }

    /**
     * Check if the user's role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role
            ? $this->role
                ->permissions()
                ->where('slug', $permission)
                ->exists()
            : false;
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdministrator(): bool
    {
        return $this->hasRole('administrator');
    }

    /**
     * Check if the user is a farm manager.
     */
    public function isFarmManager(): bool
    {
        return $this->hasRole('farm-manager');
    }

    /**
     * Check if the user is a veterinarian.
     */
    public function isVeterinarian(): bool
    {
        return $this->hasRole('veterinarian');
    }

    /**
     * Check if the user is farm staff.
     */
    public function isFarmStaff(): bool
    {
        return $this->hasRole('farm-staff');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}