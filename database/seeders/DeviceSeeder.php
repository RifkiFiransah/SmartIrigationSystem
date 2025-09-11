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
        $target = 8; // exactly 8 nodes
        $baseline = [
            'Greenhouse A - Zona 1',
            'Greenhouse A - Zona 2',
            'Greenhouse B - Zona 1',
            'Greenhouse B - Zona 2',
            'Area Outdoor - Utara',
            'Area Outdoor - Selatan',
            'Nursery - Bibit',
            'Research Plot',
        ];

        $existing = DB::table('devices')->pluck('device_id','device_id')->toArray();
        $rows = [];
        foreach (range(1,$target) as $i) {
            $idStr = str_pad((string)$i,3,'0',STR_PAD_LEFT);
            $deviceId = 'DEVICE_'.$idStr;
            if (isset($existing[$deviceId])) continue;
            $rows[] = [
                'device_id' => $deviceId,
                'device_name' => 'Node '.$i,
                'location' => $baseline[$i-1] ?? ('Lokasi '.$i),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($rows) {
            DB::table('devices')->insert($rows);
        }
    }
}
