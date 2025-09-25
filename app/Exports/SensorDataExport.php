<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SensorDataExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $sensorRows) {}

    public function collection(): Collection
    {
        return $this->sensorRows->map(function ($r) {
            return [
                $r->device_id,
                $r->recorded_at,
                $r->ground_temperature_c,
                $r->soil_moisture_pct,
                $r->water_height_cm,
                $r->battery_voltage_v,
                $r->irrigation_usage_total_l,
            ];
        });
    }

    public function headings(): array
    {
        return ['Device ID','Recorded At','Ground Temp (C)','Soil Moisture (%)','Water Height (cm)','Battery Voltage (V)','Irrigation Usage Total (L)'];
    }
}
