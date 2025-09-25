<?php

namespace Database\Seeders;

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
        $this->command->info('Creating sensor data for the past 24 hours...');
        
        // Get active device IDs
        $deviceIds = DB::table('devices')->where('is_active', true)->pluck('id')->toArray();
        
        if (empty($deviceIds)) {
            $this->command->warn('No active devices found. Skipping sensor data seeding.');
            return;
        }

        $sensorData = [];
        $now = Carbon::now();
        
        foreach ($deviceIds as $deviceId) {
            // Generate 6 readings (4-hour intervals) for the past 24 hours
            for ($h = 20; $h >= 0; $h -= 4) {
                $timestamp = $now->copy()->subHours($h)->startOfHour()->addMinutes(rand(0, 59));
                $hour = (int)$timestamp->format('G');
                
                // Generate realistic sensor values based on device ID and time
                $temperature = $this->getTemperatureByDevice($deviceId, $hour);
                $soilMoisture = $this->getSoilMoistureByDevice($deviceId, $hour);
                $waterHeight = $this->getWaterHeightByDevice($deviceId);
                $lightIntensity = $this->getLightByHour($hour);
                $batteryVoltage = round(4.20 - (($hour / 24) * rand(5, 15) / 100), 2);
                $irrigationUsage = round(rand(50, 150) / 10, 3);
                $status = $this->getSimpleStatus($temperature, $soilMoisture);
                
                $sensorData[] = [
                    'device_id' => $deviceId,
                    'temperature' => $temperature,
                    'humidity' => rand(45, 75),
                    'soil_moisture' => $soilMoisture,
                    'water_flow' => null,
                    'light_intensity' => $lightIntensity,
                    'ground_temperature_c' => $temperature,
                    'soil_moisture_pct' => $soilMoisture,
                    'water_height_cm' => $waterHeight,
                    'irrigation_usage_total_l' => $irrigationUsage,
                    'battery_voltage_v' => $batteryVoltage,
                    'ph_level' => $this->getPhByDevice($deviceId),
                    'nitrogen_level' => $this->getNitrogenByDevice($deviceId),
                    'phosphorus_level' => $this->getPhosphorusByDevice($deviceId),
                    'potassium_level' => $this->getPotassiumByDevice($deviceId),
                    'ina226_power_mw' => $this->getPowerByDevice($deviceId),
                    'ina226_current_ma' => $this->getCurrentByDevice($deviceId),
                    'ina226_voltage_v' => $this->getVoltageByDevice($deviceId),
                    
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
    private function getLightByHour(int $hour): ?int
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

    /**
     * Get pH level by device
     */
    private function getPhByDevice(int $deviceId): float
    {
        $basePh = 6.5 + ($deviceId % 4) * 0.3; // 6.5-7.7 range
        return round($basePh + rand(-10, 10) / 100, 2);
    }

    /**
     * Get nitrogen level by device
     */
    private function getNitrogenByDevice(int $deviceId): float
    {
        $baseNitrogen = 100 + ($deviceId % 5) * 20; // 100-180 mg/kg
        return round($baseNitrogen + rand(-20, 20), 1);
    }

    /**
     * Get phosphorus level by device
     */
    private function getPhosphorusByDevice(int $deviceId): float
    {
        $basePhosphorus = 30 + ($deviceId % 4) * 10; // 30-60 mg/kg
        return round($basePhosphorus + rand(-10, 10), 1);
    }

    /**
     * Get potassium level by device
     */
    private function getPotassiumByDevice(int $deviceId): float
    {
        $basePotassium = 200 + ($deviceId % 6) * 30; // 200-350 mg/kg
        return round($basePotassium + rand(-30, 30), 1);
    }

    /**
     * Get power consumption by device (INA226 sensor)
     */
    private function getPowerByDevice(int $deviceId): ?float
    {
        // Only certain devices have INA226 power sensors
        return match ($deviceId) {
            7 => round(rand(50, 150) + rand(0, 10) / 10, 3), // Node 7 has power measurement
            10 => round(rand(200, 500) + rand(0, 50) / 10, 3), // Pump has higher power consumption
            default => null, // Other devices don't have INA226
        };
    }

    /**
     * Get current measurement by device (INA226 sensor)
     */
    private function getCurrentByDevice(int $deviceId): ?float
    {
        // Only devices with INA226 have current measurement
        return match ($deviceId) {
            7 => round(rand(20, 50) + rand(0, 10) / 10, 3), // Node 7
            10 => round(rand(100, 200) + rand(0, 20) / 10, 3), // Pump
            default => null,
        };
    }

    /**
     * Get voltage measurement by device (INA226 sensor)
     */
    private function getVoltageByDevice(int $deviceId): ?float
    {
        // Only devices with INA226 have voltage measurement
        return match ($deviceId) {
            7 => round(3.3 + rand(-5, 5) / 100, 3), // Node 7 - 3.3V supply
            10 => round(12.0 + rand(-10, 10) / 100, 3), // Pump - 12V supply
            default => null,
        };
    }
}