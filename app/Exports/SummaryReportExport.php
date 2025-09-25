<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SummaryReportExport implements FromCollection, WithHeadings
{
    public function __construct(private array $rows) {}

    public function collection(): Collection
    {
        return collect($this->rows)->map(function ($r) {
            return [
                $r['tanggal'] ?? null,
                $r['device_name'] ?? null,
                $r['records_count'] ?? 0,
                $r['ground_temp_avg'] ?? null,
                $r['ground_temp_min'] ?? null,
                $r['ground_temp_max'] ?? null,
                $r['soil_moisture_avg'] ?? null,
                $r['soil_moisture_min'] ?? null,
                $r['soil_moisture_max'] ?? null,
                $r['water_height_avg'] ?? null,
                $r['battery_voltage_avg'] ?? null,
                $r['battery_voltage_min'] ?? null,
                $r['irrigation_usage_delta_l'] ?? null,
                $r['water_usage_log_sum_l'] ?? null,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal','Device','Records','Temp Avg','Temp Min','Temp Max','Soil Avg','Soil Min','Soil Max','Water Height Avg','Battery V Avg','Battery V Min','Irrigation Delta (L)','Water Usage Log (L)'
        ];
    }
}
