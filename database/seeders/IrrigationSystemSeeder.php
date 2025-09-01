<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IrrigationControl;
use App\Models\IrrigationSchedule;
use App\Models\WaterStorage;
use Carbon\Carbon;

class IrrigationSystemSeeder extends Seeder
{
    public function run()
    {
        // Create irrigation controls for each water storage area
        $waterStorages = WaterStorage::all();
        
        foreach ($waterStorages as $storage) {
            // Skip if device_id is null
            if (!$storage->device_id) {
                continue;
            }
            
            // Create irrigation control for this storage tank
            $control = IrrigationControl::create([
                'control_name' => "Kontrol Irigasi " . $storage->area_name,
                'control_type' => $storage->irrigation_system_type === 'drip' ? 'pump' : 'valve',
                'device_id' => $storage->device_id,
                'pin_number' => 'GPIO_' . (rand(1, 27)), // Random GPIO pin
                'status' => 'off',
                'mode' => 'auto',
                'duration_minutes' => 30,
                'last_activated_at' => null,
                'last_deactivated_at' => null,
                'settings' => [
                    'max_flow_rate' => 10,
                    'min_pressure' => 1.5,
                    'max_pressure' => 3.0,
                    'soil_moisture_threshold' => 40,
                    'temperature_threshold' => 35,
                    'ph_min' => 6.0,
                    'ph_max' => 7.5,
                    'area_coverage_sqm' => $storage->area_size_sqm,
                    'plant_types' => $storage->plant_types,
                    'auto_stop_conditions' => [
                        'tank_low_level' => 20,
                        'high_temperature' => 40,
                        'sensor_failure' => true
                    ]
                ],
                'is_active' => true,
                'description' => "Sistem kontrol irigasi untuk " . $storage->area_name
            ]);

            // Create daily schedules for each irrigation control
            $this->createDailySchedules($control);
            
            // Create sensor-based schedules
            $this->createSensorBasedSchedules($control, $storage);
        }
    }

    private function createDailySchedules($control)
    {
        // Morning irrigation schedule
        IrrigationSchedule::create([
            'schedule_name' => "Penyiraman Pagi - " . $control->control_name,
            'irrigation_control_id' => $control->id,
            'schedule_type' => 'daily',
            'start_time' => Carbon::createFromTime(6, 0, 0),
            'duration_minutes' => 30,
            'days_of_week' => null, // Daily
            'trigger_conditions' => null,
            'is_active' => true,
            'is_enabled' => true,
            'last_run_at' => null,
            'next_run_at' => Carbon::tomorrow()->setTime(6, 0, 0),
            'run_count' => 0,
            'description' => 'Penyiraman rutin pagi hari untuk menjaga kelembaban tanah'
        ]);

        // Evening irrigation schedule
        IrrigationSchedule::create([
            'schedule_name' => "Penyiraman Sore - " . $control->control_name,
            'irrigation_control_id' => $control->id,
            'schedule_type' => 'daily',
            'start_time' => Carbon::createFromTime(17, 30, 0),
            'duration_minutes' => 25,
            'days_of_week' => null,
            'trigger_conditions' => null,
            'is_active' => true,
            'is_enabled' => true,
            'last_run_at' => null,
            'next_run_at' => Carbon::today()->setTime(17, 30, 0),
            'run_count' => 0,
            'description' => 'Penyiraman sore hari sebelum matahari terbenam'
        ]);

        // Weekly deep watering (weekends)
        IrrigationSchedule::create([
            'schedule_name' => "Penyiraman Intensif - " . $control->control_name,
            'irrigation_control_id' => $control->id,
            'schedule_type' => 'weekly',
            'start_time' => Carbon::createFromTime(5, 0, 0),
            'duration_minutes' => 60,
            'days_of_week' => [0, 6], // Sunday and Saturday
            'trigger_conditions' => null,
            'is_active' => false, // Disabled by default
            'is_enabled' => true,
            'last_run_at' => null,
            'next_run_at' => Carbon::now()->next(Carbon::SUNDAY)->setTime(5, 0, 0),
            'run_count' => 0,
            'description' => 'Penyiraman intensif akhir pekan untuk nutrisi mendalam'
        ]);
    }

    private function createSensorBasedSchedules($control, $storage)
    {
        // Soil moisture based irrigation
        IrrigationSchedule::create([
            'schedule_name' => "Auto Kelembaban - " . $control->control_name,
            'irrigation_control_id' => $control->id,
            'schedule_type' => 'sensor_based',
            'start_time' => Carbon::createFromTime(0, 0, 0), // Any time
            'duration_minutes' => 20,
            'days_of_week' => null,
            'trigger_conditions' => [
                'soil_moisture_below' => 35,
                'temperature_above' => 30,
                'time_range' => ['06:00', '18:00'], // Only during day
                'cooldown_minutes' => 120 // Wait 2 hours between triggers
            ],
            'is_active' => true,
            'is_enabled' => true,
            'last_run_at' => null,
            'next_run_at' => null, // Triggered by sensor
            'run_count' => 0,
            'description' => 'Penyiraman otomatis berdasarkan kelembaban tanah rendah'
        ]);

        // Temperature emergency irrigation
        IrrigationSchedule::create([
            'schedule_name' => "Emergency Suhu - " . $control->control_name,
            'irrigation_control_id' => $control->id,
            'schedule_type' => 'sensor_based',
            'start_time' => Carbon::createFromTime(0, 0, 0),
            'duration_minutes' => 15,
            'days_of_week' => null,
            'trigger_conditions' => [
                'temperature_above' => 38,
                'soil_moisture_below' => 50,
                'immediate_trigger' => true,
                'cooldown_minutes' => 60
            ],
            'is_active' => true,
            'is_enabled' => true,
            'last_run_at' => null,
            'next_run_at' => null,
            'run_count' => 0,
            'description' => 'Penyiraman darurat saat suhu terlalu tinggi'
        ]);

        // Nutrient level maintenance (for NFT systems)
        if ($storage->irrigation_system_type === 'nft') {
            IrrigationSchedule::create([
                'schedule_name' => "Sirkulasi Nutrisi - " . $control->control_name,
                'irrigation_control_id' => $control->id,
                'schedule_type' => 'sensor_based',
                'start_time' => Carbon::createFromTime(0, 0, 0),
                'duration_minutes' => 10,
                'days_of_week' => null,
                'trigger_conditions' => [
                    'ec_below' => 1.2,
                    'ph_outside_range' => [5.5, 6.8],
                    'nutrient_flow_check' => true,
                    'interval_hours' => 4
                ],
                'is_active' => true,
                'is_enabled' => true,
                'last_run_at' => null,
                'next_run_at' => null,
                'run_count' => 0,
                'description' => 'Sirkulasi dan penyesuaian nutrisi untuk sistem NFT'
            ]);
        }
    }
}
