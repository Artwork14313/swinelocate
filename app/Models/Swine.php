<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Farm;
use App\Models\FarmLocation;
use App\Models\SwineMovement;

class Swine extends Model
{
    use SoftDeletes;

    protected $table = 'swine';

    protected $fillable = [
        'farm_id',
        'current_location_id',
        'tag_number',
        'qr_token',
        'name',
        'sex',
        'breed',
        'birth_date',
        'acquisition_date',
        'source',
        'status',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'acquisition_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(
            FarmLocation::class,
            'current_location_id'
        );
    }

    protected static function booted(): void
    {
        static::creating(function (Swine $swine) {
            if (empty($swine->qr_token)) {
                $swine->qr_token = 'SWL-' . strtoupper(
                    str()->random(12)
                );
            }
        });
    }

    public function movements(): HasMany
    {
        return $this->hasMany(
            SwineMovement::class
        )->latest('movement_date');
    }
    
}