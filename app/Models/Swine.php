<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $farm_id
 * @property int|null $farmer_id
 * @property int|null $current_location_id
 * @property string $status
 * @property string $qr_token
 */
class Swine extends Model
{
    use SoftDeletes;

    protected $table = 'swine';

    protected $fillable = [
        'farm_id',
        'farmer_id',
        'local_id',
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
        'is_for_sale',
        'asking_price',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'acquisition_date' => 'date',
        'is_for_sale' => 'boolean',
        'asking_price' => 'decimal:2',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(
            FarmLocation::class,
            'current_location_id'
        );
    }

    public function movements(): HasMany
    {
        return $this->hasMany(
            SwineMovement::class
        )->latest('movement_date');
    }

    public function sale(): HasOne
    {
        return $this->hasOne(SwineSale::class);
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function weightRecords(): HasMany
    {
        return $this->hasMany(WeightRecord::class);
    }

    public function latestWeightRecord(): HasOne
    {
        return $this->hasOne(WeightRecord::class)->latestOfMany('record_date');
    }

    protected static function booted(): void
    {
        static::creating(function (Swine $swine) {
            if (empty($swine->qr_token)) {
                $swine->qr_token = 'SWL-'.strtoupper(
                    Str::random(12)
                );
            }
        });
    }
}
