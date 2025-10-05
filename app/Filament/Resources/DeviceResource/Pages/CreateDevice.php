<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Models\SensorData;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

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
        
        // Log untuk debugging
        Log::info('CreateDevice afterCreate called', [
            'device_id' => $this->record->id,
            'init_sensor_enable' => $data['init_sensor_enable'] ?? 'not set',
            'data' => $data
        ]);
        
        if (!($data['init_sensor_enable'] ?? false)) {
            Log::info('Init sensor not enabled, skipping');
            return; // user not request initial sensor row
        }
        
        try {
            $sensorData = [
                'device_id' => $this->record->id,
                'ground_temperature_c' => $data['init_ground_temperature_c'] ?? null,
                'soil_moisture_pct' => $data['init_soil_moisture_pct'] ?? null,
                'irrigation_usage_total_l' => $data['init_irrigation_usage_total_l'] ?? null,
                'battery_voltage_v' => $data['init_battery_voltage_v'] ?? null,
                'ina226_power_mw' => $data['init_ina226_power_mw'] ?? null,
                'recorded_at' => now(),
            ];
            
            // Filter only non-null values except device_id and recorded_at
            $filteredData = array_filter($sensorData, function($v, $k) {
                return $v !== null || in_array($k, ['device_id', 'recorded_at']);
            }, ARRAY_FILTER_USE_BOTH);
            
            Log::info('Creating initial sensor data', ['data' => $filteredData]);
            
            $sensor = SensorData::create($filteredData);
            
            Log::info('Initial sensor data created successfully', ['sensor_id' => $sensor->id]);
            
            Notification::make()
                ->success()
                ->title('Data sensor awal berhasil dibuat')
                ->send();
                
        } catch (\Throwable $e) {
            Log::error('Init sensor create failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            Notification::make()
                ->danger()
                ->title('Gagal membuat data sensor awal')
                ->body($e->getMessage())
                ->send();
        }
    }
}
