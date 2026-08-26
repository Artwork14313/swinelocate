<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'swine_id',
        'recorded_by',
        'record_date',
        'weight',
        'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'weight' => 'decimal:2',
    ];

    public function swine(): BelongsTo
    {
        return $this->belongsTo(Swine::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}