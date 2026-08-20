<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Swine extends Model
{
    use SoftDeletes;

    protected $table = 'swine';

    protected $fillable = [
        'farm_id',
        'current_location_id',
        'tag_number',
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
}