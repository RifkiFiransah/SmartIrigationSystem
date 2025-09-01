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
        // Get available devices
        $devices = Device::all();
        
        // Contoh data tangki air dengan area dan jalur irigasi
        $waterStorages = [
            // [
            //     'tank_name' => 'Main Water Tank A',
            //     'zone_name' => 'Greenhouse Complex A',
            //     'area_name' => 'Blok Tomat Hidroponik A1',
            //     'zone_description' => 'Kompleks greenhouse dengan sistem hidroponik untuk tanaman tomat premium.',
            //     'total_lines' => 8,
            //     'area_size_sqm' => 400.00,
            //     'plant_types' => 'Tomat Cherry, Tomat Beefsteak',
            //     'irrigation_system_type' => 'drip',
            //     'irrigation_lines' => [
            //         [
            //             'line_id' => 'L001',
            //             'line_name' => 'Jalur Tomat Cherry A',
            //             'line_type' => 'drip',
            //             'plant_count' => 50,
            //             'coverage_sqm' => 50.0,
            //             'flow_rate_lpm' => 2.5,
            //             'status' => 'active',
            //             'nodes' => [
            //                 [
            //                     'node_id' => 'NODE_A1_001',
            //                     'sensor_type' => 'soil_moisture',
            //                     'location' => 'Row 1-2',
            //                     'status' => 'active'
            //                 ],
            //                 [
            //                     'node_id' => 'NODE_A1_002',
            //                     'sensor_type' => 'temperature_humidity',
            //                     'location' => 'Row 3-4',
            //                     'status' => 'active'
            //                 ]
            //             ]
            //         ],
            //         [
            //             'line_id' => 'L002',
            //             'line_name' => 'Jalur Tomat Cherry B',
            //             'line_type' => 'drip',
            //             'plant_count' => 50,
            //             'coverage_sqm' => 50.0,
            //             'flow_rate_lpm' => 2.5,
            //             'status' => 'active',
            //             'nodes' => [
            //                 [
            //                     'node_id' => 'NODE_A1_003',
            //                     'sensor_type' => 'ph_ec',
            //                     'location' => 'Row 5-6',
            //                     'status' => 'active'
            //                 ]
            //             ]
            //         ],
            //         ],
            //         [
            //             'line_id' => 'L003',
            //             'line_name' => 'Jalur Beefsteak A',
            //             'line_type' => 'drip',
            //             'plant_count' => 40,
            //             'coverage_sqm' => 80.0,
            //             'flow_rate_lpm' => 4.0,
            //             'status' => 'active',
            //             'nodes' => ['NODE_A1_004']
            //         ],
            //         [
            //             'line_id' => 'L004',
            //             'line_name' => 'Jalur Beefsteak B',
            //             'line_type' => 'drip',
            //             'plant_count' => 40,
            //             'coverage_sqm' => 80.0,
            //             'flow_rate_lpm' => 4.0,
            //             'status' => 'active',
            //             'nodes' => ['NODE_A1_005']
            //         ],
            //         [
            //             'line_id' => 'L005',
            //             'line_name' => 'Jalur Nursery A1',
            //             'line_type' => 'misting',
            //             'plant_count' => 200,
            //             'coverage_sqm' => 20.0,
            //             'flow_rate_lpm' => 1.0,
            //             'status' => 'active',
            //             'nodes' => ['NODE_A1_006']
            //         ],
            //         [
            //             'line_id' => 'L006',
            //             'line_name' => 'Jalur Backup A',
            //             'line_type' => 'drip',
            //             'plant_count' => 30,
            //             'coverage_sqm' => 40.0,
            //             'flow_rate_lpm' => 2.0,
            //             'status' => 'maintenance',
            //             'nodes' => []
            //         ],
            //         [
            //             'line_id' => 'L007',
            //             'line_name' => 'Jalur Ekspansi A',
            //             'line_type' => 'drip',
            //             'plant_count' => 0,
            //             'coverage_sqm' => 40.0,
            //             'flow_rate_lpm' => 2.0,
            //             'status' => 'inactive',
            //             'nodes' => []
            //         ],
            //         [
            //             'line_id' => 'L008',
            //             'line_name' => 'Jalur Testing A',
            //             'line_type' => 'mixed',
            //             'plant_count' => 10,
            //             'coverage_sqm' => 40.0,
            //             'flow_rate_lpm' => 1.5,
            //             'status' => 'active',
            //             'nodes' => ['NODE_A1_007']
            //         ]
            //     ],
            //     'device_id' => $devices->where('device_id', 'DEVICE_001')->first()?->id,
            //     'associated_devices' => [
            //         [
            //             'device_id' => $devices->where('device_id', 'DEVICE_001')->first()?->id,
            //             'role' => 'Primary Controller & Soil Moisture Sensor'
            //         ]
            //     ],
            //     'total_capacity' => 2000.00,
            //     'current_volume' => 1600.00,
            //     'max_daily_usage' => 300.00,
            //     'status' => 'normal',
            //     'notes' => 'Tangki utama untuk blok tomat hidroponik dengan 8 jalur irigasi. Dilengkapi sensor otomatis.',
            // ],
            [
                'tank_name' => 'Backup Tank A',
                'zone_name' => 'Greenhouse Complex A',
                'area_name' => 'Backup Area Greenhouse A',
                'zone_description' => 'Tangki cadangan untuk greenhouse A sebagai backup saat maintenance.',
                'total_lines' => 4,
                'area_size_sqm' => 200.00,
                'plant_types' => 'Tomat (Backup)',
                'irrigation_system_type' => 'drip',
                'irrigation_lines' => [
                    [
                        'line_id' => 'LB001',
                        'line_name' => 'Backup Line 1',
                        'line_type' => 'drip',
                        'plant_count' => 25,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'LB002',
                        'line_name' => 'Backup Line 2',
                        'line_type' => 'drip',
                        'plant_count' => 25,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'LB003',
                        'line_name' => 'Emergency Line A',
                        'line_type' => 'manual',
                        'plant_count' => 20,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 1.5,
                        'status' => 'inactive',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'LB004',
                        'line_name' => 'Emergency Line B',
                        'line_type' => 'manual',
                        'plant_count' => 20,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 1.5,
                        'status' => 'inactive',
                        'nodes' => []
                    ]
                ],
                'device_id' => null,
                'associated_devices' => [],
                'total_capacity' => 1000.00,
                'current_volume' => 200.00,
                'max_daily_usage' => 150.00,
                'status' => 'low',
                'notes' => 'Tangki cadangan - perlu diisi ulang segera. 4 jalur backup untuk kontinuitas operasi.',
            ],
            [
                'tank_name' => 'NFT System Tank B',
                'zone_name' => 'Greenhouse Complex B',
                'area_name' => 'Blok Sayuran Berdaun B1',
                'zone_description' => 'Kompleks greenhouse dengan sistem NFT untuk sayuran berdaun hijau premium.',
                'total_lines' => 12,
                'area_size_sqm' => 600.00,
                'plant_types' => 'Lettuce, Pak Choy, Bayam, Kangkung',
                'irrigation_system_type' => 'nft',
                'irrigation_lines' => [
                    [
                        'line_id' => 'N001',
                        'line_name' => 'NFT Channel Lettuce A',
                        'line_type' => 'nft',
                        'plant_count' => 80,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 3.0,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_001']
                    ],
                    [
                        'line_id' => 'N002',
                        'line_name' => 'NFT Channel Lettuce B',
                        'line_type' => 'nft',
                        'plant_count' => 80,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 3.0,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_002']
                    ],
                    [
                        'line_id' => 'N003',
                        'line_name' => 'NFT Channel Pak Choy A',
                        'line_type' => 'nft',
                        'plant_count' => 60,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_003']
                    ],
                    [
                        'line_id' => 'N004',
                        'line_name' => 'NFT Channel Pak Choy B',
                        'line_type' => 'nft',
                        'plant_count' => 60,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_004']
                    ],
                    [
                        'line_id' => 'N005',
                        'line_name' => 'NFT Channel Bayam A',
                        'line_type' => 'nft',
                        'plant_count' => 100,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_005']
                    ],
                    [
                        'line_id' => 'N006',
                        'line_name' => 'NFT Channel Bayam B',
                        'line_type' => 'nft',
                        'plant_count' => 100,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_006']
                    ],
                    [
                        'line_id' => 'N007',
                        'line_name' => 'NFT Channel Kangkung A',
                        'line_type' => 'nft',
                        'plant_count' => 120,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.8,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_007']
                    ],
                    [
                        'line_id' => 'N008',
                        'line_name' => 'NFT Channel Kangkung B',
                        'line_type' => 'nft',
                        'plant_count' => 120,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.8,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_008']
                    ],
                    [
                        'line_id' => 'N009',
                        'line_name' => 'Research Channel A',
                        'line_type' => 'nft',
                        'plant_count' => 20,
                        'coverage_sqm' => 25.0,
                        'flow_rate_lpm' => 1.5,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_009']
                    ],
                    [
                        'line_id' => 'N010',
                        'line_name' => 'Research Channel B',
                        'line_type' => 'nft',
                        'plant_count' => 20,
                        'coverage_sqm' => 25.0,
                        'flow_rate_lpm' => 1.5,
                        'status' => 'active',
                        'nodes' => ['NODE_B1_010']
                    ],
                    [
                        'line_id' => 'N011',
                        'line_name' => 'Backup NFT Channel A',
                        'line_type' => 'nft',
                        'plant_count' => 0,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'maintenance',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N012',
                        'line_name' => 'Backup NFT Channel B',
                        'line_type' => 'nft',
                        'plant_count' => 0,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'inactive',
                        'nodes' => []
                    ]
                ],
                'device_id' => $devices->where('device_id', 'DEVICE_002')->first()?->id,
                'associated_devices' => [
                    [
                        'device_id' => $devices->where('device_id', 'DEVICE_002')->first()?->id,
                        'role' => 'NFT Controller & pH/EC Sensor'
                    ]
                ],
                'total_capacity' => 1500.00,
                'current_volume' => 1275.00,
                'max_daily_usage' => 250.00,
                'status' => 'normal',
                'notes' => 'Tangki NFT dengan 12 channel untuk sayuran berdaun. Air diperkaya nutrisi khusus leafy greens.',
            ],
            [
                'tank_name' => 'Outdoor Field Tank',
                'zone_name' => 'Outdoor Field - Mixed Vegetables',
                'zone_description' => 'Lahan terbuka untuk tanaman campuran: cabai, terong, buncis. Sistem sprinkler overhead.',
                'device_id' => $devices->where('device_id', 'DEVICE_003')->first()?->id,
                'associated_devices' => [
                    [
                        'device_id' => $devices->where('device_id', 'DEVICE_003')->first()?->id,
                        'role' => 'Weather Station'
                    ]
                ],
                'total_capacity' => 2000.00,
                'current_volume' => 1800.00,
                'max_daily_usage' => 300.00,
                'status' => 'normal',
                'notes' => 'Tangki besar untuk lahan outdoor. Dilengkapi filter penyaring kotoran.',
            ],
            [
                'tank_name' => 'Nursery Tank',
                'zone_name' => 'Nursery Area - Seedling Zone',
                'zone_description' => 'Area pembibitan untuk semua jenis tanaman. Sistem misting halus untuk bibit.',
                'device_id' => null,
                'associated_devices' => [],
                'total_capacity' => 400.00,
                'current_volume' => 380.00,
                'max_daily_usage' => 50.00,
                'status' => 'full',
                'notes' => 'Tangki khusus nursery dengan air yang sudah di-filter dan di-sterilisasi.',
            ],
            [
                'tank_name' => 'Emergency Reserve Tank',
                'zone_name' => 'Central Reserve - All Zones',
                'zone_description' => 'Tangki cadangan darurat untuk semua zona saat terjadi gangguan supply air utama.',
                'device_id' => null,
                'associated_devices' => [],
                'total_capacity' => 1200.00,
                'current_volume' => 50.00,
                'max_daily_usage' => 0.00,
                'status' => 'empty',
                'notes' => 'PERLU SEGERA DIISI! Tangki darurat untuk kontinuitas operasi.',
            ]
        ];

        foreach ($waterStorages as $storage) {
            WaterStorage::create($storage);
        }
    }
}
