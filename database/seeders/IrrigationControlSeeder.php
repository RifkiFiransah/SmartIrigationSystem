<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IrrigationControl;
use App\Models\IrrigationSchedule;
use App\Models\Device;

class IrrigationControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada device yang bisa digunakan
        $devices = Device::all();
        
        if ($devices->isEmpty()) {
            // Buat sample devices jika belum ada
            $devices = collect([
                Device::create([
                    'device_name' => 'Greenhouse Controller A',
                    'device_id' => 'GH_CTRL_001',
                    'location' => 'Greenhouse Section A',
                    'is_active' => true,
                ]),
                Device::create([
                    'device_name' => 'Garden Controller B', 
                    'device_id' => 'GD_CTRL_002',
                    'location' => 'Garden Section B',
                    'is_active' => true,
                ]),
                Device::create([
                    'device_name' => 'Field Controller C',
                    'device_id' => 'FD_CTRL_003', 
                    'location' => 'Field Section C',
                    'is_active' => true,
                ]),
            ]);
        }

        // Sample irrigation controls
        $controls = [
            [
                'control_name' => 'Main Water Pump',
                'control_type' => 'pump',
                'device_id' => $devices->first()->id,
                'pin_number' => 'GPIO_2',
                'status' => 'off',
                'mode' => 'auto',
                'duration_minutes' => 45,
                'settings' => [
                    'flow_rate' => 150, // L/min
                    'pressure_threshold' => 30, // psi
                    'auto_shutoff' => true,
                ],
                'is_active' => true,
                'description' => 'Main water pump untuk seluruh sistem irigasi greenhouse'
            ],
            [
                'control_name' => 'Zone A Sprinkler Valve',
                'control_type' => 'valve',
                'device_id' => $devices->first()->id,
                'pin_number' => 'GPIO_3',
                'status' => 'off',
                'mode' => 'manual',
                'duration_minutes' => 30,
                'settings' => [
                    'zone_coverage' => 'Section A1-A4',
                    'nozzle_type' => 'mist',
                    'flow_control' => 'adjustable',
                ],
                'is_active' => true,
                'description' => 'Kontrol valve untuk sprinkler zone A (tanaman sayuran)'
            ],
            [
                'control_name' => 'Zone B Drip System',
                'control_type' => 'valve',
                'device_id' => $devices->skip(1)->first()->id,
                'pin_number' => 'GPIO_4',
                'status' => 'off',
                'mode' => 'auto',
                'duration_minutes' => 60,
                'settings' => [
                    'drip_rate' => 'slow',
                    'coverage_area' => '50 sqm',
                    'water_savings' => true,
                ],
                'is_active' => true,
                'description' => 'Sistem drip irrigation untuk zone B (tanaman buah)'
            ],
            [
                'control_name' => 'Backup Pump Motor',
                'control_type' => 'motor',
                'device_id' => $devices->skip(2)->first()->id,
                'pin_number' => 'GPIO_5',
                'status' => 'off',
                'mode' => 'manual',
                'duration_minutes' => 30,
                'settings' => [
                    'backup_mode' => true,
                    'auto_trigger' => 'main_pump_failure',
                    'capacity' => 'medium',
                ],
                'is_active' => true,
                'description' => 'Motor pump cadangan untuk emergency atau backup system'
            ],
            [
                'control_name' => 'Fertilizer Injector Pump',
                'control_type' => 'pump',
                'device_id' => $devices->first()->id,
                'pin_number' => 'GPIO_6',
                'status' => 'off',
                'mode' => 'manual',
                'duration_minutes' => 15,
                'settings' => [
                    'fertilizer_ratio' => '1:200',
                    'injection_rate' => 'controlled',
                    'nutrient_type' => 'NPK',
                ],
                'is_active' => true,
                'description' => 'Pompa injeksi pupuk otomatis untuk nutrisi tanaman'
            ],
        ];

        // Buat irrigation controls
        $createdControls = collect();
        foreach ($controls as $controlData) {
            $control = IrrigationControl::create($controlData);
            $createdControls->push($control);
        }

        // Buat sample schedules untuk beberapa controls
        $schedules = [
            [
                'schedule_name' => 'Morning Watering - Main Pump',
                'irrigation_control_id' => $createdControls->first()->id,
                'schedule_type' => 'daily',
                'start_time' => '06:00:00',
                'duration_minutes' => 45,
                'days_of_week' => null,
                'trigger_conditions' => null,
                'is_active' => true,
                'is_enabled' => true,
                'description' => 'Penyiraman pagi setiap hari jam 6:00'
            ],
            [
                'schedule_name' => 'Evening Watering - Zone A',
                'irrigation_control_id' => $createdControls->skip(1)->first()->id,
                'schedule_type' => 'daily',
                'start_time' => '18:00:00',
                'duration_minutes' => 30,
                'days_of_week' => null,
                'trigger_conditions' => null,
                'is_active' => true,
                'is_enabled' => true,
                'description' => 'Penyiraman sore untuk zone A'
            ],
            [
                'schedule_name' => 'Weekend Drip System',
                'irrigation_control_id' => $createdControls->skip(2)->first()->id,
                'schedule_type' => 'weekly',
                'start_time' => '07:00:00',
                'duration_minutes' => 90,
                'days_of_week' => [0, 6], // Sunday & Saturday
                'trigger_conditions' => null,
                'is_active' => true,
                'is_enabled' => true,
                'description' => 'Drip irrigation intensif weekend'
            ],
            [
                'schedule_name' => 'Soil Moisture Based Watering',
                'irrigation_control_id' => $createdControls->skip(1)->first()->id,
                'schedule_type' => 'sensor_based',
                'start_time' => '00:00:00',
                'duration_minutes' => 20,
                'days_of_week' => null,
                'trigger_conditions' => [
                    'soil_moisture' => [
                        'operator' => '<',
                        'value' => 30
                    ],
                    'temperature' => [
                        'operator' => '>',
                        'value' => 25
                    ]
                ],
                'is_active' => true,
                'is_enabled' => true,
                'description' => 'Auto watering berdasarkan sensor kelembapan tanah'
            ],
            [
                'schedule_name' => 'Weekly Fertilizer Schedule',
                'irrigation_control_id' => $createdControls->last()->id,
                'schedule_type' => 'weekly',
                'start_time' => '05:30:00',
                'duration_minutes' => 15,
                'days_of_week' => [1, 4], // Monday & Thursday
                'trigger_conditions' => null,
                'is_active' => true,
                'is_enabled' => true,
                'description' => 'Pemberian pupuk cair 2x seminggu'
            ],
        ];

        foreach ($schedules as $scheduleData) {
            $schedule = IrrigationSchedule::create($scheduleData);
            // Update next_run_at
            $schedule->updateNextRun();
        }

        $this->command->info('✅ Irrigation Control seeder completed!');
        $this->command->info("Created {$createdControls->count()} irrigation controls");
        $this->command->info("Created " . count($schedules) . " irrigation schedules");
    }
}
