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
        DB::table('devices')->insert([
            [
                'device_id' => 'DEVICE_001',
                'device_name' => 'Sensor Kebun A',
                'location' => 'Kebun Tomat Blok A',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'device_id' => 'DEVICE_002',
                'device_name' => 'Sensor Kebun B',
                'location' => 'Kebun Cabai Blok B',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
