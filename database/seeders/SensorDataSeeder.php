<?php

// namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB;
// use Carbon\Carbon;

// class SensorDataSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//         // Ambil device_id dari tabel devices yang aktif
//         $deviceIds = DB::table('devices')->where('is_active', true)->pluck('id')->toArray();
//         $sensorData = [];
//         $statuses = ['normal', 'alert', 'critical'];

//         // Generate data untuk 30 hari terakhir
//         for ($day = 29; $day >= 0; $day--) {
//             $date = Carbon::now()->subDays($day);
            
//             foreach ($deviceIds as $deviceId) {
//                 // Generate 6 data per hari (setiap 4 jam)
//                 for ($hour = 0; $hour < 24; $hour += 4) {
//                     $timestamp = $date->copy()->addHours($hour);
                    
//                     // Simulate realistic sensor readings
//                     $temperature = rand(18, 40) + (rand(0, 99) / 100); // 18-40°C
//                     $humidity = rand(30, 95) + (rand(0, 99) / 100);    // 30-95%
//                     $soilMoisture = rand(20, 80) + (rand(0, 99) / 100); // 20-80%
//                     $waterFlow = rand(0, 500) + (rand(0, 99) / 100);   // 0-500 L/h
                    
//                     // Determine status based on sensor values
//                     $status = $this->determineStatus($temperature, $humidity, $soilMoisture);
                    
//                     $sensorData[] = [
//                         'device_id' => $deviceId,
//                         'temperature' => $temperature,
//                         'humidity' => $humidity,
//                         'soil_moisture' => $soilMoisture,
//                         'water_flow' => $waterFlow,
//                         'recorded_at' => $timestamp,
//                         'status' => $status,
//                         'created_at' => $timestamp,
//                         'updated_at' => $timestamp,
//                     ];
//                 }
//             }
//         }

//         // Batch insert untuk performa yang lebih baik
//         $chunks = array_chunk($sensorData, 100);
//         foreach ($chunks as $chunk) {
//             DB::table('sensor_data')->insert($chunk);
//         }
//     }

//     /**
//      * Determine status based on sensor readings
//      */
//     private function determineStatus($temperature, $humidity, $soilMoisture): string
//     {
//         // Critical conditions
//         if ($temperature > 38 || $temperature < 5 || $soilMoisture < 15) {
//             return 'kritis';
//         }
        
//         // Alert conditions
//         if ($temperature > 35 || $temperature < 10 || $soilMoisture < 25 || $humidity > 90) {
//             return 'peringatan';
//         }
        
