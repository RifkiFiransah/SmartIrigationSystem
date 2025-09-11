<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaterStorage;

class WaterStorageSeederMultiNode extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        WaterStorage::truncate();

        // Create water storage data with multiple nodes per irrigation line
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
                'total_lines' => 6,
                'area_size_sqm' => 400.00,
                'plant_types' => 'Tomat Cherry, Tomat Beefsteak',
                'irrigation_system_type' => 'drip',
                'irrigation_lines' => [
                    [
                        'line_id' => 'L001',
                        'line_name' => 'Jalur Tomat Cherry A - Seksi 1',
                        'line_type' => 'drip',
                        'plant_count' => 50,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_001',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 1 - Start',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_A1_002',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 1 - Middle',
                                'status' => 'active',
                                'coordinates' => ['x' => 25, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_A1_003',
                                'sensor_type' => 'temperature_humidity',
                                'location' => 'Row 1 - End',
                                'status' => 'active',
                                'coordinates' => ['x' => 40, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_A1_004',
                                'sensor_type' => 'ph_ec',
                                'location' => 'Row 2 - Start',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 10]
                            ],
                            [
                                'node_id' => 'NODE_A1_005',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Main Line Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 7.5]
                            ],
                            [
                                'node_id' => 'NODE_A1_006',
                                'sensor_type' => 'pressure',
                                'location' => 'Distribution Point',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 25, 'y' => 7.5]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L002',
                        'line_name' => 'Jalur Tomat Cherry B - Seksi 2',
                        'line_type' => 'drip',
                        'plant_count' => 50,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_007',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 3 - Start',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 15]
                            ],
                            [
                                'node_id' => 'NODE_A1_008',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 3 - Middle',
                                'status' => 'active',
                                'coordinates' => ['x' => 25, 'y' => 15]
                            ],
                            [
                                'node_id' => 'NODE_A1_009',
                                'sensor_type' => 'temperature_humidity',
                                'location' => 'Row 4 - Center',
                                'status' => 'active',
                                'coordinates' => ['x' => 17.5, 'y' => 20]
                            ],
                            [
                                'node_id' => 'NODE_A1_010',
                                'sensor_type' => 'light_sensor',
                                'location' => 'Canopy Level',
                                'status' => 'active',
                                'coordinates' => ['x' => 25, 'y' => 17.5]
                            ],
                            [
                                'node_id' => 'NODE_A1_011',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Section B Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 17.5]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L003',
                        'line_name' => 'Jalur Beefsteak A - Premium',
                        'line_type' => 'drip',
                        'plant_count' => 40,
                        'coverage_sqm' => 80.0,
                        'flow_rate_lpm' => 4.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_012',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 5 - Zone A',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 25]
                            ],
                            [
                                'node_id' => 'NODE_A1_013',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 5 - Zone B',
                                'status' => 'active',
                                'coordinates' => ['x' => 30, 'y' => 25]
                            ],
                            [
                                'node_id' => 'NODE_A1_014',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 6 - Zone A',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 35]
                            ],
                            [
                                'node_id' => 'NODE_A1_015',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 6 - Zone B',
                                'status' => 'active',
                                'coordinates' => ['x' => 30, 'y' => 35]
                            ],
                            [
                                'node_id' => 'NODE_A1_016',
                                'sensor_type' => 'ph_ec',
                                'location' => 'Nutrient Mix Point',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 30]
                            ],
                            [
                                'node_id' => 'NODE_A1_017',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Main Distribution',
                                'status' => 'active',
                                'coordinates' => ['x' => 20, 'y' => 30]
                            ],
                            [
                                'node_id' => 'NODE_A1_018',
                                'sensor_type' => 'pressure',
                                'location' => 'End Point Monitor',
                                'status' => 'active',
                                'coordinates' => ['x' => 40, 'y' => 30]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L004',
                        'line_name' => 'Jalur Beefsteak B - Standard',
                        'line_type' => 'drip',
                        'plant_count' => 40,
                        'coverage_sqm' => 80.0,
                        'flow_rate_lpm' => 4.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_019',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 7 - Section 1',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 40]
                            ],
                            [
                                'node_id' => 'NODE_A1_020',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Row 7 - Section 2',
                                'status' => 'active',
                                'coordinates' => ['x' => 30, 'y' => 40]
                            ],
                            [
                                'node_id' => 'NODE_A1_021',
                                'sensor_type' => 'temperature_humidity',
                                'location' => 'Row 8 - Center',
                                'status' => 'active',
                                'coordinates' => ['x' => 20, 'y' => 45]
                            ],
                            [
                                'node_id' => 'NODE_A1_022',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Branch Inlet',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 5, 'y' => 42.5]
                            ],
                            [
                                'node_id' => 'NODE_A1_023',
                                'sensor_type' => 'valve_control',
                                'location' => 'Auto Valve Station',
                                'status' => 'active',
                                'coordinates' => ['x' => 15, 'y' => 42.5]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L005',
                        'line_name' => 'Jalur Nursery - Bibit Tomat',
                        'line_type' => 'misting',
                        'plant_count' => 200,
                        'coverage_sqm' => 20.0,
                        'flow_rate_lpm' => 1.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_024',
                                'sensor_type' => 'humidity',
                                'location' => 'Nursery Zone 1',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 50]
                            ],
                            [
                                'node_id' => 'NODE_A1_025',
                                'sensor_type' => 'humidity',
                                'location' => 'Nursery Zone 2',
                                'status' => 'active',
                                'coordinates' => ['x' => 15, 'y' => 50]
                            ],
                            [
                                'node_id' => 'NODE_A1_026',
                                'sensor_type' => 'temperature_humidity',
                                'location' => 'Climate Control Point',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 52]
                            ],
                            [
                                'node_id' => 'NODE_A1_027',
                                'sensor_type' => 'misting_nozzle',
                                'location' => 'Misting Array 1',
                                'status' => 'active',
                                'coordinates' => ['x' => 7.5, 'y' => 48]
                            ],
                            [
                                'node_id' => 'NODE_A1_028',
                                'sensor_type' => 'misting_nozzle',
                                'location' => 'Misting Array 2',
                                'status' => 'active',
                                'coordinates' => ['x' => 12.5, 'y' => 48]
                            ],
                            [
                                'node_id' => 'NODE_A1_029',
                                'sensor_type' => 'pressure',
                                'location' => 'Misting Pump Station',
                                'status' => 'active',
                                'coordinates' => ['x' => 2, 'y' => 50]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'L006',
                        'line_name' => 'Jalur Experimental - Research',
                        'line_type' => 'drip',
                        'plant_count' => 20,
                        'coverage_sqm' => 40.0,
                        'flow_rate_lpm' => 1.5,
                        'status' => 'maintenance',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_A1_030',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Test Plot A',
                                'status' => 'active',
                                'coordinates' => ['x' => 45, 'y' => 10]
                            ],
                            [
                                'node_id' => 'NODE_A1_031',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Test Plot B',
                                'status' => 'active',
                                'coordinates' => ['x' => 45, 'y' => 20]
                            ],
                            [
                                'node_id' => 'NODE_A1_032',
                                'sensor_type' => 'research_sensor',
                                'location' => 'Data Collection Hub',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 47, 'y' => 15]
                            ],
                            [
                                'node_id' => 'NODE_A1_033',
                                'sensor_type' => 'ph_ec',
                                'location' => 'Nutrient Analysis Point',
                                'status' => 'inactive',
                                'coordinates' => ['x' => 43, 'y' => 15]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        foreach ($waterStorages as $storage) {
            WaterStorage::create($storage);
        }

        echo "✅ WaterStorage Multi-Node seeder completed successfully!\n";
        echo "   Created " . count($waterStorages) . " water storage records\n";
        
        $totalLines = array_sum(array_column($waterStorages, 'total_lines'));
        echo "   Total irrigation lines: " . $totalLines . "\n";
        
        $totalNodes = 0;
        foreach ($waterStorages as $storage) {
            foreach ($storage['irrigation_lines'] as $line) {
                $totalNodes += count($line['nodes']);
            }
        }
        echo "   Total sensor nodes: " . $totalNodes . "\n";
        echo "   Average nodes per line: " . round($totalNodes / $totalLines, 1) . "\n";
    }
}
