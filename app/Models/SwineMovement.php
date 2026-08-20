<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwineMovement extends Model
{
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

    public function swine(): BelongsTo
    {
        return $this->belongsTo(Swine::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(
            FarmLocation::class,
            'from_location_id'
        );
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(
            FarmLocation::class,
            'to_location_id'
        );
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'recorded_by'
        );
    }
}