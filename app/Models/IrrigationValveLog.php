<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationValveLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'irrigation_valve_id',
        'node_uid',
        'action',      // open|close|toggle_mode
        'trigger',     // manual|auto|schedule
        'duration_seconds',
        'notes',
    ];

    public function valve(): BelongsTo
    {
        return $this->belongsTo(IrrigationValve::class, 'irrigation_valve_id');
    }
}
