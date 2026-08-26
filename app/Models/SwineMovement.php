<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwineMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'swine_id',
        'from_location_id',
        'to_location_id',
        'movement_date',
        'reason',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Swine
    |--------------------------------------------------------------------------
    */

    public function swine(): BelongsTo
    {
        return $this->belongsTo(Swine::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Previous Location
    |--------------------------------------------------------------------------
    */

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(
            FarmLocation::class,
            'from_location_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | New Location
    |--------------------------------------------------------------------------
    */

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(
            FarmLocation::class,
            'to_location_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Recorded By
    |--------------------------------------------------------------------------
    */

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by'
        );
    }
}