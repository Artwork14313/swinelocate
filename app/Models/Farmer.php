<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Farmer extends Model
{
    protected $fillable = [
        'farm_id',
        'name',
        'contact_number',
        'email',
        'address',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function swine(): HasMany
    {
        return $this->hasMany(Swine::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Farmer $farmer) {
            if (empty($farmer->qr_token)) {
                $farmer->qr_token = Str::random(48);
            }
        });
    }
}
