<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Swine;

class FarmLocation extends Model
{
    protected $fillable = [
        'farm_id',
        'location_code',
        'name',
        'type',
        'capacity',
        'description',
        'status',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function swine(): HasMany
    {
        return $this->hasMany(
            Swine::class,
            'current_location_id'
        );
    }

}