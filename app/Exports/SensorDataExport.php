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
                $r->id,
                $r->device_name,
                $r->recorded_at,
                $r->ground_temperature_c ?? 'N/A',
                $r->soil_moisture_pct ?? 'N/A',
                $r->water_height_cm ?? 'N/A',
                $r->battery_voltage_v ?? 'N/A',
                $r->irrigation_usage_total_l ?? 'N/A',
                $r->status ?? 'normal',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sensor ID',
            'Device Name', 
            'Recorded At',
            'Ground Temp (°C)',
            'Soil Moisture (%)',
            'Water Height (cm)',
            'Battery Voltage (V)',
            'Irrigation Usage (L)',
            'Status'
        ];
    }
}
