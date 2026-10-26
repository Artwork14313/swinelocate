<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwineSale extends Model
{
    protected $fillable = [
        'swine_id',
        'buyer_name',
        'buyer_contact',
        'sale_price',
        'sold_at',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'sold_at' => 'date',
    ];

    public function swine(): BelongsTo
    {
        return $this->belongsTo(Swine::class)->withTrashed();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
