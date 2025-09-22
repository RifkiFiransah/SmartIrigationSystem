<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Models\SensorData;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDevice extends CreateRecord
{
    protected static string $resource = DeviceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $data = $this->data; // form state
        if (!($data['init_sensor_enable'] ?? false)) {
            return; // user not request initial sensor row
        }
        try {
            SensorData::create(array_filter([
                'device_id' => $this->record->id, // assumes FK named device_id (adjust if different)
                'ground_temperature_c' => $data['init_ground_temperature_c'] ?? null,
                'soil_moisture_pct' => $data['init_soil_moisture_pct'] ?? null,
                'irrigation_usage_total_l' => $data['init_irrigation_usage_total_l'] ?? null,
                'battery_voltage_v' => $data['init_battery_voltage_v'] ?? null,
                'ina226_power_mw' => $data['init_ina226_power_mw'] ?? null,
                'recorded_at' => now(),
            ], fn($v)=>$v!==null));
        } catch (\Throwable $e) {
            // swallow or log if needed: \Log::warning('Init sensor create failed: '.$e->getMessage());
        }
    }
}
