<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'water_storage_id',
        'usage_date',
        'volume_used_l',
        'source',
        'meta',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'volume_used_l' => 'decimal:2',
        'meta' => 'array',
    ];

    public function waterStorage(): BelongsTo
    {
        return $this->belongsTo(WaterStorage::class);
    }
}
