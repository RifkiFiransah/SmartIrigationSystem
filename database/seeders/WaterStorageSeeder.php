<?php

namespace Database\Seeders;

use App\Models\WaterStorage;
use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WaterStorageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh data tangki air
        $waterStorages = [
            [
                'tank_name' => 'Main Water Tank',
                'device_id' => Device::first()?->id,
                'total_capacity' => 1000.00,
                'current_volume' => 750.00,
                'status' => 'normal',
                'notes' => 'Tangki air utama untuk irigasi kebun utama',
            ],
            [
                'tank_name' => 'Secondary Tank A',
                'device_id' => null,
                'total_capacity' => 500.00,
                'current_volume' => 125.00,
                'status' => 'low',
                'notes' => 'Tangki cadangan untuk area A',
            ],
            [
                'tank_name' => 'Emergency Tank',
                'device_id' => null,
                'total_capacity' => 250.00,
                'current_volume' => 20.00,
                'status' => 'empty',
                'notes' => 'Tangki darurat - perlu diisi segera',
            ],
            [
                'tank_name' => 'Greenhouse Tank',
                'device_id' => null,
                'total_capacity' => 300.00,
                'current_volume' => 280.00,
                'status' => 'full',
                'notes' => 'Tangki khusus untuk greenhouse',
            ],
        ];

        foreach ($waterStorages as $storage) {
            WaterStorage::create($storage);
        }
    }
}
