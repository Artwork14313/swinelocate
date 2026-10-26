<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmLocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farm_id',
        'location_code',
        'name',
        'type',
        'capacity',
        'description',
        'status',
    ];

    protected $with = ['farm'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function getNameWithAddressAttribute(): string
    {
        $farm = $this->farm;
        $address = collect([$farm?->address, $farm?->municipality, $farm?->province])
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->unique()
            ->implode(', ');

        return $address === '' ? $this->name : "{$this->name} — {$address}";
    }

    public function swine(): HasMany
    {
        return $this->hasMany(
            Swine::class,
            'current_location_id'
        );
    }
}
