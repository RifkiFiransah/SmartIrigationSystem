<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if devices already exist to avoid duplicates
        $existingDevices = DB::table('devices')->pluck('device_id')->toArray();
        
        $devices = [
            [
                'device_id' => 'DEVICE_001',
                'device_name' => 'Node 1 - Greenhouse A',
                'location' => 'Greenhouse A - Tomat',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'device_id' => 'DEVICE_002',
                'device_name' => 'Node 2 - Greenhouse B',
                'location' => 'Greenhouse B - Cabai',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'device_id' => 'DEVICE_003',
                'device_name' => 'Node 3 - Outdoor Field',
                'location' => 'Outdoor Field - Sayuran',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert only devices that don't exist yet
        foreach ($devices as $device) {
            if (!in_array($device['device_id'], $existingDevices)) {
                DB::table('devices')->insert($device);
            }
        }
    }
}
