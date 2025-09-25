<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WaterUsageLogsExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $logs) {}

    public function collection(): Collection
    {
        return $this->logs->map(function ($l) {
            return [
                $l->device_id,
                $l->usage_date,
                $l->volume_used_l,
                $l->source,
            ];
        });
    }

    public function headings(): array
    {
        return ['Device ID','Usage Date','Volume Used (L)','Source'];
    }
}
