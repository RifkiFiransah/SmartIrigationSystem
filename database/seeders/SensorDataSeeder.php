<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil device_id dari tabel devices yang aktif
        $deviceIds = DB::table('devices')->where('is_active', true)->pluck('id')->toArray();
        $sensorData = [];
        $statuses = ['normal', 'alert', 'critical'];

        // Generate data untuk 30 hari terakhir
        for ($day = 29; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            
            foreach ($deviceIds as $deviceId) {
                // Generate 6 data per hari (setiap 4 jam)
                for ($hour = 0; $hour < 24; $hour += 4) {
                    $timestamp = $date->copy()->addHours($hour);
                    
                    // Simulate realistic sensor readings
                    $temperature = rand(18, 40) + (rand(0, 99) / 100); // 18-40°C
                    $humidity = rand(30, 95) + (rand(0, 99) / 100);    // 30-95%
                    $soilMoisture = rand(20, 80) + (rand(0, 99) / 100); // 20-80%
                    $waterFlow = rand(0, 500) + (rand(0, 99) / 100);   // 0-500 L/h
                    
                    // Determine status based on sensor values
                    $status = $this->determineStatus($temperature, $humidity, $soilMoisture);
                    
                    $sensorData[] = [
                        'device_id' => $deviceId,
                        'temperature' => $temperature,
                        'humidity' => $humidity,
                        'soil_moisture' => $soilMoisture,
                        'water_flow' => $waterFlow,
                        'recorded_at' => $timestamp,
                        'status' => $status,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }
        }

        // Batch insert untuk performa yang lebih baik
        $chunks = array_chunk($sensorData, 100);
        foreach ($chunks as $chunk) {
            DB::table('sensor_data')->insert($chunk);
        }
    }

    /**
     * Determine status based on sensor readings
     */
    private function determineStatus($temperature, $humidity, $soilMoisture): string
    {
        // Critical conditions
        if ($temperature > 38 || $temperature < 5 || $soilMoisture < 15) {
            return 'kritis';
        }
        
        // Alert conditions
        if ($temperature > 35 || $temperature < 10 || $soilMoisture < 25 || $humidity > 90) {
            return 'peringatan';
        }
        
        // Normal conditions
        return 'normal';
    }
}
