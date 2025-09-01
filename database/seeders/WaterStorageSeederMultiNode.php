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
                'total_lines' => 8,
                'area_size_sqm' => 600.00,
                'plant_types' => 'Lettuce, Pak Choy, Bayam, Kangkung',
                'irrigation_system_type' => 'nft',
                'irrigation_lines' => [
                    [
                        'line_id' => 'N001',
                        'line_name' => 'NFT Channel Lettuce A - Premium Line',
                        'line_type' => 'nft',
                        'plant_count' => 80,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 3.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_001',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Inlet Monitoring',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_B1_002',
                                'sensor_type' => 'ec_meter',
                                'location' => 'Channel Start',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_B1_003',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Mid Channel Flow',
                                'status' => 'active',
                                'coordinates' => ['x' => 25, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_B1_004',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Channel End',
                                'status' => 'active',
                                'coordinates' => ['x' => 45, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_B1_005',
                                'sensor_type' => 'water_level',
                                'location' => 'Return Collection',
                                'status' => 'active',
                                'coordinates' => ['x' => 50, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_B1_006',
                                'sensor_type' => 'temperature',
                                'location' => 'Nutrient Temperature',
                                'status' => 'active',
                                'coordinates' => ['x' => 15, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_B1_007',
                                'sensor_type' => 'dissolved_oxygen',
                                'location' => 'Oxygen Level Monitor',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 35, 'y' => 5]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N002',
                        'line_name' => 'NFT Channel Lettuce B - Standard Line',
                        'line_type' => 'nft',
                        'plant_count' => 80,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 3.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_008',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Line B Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 10]
                            ],
                            [
                                'node_id' => 'NODE_B1_009',
                                'sensor_type' => 'ec_meter',
                                'location' => 'Channel B Start',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 10]
                            ],
                            [
                                'node_id' => 'NODE_B1_010',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Mid Channel B',
                                'status' => 'active',
                                'coordinates' => ['x' => 25, 'y' => 10]
                            ],
                            [
                                'node_id' => 'NODE_B1_011',
                                'sensor_type' => 'plant_health',
                                'location' => 'Growth Monitor Zone',
                                'status' => 'active',
                                'coordinates' => ['x' => 20, 'y' => 10]
                            ],
                            [
                                'node_id' => 'NODE_B1_012',
                                'sensor_type' => 'water_level',
                                'location' => 'Return B Collection',
                                'status' => 'active',
                                'coordinates' => ['x' => 50, 'y' => 10]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N003',
                        'line_name' => 'NFT Channel Pak Choy A - High Density',
                        'line_type' => 'nft',
                        'plant_count' => 120,
                        'coverage_sqm' => 40.0,
                        'flow_rate_lpm' => 2.8,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_013',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Pak Choy Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 15]
                            ],
                            [
                                'node_id' => 'NODE_B1_014',
                                'sensor_type' => 'ec_meter',
                                'location' => 'Channel C Start',
                                'status' => 'active',
                                'coordinates' => ['x' => 8, 'y' => 15]
                            ],
                            [
                                'node_id' => 'NODE_B1_015',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Section C1',
                                'status' => 'active',
                                'coordinates' => ['x' => 15, 'y' => 15]
                            ],
                            [
                                'node_id' => 'NODE_B1_016',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Section C2',
                                'status' => 'active',
                                'coordinates' => ['x' => 25, 'y' => 15]
                            ],
                            [
                                'node_id' => 'NODE_B1_017',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Section C3',
                                'status' => 'active',
                                'coordinates' => ['x' => 35, 'y' => 15]
                            ],
                            [
                                'node_id' => 'NODE_B1_018',
                                'sensor_type' => 'plant_density',
                                'location' => 'Density Monitor',
                                'status' => 'active',
                                'coordinates' => ['x' => 20, 'y' => 15]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N004',
                        'line_name' => 'NFT Channel Kangkung Multi-Tier',
                        'line_type' => 'nft',
                        'plant_count' => 150,
                        'coverage_sqm' => 45.0,
                        'flow_rate_lpm' => 2.2,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_019',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Tier 1 Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 20]
                            ],
                            [
                                'node_id' => 'NODE_B1_020',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Tier 2 Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 22]
                            ],
                            [
                                'node_id' => 'NODE_B1_021',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Tier 3 Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 24]
                            ],
                            [
                                'node_id' => 'NODE_B1_022',
                                'sensor_type' => 'flow_meter',
                                'location' => 'Distribution Hub',
                                'status' => 'active',
                                'coordinates' => ['x' => 3, 'y' => 22]
                            ],
                            [
                                'node_id' => 'NODE_B1_023',
                                'sensor_type' => 'water_level',
                                'location' => 'Multi-Tier Collection',
                                'status' => 'active',
                                'coordinates' => ['x' => 48, 'y' => 22]
                            ],
                            [
                                'node_id' => 'NODE_B1_024',
                                'sensor_type' => 'plant_growth',
                                'location' => 'Growth Rate Monitor',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 25, 'y' => 22]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N005',
                        'line_name' => 'NFT Channel Bayam Express',
                        'line_type' => 'nft',
                        'plant_count' => 100,
                        'coverage_sqm' => 35.0,
                        'flow_rate_lpm' => 2.0,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_025',
                                'sensor_type' => 'fast_flow_meter',
                                'location' => 'Express Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 30]
                            ],
                            [
                                'node_id' => 'NODE_B1_026',
                                'sensor_type' => 'nutrient_concentration',
                                'location' => 'Concentration Monitor',
                                'status' => 'active',
                                'coordinates' => ['x' => 15, 'y' => 30]
                            ],
                            [
                                'node_id' => 'NODE_B1_027',
                                'sensor_type' => 'harvest_ready',
                                'location' => 'Harvest Indicator',
                                'status' => 'active',
                                'coordinates' => ['x' => 35, 'y' => 30]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N006',
                        'line_name' => 'NFT Channel Mixed Herbs',
                        'line_type' => 'nft',
                        'plant_count' => 60,
                        'coverage_sqm' => 30.0,
                        'flow_rate_lpm' => 1.8,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_028',
                                'sensor_type' => 'herb_aromatic',
                                'location' => 'Aroma Quality Monitor',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 35]
                            ],
                            [
                                'node_id' => 'NODE_B1_029',
                                'sensor_type' => 'nutrient_ph',
                                'location' => 'Herb-specific Nutrients',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 35]
                            ],
                            [
                                'node_id' => 'NODE_B1_030',
                                'sensor_type' => 'essential_oil',
                                'location' => 'Oil Content Monitor',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 25, 'y' => 35]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N007',
                        'line_name' => 'NFT Channel Research Line',
                        'line_type' => 'nft',
                        'plant_count' => 40,
                        'coverage_sqm' => 25.0,
                        'flow_rate_lpm' => 1.5,
                        'status' => 'maintenance',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_031',
                                'sensor_type' => 'research_multi',
                                'location' => 'Multi-Parameter Hub',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 15, 'y' => 40]
                            ],
                            [
                                'node_id' => 'NODE_B1_032',
                                'sensor_type' => 'data_logger',
                                'location' => 'Research Data Collection',
                                'status' => 'active',
                                'coordinates' => ['x' => 30, 'y' => 40]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'N008',
                        'line_name' => 'NFT Channel Backup System',
                        'line_type' => 'nft',
                        'plant_count' => 0,
                        'coverage_sqm' => 50.0,
                        'flow_rate_lpm' => 0.0,
                        'status' => 'inactive',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_B1_033',
                                'sensor_type' => 'system_monitor',
                                'location' => 'Backup System Status',
                                'status' => 'inactive',
                                'coordinates' => ['x' => 25, 'y' => 45]
                            ]
                        ]
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
                'total_lines' => 3,
                'area_size_sqm' => 200.00,
                'plant_types' => 'Tomat (Backup), Sayuran Emergency',
                'irrigation_system_type' => 'drip',
                'irrigation_lines' => [
                    [
                        'line_id' => 'LB001',
                        'line_name' => 'Emergency Line Alpha',
                        'line_type' => 'drip',
                        'plant_count' => 30,
                        'coverage_sqm' => 60.0,
                        'flow_rate_lpm' => 2.5,
                        'status' => 'active',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_BA_001',
                                'sensor_type' => 'emergency_flow',
                                'location' => 'Emergency Inlet',
                                'status' => 'active',
                                'coordinates' => ['x' => 5, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_BA_002',
                                'sensor_type' => 'backup_pressure',
                                'location' => 'Pressure Monitor',
                                'status' => 'active',
                                'coordinates' => ['x' => 15, 'y' => 5]
                            ],
                            [
                                'node_id' => 'NODE_BA_003',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Emergency Zone 1',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 10]
                            ],
                            [
                                'node_id' => 'NODE_BA_004',
                                'sensor_type' => 'soil_moisture',
                                'location' => 'Emergency Zone 2',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 20, 'y' => 10]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'LB002',
                        'line_name' => 'Emergency Line Beta',
                        'line_type' => 'sprinkler',
                        'plant_count' => 50,
                        'coverage_sqm' => 100.0,
                        'flow_rate_lpm' => 5.0,
                        'status' => 'maintenance',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_BA_005',
                                'sensor_type' => 'sprinkler_coverage',
                                'location' => 'Sprinkler Zone 1',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 5, 'y' => 20]
                            ],
                            [
                                'node_id' => 'NODE_BA_006',
                                'sensor_type' => 'sprinkler_coverage',
                                'location' => 'Sprinkler Zone 2',
                                'status' => 'maintenance',
                                'coordinates' => ['x' => 15, 'y' => 20]
                            ],
                            [
                                'node_id' => 'NODE_BA_007',
                                'sensor_type' => 'water_distribution',
                                'location' => 'Distribution Control',
                                'status' => 'active',
                                'coordinates' => ['x' => 10, 'y' => 25]
                            ]
                        ]
                    ],
                    [
                        'line_id' => 'LB003',
                        'line_name' => 'Emergency Line Gamma',
                        'line_type' => 'drip',
                        'plant_count' => 0,
                        'coverage_sqm' => 40.0,
                        'flow_rate_lpm' => 0.0,
                        'status' => 'inactive',
                        'nodes' => [
                            [
                                'node_id' => 'NODE_BA_008',
                                'sensor_type' => 'standby_monitor',
                                'location' => 'Standby Status',
                                'status' => 'inactive',
                                'coordinates' => ['x' => 25, 'y' => 15]
                            ]
                        ]
                    ]
                ]
            ]
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
