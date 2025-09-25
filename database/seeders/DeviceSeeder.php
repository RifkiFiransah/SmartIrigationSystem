<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating devices...');
        
        $devices = [
            ['device_id' => 'DEVICE_001', 'device_name' => 'Node 1', 'location' => 'Greenhouse A - Zona 1'],
            ['device_id' => 'DEVICE_002', 'device_name' => 'Node 2', 'location' => 'Greenhouse A - Zona 2'],
            ['device_id' => 'DEVICE_003', 'device_name' => 'Node 3', 'location' => 'Greenhouse B - Zona 1'],
            ['device_id' => 'DEVICE_004', 'device_name' => 'Node 4', 'location' => 'Greenhouse B - Zona 2'],
            ['device_id' => 'DEVICE_005', 'device_name' => 'Node 5', 'location' => 'Area Outdoor - Utara'],
            ['device_id' => 'DEVICE_006', 'device_name' => 'Node 6', 'location' => 'Area Outdoor - Selatan'],
            ['device_id' => 'DEVICE_007', 'device_name' => 'Node 7', 'location' => 'Nursery - Bibit'],
            ['device_id' => 'DEVICE_008', 'device_name' => 'Node 8', 'location' => 'Research Plot'],
            ['device_id' => 'DEVICE_009', 'device_name' => 'Node 9', 'location' => 'Reservoir Utama'],
            ['device_id' => 'DEVICE_010', 'device_name' => 'Node 10', 'location' => 'Pompa Intake'],
            ['device_id' => 'DEVICE_011', 'device_name' => 'Node 11', 'location' => 'Bed Percobaan A'],
            ['device_id' => 'DEVICE_012', 'device_name' => 'Node 12', 'location' => 'Bed Percobaan B'],
        ];

        $existing = DB::table('devices')->pluck('device_id')->toArray();
        $newDevices = [];
        
        foreach ($devices as $device) {
            if (!in_array($device['device_id'], $existing)) {
                $newDevices[] = array_merge($device, [
                    'is_active' => true,
                    'connection_state' => 'online',
                    'connection_state_source' => 'auto',
                    'valve_state' => 'closed',
                    'valve_state_changed_at' => now(),
                    'last_seen_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        if (count($newDevices) > 0) {
            DB::table('devices')->insert($newDevices);
            $this->command->info('Created ' . count($newDevices) . ' new devices');
        } else {
            $this->command->info('All devices already exist');
        }
    }
}
