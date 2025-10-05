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
                $s->device_id ?? 'N/A',
                $s->zone_name ?? 'N/A',
                $s->capacity_liters ?? 0,
                $s->current_volume_liters ?? 0,
                $s->percentage ?? 0,
                $s->status ?? 'unknown',
                $s->area_name ?? 'N/A',
                $s->area_size_sqm ?? 0,
                $s->plant_types ?? 'N/A',
                $s->height_cm ?? 0,
                $s->last_height_cm ?? 0,
                $s->max_daily_usage ?? 0,
                $s->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tank Name',
            'Device ID',
            'Zone Name',
            'Capacity (L)',
            'Current Volume (L)',
            'Percentage (%)',
            'Status',
            'Area Name',
            'Area Size (sqm)',
            'Plant Types',
            'Height (cm)',
            'Last Height (cm)',
            'Max Daily Usage (L)',
            'Created At'
        ];
    }
}
