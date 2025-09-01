<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaterStorage;

class WaterStorageSeederFixed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        WaterStorage::truncate();

        // Create water storage data with irrigation lines
        $waterStorages = [
            [
                'tank_name' => 'Main Water Tank A',
                'zone_name' => 'Greenhouse Complex A',
                'area_name' => 'Blok Tomat Hidroponik A1',
                'zone_description' => 'Kompleks greenhouse dengan sistem hidroponik untuk tanaman tomat premium.',
                'capacity_liters' => 2000.00,
                'current_volume_liters' => 1600.00,
                'percentage' => 80.00,
                'status' => 'normal',
                'device_id' => 1,
                'total_lines' => 8,
                'area_size_sqm' => 400.00,
                'plant_types' => 'Tomat Cherry, Tomat Beefsteak',
                'irrigation_system_type' => 'drip',
                'irrigation_lines' => [
                    [
                        'line_id' => 'L001',
                        'line_name' => 'Jalur Tomat Cherry A',
                        'line_type' => 'drip',
                        'plant_count' => 50,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_001',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 1-2',
                                'status' => 'active'
                            ],
                            [
                                'node_id' => 'NODE_A1_002',
                                'sensor_type' => 'temperature_humidity',
                                'location' => 'Row 3-4',
                                'status' => 'active'
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L002',
                        'line_name' => 'Jalur Tomat Cherry B',
                        'line_type' => 'drip',
                        'plant_count' => 50,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_003',
                                'sensor_type' => 'ph_ec',
                                'location' => 'Row 5-6',
                                'status' => 'active'
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L003',
                        'line_name' => 'Jalur Beefsteak A',
                        'line_type' => 'drip',
                        'plant_count' => 40,
                        'coverage_sqm' => 80.0,
                        'flow_rate_lpm' => 4.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_004',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Main Line A',
                                'status' => 'active'
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L004',
                        'line_name' => 'Jalur Beefsteak B',
                        'line_type' => 'drip',
                        'plant_count' => 40,
                        'coverage_sqm' => 80.0,
                        'flow_rate_lpm' => 4.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_005',
                                'sensor_type' => 'pressure',
                                'location' => 'Main Line B',
                                'status' => 'maintenance'
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L005',
                        'line_name' => 'Jalur Nursery A1',
                        'line_type' => 'misting',
                        'plant_count' => 200,
                        'coverage_sqm' => 20.0,
                        'flow_rate_lpm' => 1.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_006',
                                'sensor_type' => 'humidity',
                                'location' => 'Nursery Area',
                                'status' => 'active'
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L006',
                        'line_name' => 'Jalur Backup A1',
                        'line_type' => 'drip',
                        'plant_count' => 30,
                        'coverage_sqm' => 40.0,
                        'flow_rate_lpm' => 1.5,
                        'status' => 'maintenance',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'L007',
                        'line_name' => 'Jalur Experimen A1',
                        'line_type' => 'drip',
                        'plant_count' => 10,
                        'coverage_sqm' => 20.0,
                        'flow_rate_lpm' => 0.5,
                        'status' => 'inactive',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'L008',
                        'line_name' => 'Jalur Penelitian A1',
                        'line_type' => 'nft',
                        'plant_count' => 0,
                        'coverage_sqm' => 0.0,
                        'flow_rate_lpm' => 0.0,
                        'status' => 'inactive',
                        'nodes' => []
                    ]
                ]
            ],
            [
                'tank_name' => 'NFT System Tank B',
                'zone_name' => 'Greenhouse Complex B',
                'area_name' => 'Blok Sayuran Berdaun B1',
                'zone_description' => 'Sistem NFT (Nutrient Film Technique) untuk produksi sayuran berdaun.',
                'capacity_liters' => 3000.00,
                'current_volume_liters' => 2550.00,
                'percentage' => 85.00,
                'status' => 'normal',
                'device_id' => 2,
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
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_001',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Channel A Inlet',
                                'status' => 'active'
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N002',
                        'line_name' => 'NFT Channel Lettuce B',
                        'line_type' => 'nft',
                        'plant_count' => 80,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 3.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_002',
                                'sensor_type' => 'ec_meter',
                                'location' => 'Channel B Inlet',
                                'status' => 'active'
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N003',
                        'line_name' => 'NFT Channel Pak Choy A',
                        'line_type' => 'nft',
                        'plant_count' => 60,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N004',
                        'line_name' => 'NFT Channel Pak Choy B',
                        'line_type' => 'nft',
                        'plant_count' => 60,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N005',
                        'line_name' => 'NFT Channel Bayam A',
                        'line_type' => 'nft',
                        'plant_count' => 100,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N006',
                        'line_name' => 'NFT Channel Bayam B',
                        'line_type' => 'nft',
                        'plant_count' => 100,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N007',
                        'line_name' => 'NFT Channel Kangkung A',
                        'line_type' => 'nft',
                        'plant_count' => 120,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 1.8,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N008',
                        'line_name' => 'NFT Channel Kangkung B',
                        'line_type' => 'nft',
                        'plant_count' => 120,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 1.8,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N009',
                        'line_name' => 'NFT Channel Mixed A',
                        'line_type' => 'nft',
                        'plant_count' => 80,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.2,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N010',
                        'line_name' => 'NFT Channel Mixed B',
                        'line_type' => 'nft',
                        'plant_count' => 80,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.2,
                        'status' => 'active',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N011',
                        'line_name' => 'NFT Channel Reserve A',
                        'line_type' => 'nft',
                        'plant_count' => 40,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 1.5,
                        'status' => 'maintenance',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'N012',
                        'line_name' => 'NFT Channel Reserve B',
                        'line_type' => 'nft',
                        'plant_count' => 0,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 0.0,
                        'status' => 'inactive',
                        'nodes' => []
                    ]
                ]
            ],
            [
                'tank_name' => 'Backup Tank A',
                'zone_name' => 'Greenhouse Complex A',
                'area_name' => 'Backup Area Greenhouse A',
                'zone_description' => 'Tank cadangan untuk backup sistem irigasi area A.',
                'capacity_liters' => 1000.00,
                'current_volume_liters' => 450.00,
                'percentage' => 45.00,
                'status' => 'low',
                'device_id' => null,
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
                        'line_name' => 'Emergency Line 1',
                        'line_type' => 'sprinkler',
                        'plant_count' => 40,
                        'coverage_sqm' => 100.0,
                        'flow_rate_lpm' => 5.0,
                        'status' => 'maintenance',
                        'nodes' => []
                    ],
                    [
                        'line_id' => 'LB004',
                        'line_name' => 'Emergency Line 2',
                        'line_type' => 'sprinkler',
                        'plant_count' => 0,
                        'coverage_sqm' => 0.0,
                        'flow_rate_lpm' => 0.0,
                        'status' => 'inactive',
                        'nodes' => []
                    ]
                ]
            ]
        ];

        foreach ($waterStorages as $storage) {
            WaterStorage::create($storage);
        }

        echo "✅ WaterStorage seeder completed successfully!\n";
        echo "   Created " . count($waterStorages) . " water storage records\n";
        echo "   Total irrigation lines: " . array_sum(array_column($waterStorages, 'total_lines')) . "\n";
    }
}
