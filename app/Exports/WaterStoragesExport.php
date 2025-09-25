<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WaterStoragesExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $storages) {}

    public function collection(): Collection
    {
        return $this->storages->map(function ($s) {
            return [
                $s->id,
                $s->tank_name,
                $s->device_id,
                $s->capacity_liters,
                $s->current_volume_liters,
                $s->status,
                $s->max_daily_usage,
                $s->height_cm,
                $s->last_height_cm,
                $s->zone_name,
                $s->area_size_sqm,
            ];
        });
    }

    public function headings(): array
    {
        return ['ID','Tank Name','Device ID','Capacity (L)','Current Volume (L)','Status','Max Daily Usage','Height (cm)','Last Height (cm)','Zone','Area Size (sqm)'];
    }
}
