<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaterStorage;
use Illuminate\Support\Facades\DB;

class WaterStorageSeederSingle extends Seeder
{
    public function run(): void
    {
        if (WaterStorage::count() > 0) {
            return; // do not overwrite existing
        }

        $deviceIds = DB::table('devices')->orderBy('id')->pluck('id')->toArray();

        WaterStorage::create([
            'tank_name' => 'Tangki Utama',
            'zone_name' => 'Zona Utama',
            'zone_description' => 'Tangki tunggal melayani 8 node sensor & irigasi',
            // Set total tank capacity to 1000L as requested, seed current volume at 80% (800L)
            'capacity_liters' => 1000,
            'current_volume_liters' => 800,
            'status' => 'normal',
            'device_id' => $deviceIds[0] ?? null,
            'total_lines' => 3,
            'area_size_sqm' => 250,
            'plant_types' => 'Tomat, Cabai, Sayuran Daun',
            'irrigation_system_type' => 'drip',
            'irrigation_lines' => [
                [
                    'line_id' => 'L1',
                    'line_name' => 'Jalur Tomat',
                    'line_type' => 'drip',
                    'plant_count' => 120,
                    'coverage_sqm' => 90,
                    'flow_rate_lpm' => 6.5,
                    'status' => 'active',
                    'nodes' => [
                        ['node_id' => 'DEVICE_001', 'role' => 'soil', 'status' => 'active'],
                        ['node_id' => 'DEVICE_002', 'role' => 'climate', 'status' => 'active'],
                        ['node_id' => 'DEVICE_003', 'role' => 'valve', 'status' => 'active'],
                    ],
                ],
                [
                    'line_id' => 'L2',
                    'line_name' => 'Jalur Cabai',
                    'line_type' => 'drip',
                    'plant_count' => 140,
                    'coverage_sqm' => 100,
                    'flow_rate_lpm' => 7.0,
                    'status' => 'active',
                    'nodes' => [
                        ['node_id' => 'DEVICE_004', 'role' => 'soil', 'status' => 'active'],
                        ['node_id' => 'DEVICE_005', 'role' => 'climate', 'status' => 'active'],
                        ['node_id' => 'DEVICE_006', 'role' => 'flow', 'status' => 'active'],
                    ],
                ],
                [
                    'line_id' => 'L3',
                    'line_name' => 'Jalur Sayuran Daun',
                    'line_type' => 'sprinkler',
                    'plant_count' => 300,
                    'coverage_sqm' => 60,
                    'flow_rate_lpm' => 5.0,
                    'status' => 'active',
                    'nodes' => [
                        ['node_id' => 'DEVICE_007', 'role' => 'soil', 'status' => 'active'],
                        ['node_id' => 'DEVICE_008', 'role' => 'climate', 'status' => 'active'],
                    ],
                ],
            ],
            'associated_devices' => collect($deviceIds)->skip(1)->map(fn($id, $i)=>[
                'device_id' => $id,
                'role' => 'Node +'.($i+1)
            ])->values()->all(),
            'height_cm' => 120,
            'calibration_offset_cm' => 2,
            'last_height_cm' => 95,
            'max_daily_usage' => 300,
            'notes' => 'Seeder sederhana satu tangki + 8 node',
        ]);
    }
}
