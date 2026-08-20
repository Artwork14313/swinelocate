<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Swine;


class Farm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farm_code',
        'name',
        'address',
        'municipality',
        'province',
        'region',
        'latitude',
        'longitude',
        'contact_number',
        'email',
        'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(FarmLocation::class);
    }

    public function swine(): HasMany
    {
        return $this->hasMany(Swine::class);
    }
}