//         // Normal conditions
//         return 'normal';
//     }
// }

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
        $this->command->info('🌱 Starting sensor data seeding...');
        
        // Ambil device_id dari tabel devices yang aktif
        $deviceIds = DB::table('devices')->where('is_active', true)->pluck('id')->toArray();
        
        if (empty($deviceIds)) {
            $this->command->warn('⚠️  No active devices found. Please seed devices first.');
            return;
        }
        
        $this->command->info("📱 Found " . count($deviceIds) . " active devices");
        
        // Hapus data lama jika ada
        $this->cleanupOldData();
        
        $sensorData = [];
        $totalRecords = 0;
        
        // Generate data untuk 30 hari terakhir dengan variasi yang lebih realistis
        for ($day = 29; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            
            foreach ($deviceIds as $deviceId) {
                // Generate data setiap 2 jam untuk data yang lebih dense
                for ($hour = 0; $hour < 24; $hour += 2) {
                    $timestamp = $date->copy()->addHours($hour)->addMinutes(rand(0, 59));
                    
                    // Simulate realistic sensor readings dengan variasi harian
                    $sensorReadings = $this->generateRealisticReadings($timestamp, $deviceId);
                    
                    $sensorData[] = array_merge($sensorReadings, [
                        'device_id' => $deviceId,
                        'recorded_at' => $timestamp,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                    
                    $totalRecords++;
                }
            }
            
            // Progress indicator
            if (($day % 5) == 0) {
                $progress = round(((29 - $day) / 30) * 100);
                $this->command->info("📊 Progress: {$progress}% - Day " . (30 - $day) . "/30");
            }
        }
        
        $this->command->info("💾 Inserting {$totalRecords} sensor records...");
        
        // Batch insert untuk performa yang lebih baik
        $chunks = array_chunk($sensorData, 500); // Increased chunk size
        $progressBar = $this->command->getOutput()->createProgressBar(count($chunks));
        
        foreach ($chunks as $chunk) {
            DB::table('sensor_data')->insert($chunk);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("✅ Successfully seeded {$totalRecords} sensor data records!");
        
        // Display statistics
        $this->displayStatistics();
    }
    
    /**
     * Generate realistic sensor readings based on time patterns
     */
    private function generateRealisticReadings(Carbon $timestamp, int $deviceId): array
    {
        $hour = $timestamp->hour;
        $month = $timestamp->month;
        $isDay = $hour >= 6 && $hour <= 18;
        
        // Base temperature varies by time of day and season
        $baseTemp = $this->getSeasonalBaseTemp($month);
        $tempVariation = $isDay ? rand(5, 15) : rand(-5, 5);
        $temperature = round($baseTemp + $tempVariation + (rand(-200, 200) / 100), 2);
        
        // Humidity inversely related to temperature
        $baseHumidity = $isDay ? rand(40, 70) : rand(60, 85);
        $humidityAdjustment = ($temperature > 30) ? rand(-15, -5) : rand(0, 10);
        $humidity = round(max(20, min(95, $baseHumidity + $humidityAdjustment + (rand(-500, 500) / 100))), 2);
        
        // Soil moisture with seasonal patterns
        $baseSoilMoisture = $this->getSeasonalSoilMoisture($month);
        $soilVariation = rand(-10, 10) + (rand(-300, 300) / 100);
        $soilMoisture = round(max(10, min(90, $baseSoilMoisture + $soilVariation)), 2);
        
        // Water flow with realistic patterns (higher during watering times)
        $waterFlow = $this->generateWaterFlow($hour, $soilMoisture);
        
        // Light intensity (lux) - varies dramatically by time of day
        $lightIntensity = $this->generateLightIntensity($hour);
        
        // Determine status based on sensor readings
        $status = $this->determineAdvancedStatus($temperature, $humidity, $soilMoisture);
        
        return [
            'temperature' => $temperature,
            'humidity' => $humidity,
            'soil_moisture' => $soilMoisture,
            'water_flow' => $waterFlow,
            'light_intensity' => $lightIntensity,
            'status' => $status,
        ];
    }
    
    /**
     * Get seasonal base temperature
     */
    private function getSeasonalBaseTemp(int $month): float
    {
        $seasonalTemps = [
            1 => 22,  // Jan - Musim hujan
            2 => 23,  // Feb
            3 => 24,  // Mar
            4 => 26,  // Apr - Musim kering mulai
            5 => 28,  // May
            6 => 29,  // Jun
            7 => 28,  // Jul
            8 => 29,  // Aug
            9 => 30,  // Sep - Puncak kering
            10 => 28, // Oct
            11 => 26, // Nov - Mulai hujan
            12 => 24, // Dec
        ];
        
        return $seasonalTemps[$month];
    }
    
    /**
     * Get seasonal soil moisture patterns
     */
    private function getSeasonalSoilMoisture(int $month): float
    {
        $seasonalMoisture = [
            1 => 65,  // Jan - Musim hujan (tinggi)
            2 => 68,  // Feb
            3 => 62,  // Mar
            4 => 55,  // Apr - Mulai kering
            5 => 50,  // May
            6 => 45,  // Jun
            7 => 42,  // Jul
            8 => 40,  // Aug
            9 => 38,  // Sep - Paling kering
            10 => 45, // Oct
            11 => 55, // Nov - Mulai hujan
            12 => 60, // Dec
        ];
        
        return $seasonalMoisture[$month];
    }
    
    /**
     * Generate realistic water flow patterns
     */
    private function generateWaterFlow(int $hour, float $soilMoisture): float
    {
        // Watering times: early morning (5-7) and evening (17-19)
        $isWateringTime = ($hour >= 5 && $hour <= 7) || ($hour >= 17 && $hour <= 19);
        
        if ($isWateringTime && $soilMoisture < 50) {
            // Higher flow during watering with dry soil
            return round(rand(200, 800) + (rand(0, 200) / 100), 2);
        } elseif ($isWateringTime) {
            // Moderate flow during regular watering
            return round(rand(100, 400) + (rand(0, 200) / 100), 2);
        } else {
            // Minimal flow outside watering times
            return round(rand(0, 50) + (rand(0, 100) / 100), 2);
        }
    }
    
    /**
     * Generate realistic light intensity
     */
    private function generateLightIntensity(int $hour): int
    {
        if ($hour >= 6 && $hour <= 18) {
            // Daylight hours - bell curve pattern
            $peakHour = 12;
            $distanceFromPeak = abs($hour - $peakHour);
            $maxIntensity = 50000; // Max lux for outdoor
            $intensity = $maxIntensity * (1 - ($distanceFromPeak / 6));
            return max(1000, (int)($intensity + rand(-5000, 5000)));
        } else {
            // Night time - very low light
            return rand(0, 100);
        }
    }
    
    /**
     * Advanced status determination with multiple parameters
     */
    private function determineAdvancedStatus(float $temperature, float $humidity, float $soilMoisture): string
    {
        $criticalCount = 0;
        $alertCount = 0;
        
        // Temperature checks
        if ($temperature > 40 || $temperature < 5) {
            $criticalCount++;
        } elseif ($temperature > 37 || $temperature < 10) {
            $alertCount++;
        }
        
        // Soil moisture checks
        if ($soilMoisture < 15 || $soilMoisture > 85) {
            $criticalCount++;
        } elseif ($soilMoisture < 25 || $soilMoisture > 75) {
            $alertCount++;
        }
        
        // Humidity checks
        if ($humidity > 95 || $humidity < 20) {
            $criticalCount++;
        } elseif ($humidity > 90 || $humidity < 30) {
            $alertCount++;
        }
        
        // Status decision
        if ($criticalCount >= 2) {
            return 'kritis';
        } elseif ($criticalCount >= 1 || $alertCount >= 2) {
            return 'peringatan';
        } else {
            return 'normal';
        }
    }
    
    /**
     * Clean up old sensor data
     */
    private function cleanupOldData(): void
    {
        $this->command->info('🧹 Cleaning up old sensor data...');
        $deletedCount = DB::table('sensor_data')->delete();
        
        if ($deletedCount > 0) {
            $this->command->info("🗑️  Deleted {$deletedCount} old records");
        }
    }
    
    /**
     * Display seeding statistics
     */
    private function displayStatistics(): void
    {
        $this->command->newLine();
        $this->command->info('📈 Seeding Statistics:');
        
        $totalRecords = DB::table('sensor_data')->count();
        $normalCount = DB::table('sensor_data')->where('status', 'normal')->count();
        $alertCount = DB::table('sensor_data')->where('status', 'peringatan')->count();
        $criticalCount = DB::table('sensor_data')->where('status', 'kritis')->count();
        
        $this->command->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Normal', $normalCount, round(($normalCount / $totalRecords) * 100, 1) . '%'],
                ['Peringatan', $alertCount, round(($alertCount / $totalRecords) * 100, 1) . '%'],
                ['Kritis', $criticalCount, round(($criticalCount / $totalRecords) * 100, 1) . '%'],
                ['Total', $totalRecords, '100%'],
            ]
        );
        
        $dateRange = DB::table('sensor_data')
            ->selectRaw('MIN(recorded_at) as earliest, MAX(recorded_at) as latest')
            ->first();
            
        $this->command->info("📅 Date range: {$dateRange->earliest} to {$dateRange->latest}");
        $this->command->info('🎉 Sensor data seeding completed successfully!');
    }
}