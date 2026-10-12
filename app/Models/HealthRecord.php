<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'swine_id',
        'local_id',
        'recorded_by',
        'record_date',
        'record_type',
        'vaccine_name',
        'dose',
        'batch_number',
        'next_due_date',
        'symptoms',
        'diagnosis',
        'treatment',
        'observations',
        'veterinary_assessment',
        'health_status',
        'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'next_due_date' => 'date',
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
