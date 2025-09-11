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
        $this->command->info('Creating minimal sensor data (today only)...');
        
        // Get active device IDs
        $deviceIds = DB::table('devices')->where('is_active', true)->pluck('id')->toArray();
        
        if (empty($deviceIds)) {
            $this->command->warn('No active devices found. Creating sample device...');
            DB::table('devices')->insert([
                'device_name' => 'Sample IoT Node',
                'device_type' => 'sensor',
                'location' => 'Greenhouse A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $deviceIds = [DB::getPdo()->lastInsertId()];
        }

        $sensorData = [];
        
        // Generate minimal data - just today with 3 readings only
        $today = Carbon::now();
        
        foreach ($deviceIds as $deviceId) {
            // Generate only 3 readings for today (morning, noon, evening)
            $hours = [8, 14, 20]; // 8 AM, 2 PM, 8 PM
            
            foreach ($hours as $hour) {
                $timestamp = $today->copy()->hour($hour)->minute(rand(0, 59));
                
                // Simple realistic sensor values based on device ID for variety
                $temperature_c = $this->getTemperatureByDevice($deviceId, $hour);
                $soil_moisture_pct = $this->getSoilMoistureByDevice($deviceId, $hour);
                $water_height_cm = $this->getWaterHeightByDevice($deviceId); 
                $water_volume_l = ($water_height_cm / 100) * 200; // Based on height
                $light_lux = $this->getLightByDevice($deviceId, $hour); // Realistic day/night cycle
                $wind_speed_ms = rand(1, 6) + (rand(0, 99) / 100); // 1-6 m/s
                
                // INA226 power monitoring (realistic power consumption)
                $ina226_bus_voltage_v = 5.0 + (rand(-5, 5) / 100); // 4.95-5.05V
                $ina226_current_ma = 150 + rand(-30, 50); // 120-200mA
                $ina226_power_mw = $ina226_bus_voltage_v * $ina226_current_ma;
                
                // Determine status
                $status = $this->getSimpleStatus($temperature_c, $soil_moisture_pct);
                
                $sensorData[] = [
                    'device_id' => $deviceId,
                    // Legacy fields (keep for compatibility)
                    'temperature' => $temperature_c,
                    'humidity' => rand(45, 75), // Legacy field
                    'soil_moisture' => $soil_moisture_pct,
                    'water_flow' => $water_volume_l / 10, // Legacy conversion
                    'light_intensity' => $light_lux / 1000, // Legacy field
                    
                    // New sensor fields
                    'temperature_c' => $temperature_c,
                    'soil_moisture_pct' => $soil_moisture_pct,
                    'water_height_cm' => $water_height_cm,
                    'water_volume_l' => round($water_volume_l, 2),
                    'light_lux' => $light_lux,
                    'wind_speed_ms' => round($wind_speed_ms, 2),
                    
                    // INA226 fields
                    'ina226_bus_voltage_v' => round($ina226_bus_voltage_v, 3),
                    'ina226_shunt_voltage_mv' => rand(-3, 3), // Small shunt voltage
                    'ina226_current_ma' => round($ina226_current_ma, 3),
                    'ina226_power_mw' => round($ina226_power_mw, 3),
                    
                    // Timing fields
                    'device_ts' => $timestamp,
                    'device_ts_unix' => $timestamp->timestamp,
                    'recorded_at' => $timestamp,
                    'status' => $status,
                    'flags' => json_encode(['quality' => 'good', 'calibrated' => true]),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        // Insert data in chunks
        $chunks = array_chunk($sensorData, 50);
        foreach ($chunks as $chunk) {
            DB::table('sensor_data')->insert($chunk);
        }
        
        $this->command->info('Created ' . count($sensorData) . ' minimal sensor records for ' . count($deviceIds) . ' devices (today only)');
    }

    /**
     * Get realistic light values based on hour (day/night cycle)
     */
    private function getLightByHour(int $hour): int
    {
        return match (true) {
            $hour >= 6 && $hour <= 8 => rand(10000, 30000), // Morning
            $hour >= 9 && $hour <= 15 => rand(40000, 80000), // Day
            $hour >= 16 && $hour <= 18 => rand(20000, 40000), // Evening
            default => rand(0, 1000), // Night
        };
    }

    /**
     * Get temperature by device for variety
     */
    private function getTemperatureByDevice(int $deviceId, int $hour): float
    {
        $baseTemp = match ($deviceId) {
            1 => 26.27, // Node 1 - from screenshot
            2 => 52.00, // Node 2 - higher temp (greenhouse)
            3 => 35.20, // Node 3 - realistic outdoor high
            4 => 28.50, // Node 4 - normal range
            5 => 3.49, // Node 5 - wind sensor/low temp
            6 => 43.00, // Node 6 - water level
            7 => 28.90, // Node 7 - normal sensor (not power reading)
            8 => 25 + rand(-2, 5), // Node 8 - normal
            default => 25 + rand(-3, 8),
        };
        return round($baseTemp + (rand(-1, 1) * 0.1), 2);
    }

    /**
     * Get soil moisture by device for variety
     */
    private function getSoilMoistureByDevice(int $deviceId, int $hour): int
    {
        return match ($deviceId) {
            1 => rand(50, 55), // Normal range
            2 => rand(50, 55), // Normal range  
            3 => rand(83, 88), // High moisture
            4 => rand(35, 40), // Medium (not special reading for moisture)
            5 => rand(2, 5), // Very low
            6 => rand(40, 45), // Medium
            default => rand(35, 75),
        };
    }

    /**
     * Get water height by device
     */
    private function getWaterHeightByDevice(int $deviceId): int
    {
        return match ($deviceId) {
            1 => rand(35, 45), // Low-medium
            2 => rand(40, 50), // Medium
            3 => rand(80, 90), // High
            4 => rand(25, 35), // Low
            5 => rand(1, 5), // Very low
            6 => rand(40, 50), // Medium
            7 => rand(45, 55), // Medium-high
            8 => rand(30, 40), // Medium
            default => rand(35, 60),
        };
    }

    /**
     * Get light by device and hour
     */
    private function getLightByDevice(int $deviceId, int $hour): int
    {
        $baseLux = $this->getLightByHour($hour);
        
        return match ($deviceId) {
            1 => (int)($baseLux * 0.3), // Indoor/shaded
            2 => (int)($baseLux * 0.1), // Very low light
            3 => (int)($baseLux * 1.2), // High light
            4 => (int)($baseLux * 0.4), // Medium light
            5 => (int)($baseLux * 0.05), // Very low/night
            6 => (int)($baseLux * 0.8), // Good light
            7 => (int)($baseLux * 0.6), // Medium light
            8 => (int)($baseLux * 1.0), // Normal
            default => $baseLux,
        };
    }

    /**
     * Simple status determination
     */
    private function getSimpleStatus(float $temperature, int $soilMoisture): string
    {
        if ($temperature > 35 || $soilMoisture < 25) {
            return 'kritis';
        }
        
        if ($temperature > 32 || $soilMoisture < 35) {
            return 'peringatan';
        }
        
        return 'normal';
    }
}