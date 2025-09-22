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
    $this->command->info('Creating 24h hourly sensor data (past 24 hours)...');
        
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
    // Track cumulative irrigation usage per device (liters)
    $irrigationTotals = [];
        
    // Generate full 24 hours (hourly) including current hour going backwards
    $now = Carbon::now();
        
        foreach ($deviceIds as $deviceId) {
            // Generate 24 readings (one per hour) from oldest to newest for smoother charts
            for ($h = 23; $h >= 0; $h--) {
                $timestamp = $now->copy()->subHours($h)->minute(0)->second(0)->addMinutes(rand(0,5));
                $timestampHour = (int)$timestamp->format('G');
                
                // Simple realistic sensor values based on device ID for variety
                $groundTemp = $this->getTemperatureByDevice($deviceId, $timestampHour);
                $soil_moisture_pct = $this->getSoilMoistureByDevice($deviceId, $timestampHour);
                $water_height_cm = $this->getWaterHeightByDevice($deviceId); 

                // Cumulative irrigation usage (simulate increment only when moisture low)
                if (!array_key_exists($deviceId, $irrigationTotals)) {
                    $irrigationTotals[$deviceId] = rand(50, 150) / 10; // initial 5.0 - 15.0 L
                }
                $increment = ($soil_moisture_pct < 40) ? (rand(5, 25) / 100) : (rand(0, 5) / 100); // 0.00 - 0.25 L
                $irrigationTotals[$deviceId] = round($irrigationTotals[$deviceId] + $increment, 3);

                // Battery: 3.7V - 4.2V typical Li-Ion with slight drain pattern by hour
                $battery_voltage_v = round(4.20 - (($timestampHour / 24) * rand(5,15) / 100), 2); // simplistic
                
                // Determine status
                $status = $this->getSimpleStatus($groundTemp, $soil_moisture_pct);
                
                $sensorData[] = [
                    'device_id' => $deviceId,
                    // Legacy fields (keep for compatibility)
                    'temperature' => $groundTemp, // legacy field kept
                    'humidity' => rand(45, 75), // Legacy field
                    'soil_moisture' => $soil_moisture_pct,
                    'water_flow' => null, // Not used now
                    'light_intensity' => null,

                    // Device-centric new schema fields
                    'ground_temperature_c' => $groundTemp,
                    'soil_moisture_pct' => $soil_moisture_pct,
                    'water_height_cm' => $water_height_cm,
                    'irrigation_usage_total_l' => $irrigationTotals[$deviceId],
                    'battery_voltage_v' => $battery_voltage_v,
                    
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
        
    $this->command->info('Created ' . count($sensorData) . ' hourly sensor records for ' . count($deviceIds) . ' devices (last 24h)');
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
            9 => 27.5 + rand(-1,2)/2,  // Reservoir monitor
            10 => 30 + rand(-2,3)/2,   // Pompa area (sedikit lebih hangat)
            11 => 26 + rand(-2,4)/2,   // Bed Percobaan A
            12 => 26.5 + rand(-2,4)/2, // Bed Percobaan B
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
            7 => rand(45, 55),
            8 => rand(40, 60),
            9 => rand(60, 70), // reservoir edge soil
            10 => rand(30, 45), // near pump dryer
            11 => rand(55, 65), // experimental bed wetter
            12 => rand(50, 60), // experimental bed
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
            9 => rand(55, 65), // Reservoir deeper
            10 => rand(20, 30), // Pump intake trench
            11 => rand(35, 45), // Experimental A
            12 => rand(35, 45), // Experimental B
            default => rand(35, 60),
        };
    }

    /**
     * Get light by device and hour
     */
    // Removed getLightByDevice (global sensor moved / not per device)

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