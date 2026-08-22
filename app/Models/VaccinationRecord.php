<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccinationRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'swine_id',
        'recorded_by',
        'vaccine_name',
        'vaccine_type',
        'date_administered',
        'next_due_date',
        'dosage',
        'administration_route',
        'notes',
    ];

    protected $casts = [
        'date_administered' => 'date',
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