<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaterStorage;
use Illuminate\Http\Request;

class IrrigationLineController extends Controller
{
    /**
     * Get all irrigation lines grouped by area
     */
    public function getIrrigationLinesSummary()
    {
        try {
            $areas = WaterStorage::with('device')
                ->whereNotNull('irrigation_lines')
                ->get()
                ->groupBy('area_name')
                ->map(function ($tanks, $areaName) {
                    $firstTank = $tanks->first();
                    
                    // Combine all lines from all tanks in this area
                    $allLines = $tanks->flatMap(function ($tank) {
                        return collect($tank->irrigation_lines ?? [])->map(function ($line) use ($tank) {
                            $line['tank_name'] = $tank->tank_name;
                            $line['tank_id'] = $tank->id;
                            return $line;
                        });
                    });
                    
                    $activeLines = $allLines->where('status', 'active');
                    $totalPlants = $allLines->sum('plant_count');
                    $totalCoverage = $allLines->sum('coverage_sqm');
                    $totalFlowRate = $activeLines->sum('flow_rate_lpm');
                    
                    return [
                        'area_name' => $areaName,
                        'zone_name' => $firstTank->zone_name,
                        'area_description' => $firstTank->zone_description,
                        'plant_types' => $firstTank->plant_types,
                        'irrigation_system_type' => $firstTank->irrigation_system_type,
                        'area_size_sqm' => $firstTank->area_size_sqm,
                        'tanks_count' => $tanks->count(),
                        'total_lines' => $allLines->count(),
                        'active_lines' => $activeLines->count(),
                        'maintenance_lines' => $allLines->where('status', 'maintenance')->count(),
                        'inactive_lines' => $allLines->where('status', 'inactive')->count(),
                        'total_plants' => $totalPlants,
                        'total_coverage_sqm' => $totalCoverage,
                        'total_flow_rate_lpm' => $totalFlowRate,
                        'plant_density_per_sqm' => $totalCoverage > 0 ? round($totalPlants / $totalCoverage, 2) : 0,
                        'water_efficiency_lpm_per_plant' => $totalPlants > 0 ? round($totalFlowRate / $totalPlants, 3) : 0,
                        'lines' => $allLines->values(),
                        'tanks' => $tanks->map(function ($tank) {
                            return [
                                'id' => $tank->id,
                                'tank_name' => $tank->tank_name,
                                'capacity' => $tank->total_capacity,
                                'current' => $tank->current_volume,
                                'percentage' => $tank->percentage,
                                'status' => $tank->status,
                                'lines_count' => count($tank->irrigation_lines ?? []),
                            ];
                        })->values(),
                    ];
                });
                
            return response()->json([
                'success' => true,
                'data' => [
                    'areas' => $areas->values(),
                    'summary' => [
                        'total_areas' => $areas->count(),
                        'total_lines' => $areas->sum('total_lines'),
                        'total_active_lines' => $areas->sum('active_lines'),
                        'total_plants' => $areas->sum('total_plants'),
                        'total_coverage_sqm' => $areas->sum('total_coverage_sqm'),
                        'total_flow_rate_lpm' => $areas->sum('total_flow_rate_lpm'),
                        'average_plant_density' => $areas->avg('plant_density_per_sqm'),
                        'average_water_efficiency' => $areas->avg('water_efficiency_lpm_per_plant'),
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching irrigation lines: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get irrigation lines for specific area
     */
    public function getAreaIrrigationLines($areaName)
    {
        try {
            $tanks = WaterStorage::with('device')
                ->where('area_name', $areaName)
                ->get();
                
            if ($tanks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area not found'
                ], 404);
            }
            
            $area = $tanks->first();
            
            // Get all lines from all tanks in this area
            $allLines = $tanks->flatMap(function ($tank) {
                return collect($tank->irrigation_lines ?? [])->map(function ($line) use ($tank) {
                    return [
                        'line_id' => $line['line_id'],
                        'line_name' => $line['line_name'],
                        'line_type' => $line['line_type'],
                        'plant_count' => $line['plant_count'],
                        'coverage_sqm' => $line['coverage_sqm'],
                        'flow_rate_lpm' => $line['flow_rate_lpm'],
                        'status' => $line['status'],
                        'nodes' => $line['nodes'] ?? [],
                        'tank_name' => $tank->tank_name,
                        'tank_id' => $tank->id,
                        'tank_status' => $tank->status,
                        'tank_percentage' => $tank->percentage,
                    ];
                });
            });
            
            // Group lines by status and type
            $linesByStatus = $allLines->groupBy('status');
            $linesByType = $allLines->groupBy('line_type');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'area_info' => [
                        'area_name' => $areaName,
                        'zone_name' => $area->zone_name,
                        'area_description' => $area->zone_description,
                        'plant_types' => $area->plant_types,
                        'irrigation_system_type' => $area->irrigation_system_type,
                        'area_size_sqm' => $area->area_size_sqm,
                        'total_tanks' => $tanks->count(),
                    ],
                    'lines' => $allLines->values(),
                    'statistics' => [
                        'total_lines' => $allLines->count(),
                        'active_lines' => $linesByStatus->get('active', collect())->count(),
                        'maintenance_lines' => $linesByStatus->get('maintenance', collect())->count(),
                        'inactive_lines' => $linesByStatus->get('inactive', collect())->count(),
                        'total_plants' => $allLines->sum('plant_count'),
                        'total_coverage_sqm' => $allLines->sum('coverage_sqm'),
                        'total_flow_rate_lpm' => $allLines->where('status', 'active')->sum('flow_rate_lpm'),
                        'lines_by_type' => $linesByType->map(function ($lines) {
                            return $lines->count();
                        }),
                    ],
                    'tanks' => $tanks->map(function ($tank) {
                        return [
                            'id' => $tank->id,
                            'tank_name' => $tank->tank_name,
                            'capacity' => $tank->total_capacity,
                            'current' => $tank->current_volume,
                            'percentage' => $tank->percentage,
                            'status' => $tank->status,
                            'device_name' => $tank->device?->device_name,
                            'lines_count' => count($tank->irrigation_lines ?? []),
                        ];
                    })->values(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching area irrigation lines: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get line efficiency analytics
     */
    public function getLineEfficiencyAnalytics()
    {
        try {
            $allTanks = WaterStorage::whereNotNull('irrigation_lines')->get();
            
            $analytics = [];
            
            foreach ($allTanks as $tank) {
                foreach ($tank->irrigation_lines ?? [] as $line) {
                    if ($line['status'] === 'active' && $line['plant_count'] > 0) {
                        $waterPerPlant = $line['flow_rate_lpm'] / $line['plant_count'];
                        $plantsPerSqm = $line['coverage_sqm'] > 0 ? $line['plant_count'] / $line['coverage_sqm'] : 0;
                        
                        $analytics[] = [
                            'area_name' => $tank->area_name,
                            'tank_name' => $tank->tank_name,
                            'line_id' => $line['line_id'],
                            'line_name' => $line['line_name'],
                            'line_type' => $line['line_type'],
                            'plant_count' => $line['plant_count'],
                            'coverage_sqm' => $line['coverage_sqm'],
                            'flow_rate_lpm' => $line['flow_rate_lpm'],
                            'water_per_plant_lpm' => round($waterPerPlant, 3),
                            'plants_per_sqm' => round($plantsPerSqm, 2),
                            'efficiency_score' => $this->calculateEfficiencyScore($waterPerPlant, $plantsPerSqm, $line['line_type']),
                        ];
                    }
                }
            }
            
            // Sort by efficiency score
            usort($analytics, function ($a, $b) {
                return $b['efficiency_score'] <=> $a['efficiency_score'];
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'line_analytics' => $analytics,
                    'summary' => [
                        'total_active_lines' => count($analytics),
                        'average_efficiency' => count($analytics) > 0 ? round(array_sum(array_column($analytics, 'efficiency_score')) / count($analytics), 2) : 0,
                        'best_performing_line' => $analytics[0] ?? null,
                        'improvement_needed' => array_filter($analytics, function ($line) {
                            return $line['efficiency_score'] < 60;
                        }),
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating efficiency analytics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function calculateEfficiencyScore($waterPerPlant, $plantsPerSqm, $lineType)
    {
        $score = 100;
        
        // Optimal water per plant based on type
        $optimalWater = match ($lineType) {
            'drip' => 0.05, // 0.05 L/min per plant
            'nft' => 0.03,  // 0.03 L/min per plant
            'sprinkler' => 0.08, // 0.08 L/min per plant
            'misting' => 0.01,  // 0.01 L/min per plant
            default => 0.05
        };
        
        // Penalty for water usage deviation
        $waterDeviation = abs($waterPerPlant - $optimalWater) / $optimalWater;
        $score -= min($waterDeviation * 30, 40);
        
        // Bonus for good plant density
        if ($plantsPerSqm >= 3 && $plantsPerSqm <= 8) {
            $score += 10;
        } elseif ($plantsPerSqm < 1) {
            $score -= 20;
        }
        
        return max(0, min(100, round($score)));
    }
    
    /**
     * Get detailed information about a specific irrigation line including nodes
     */
    public function getLineDetails($lineId)
    {
        try {
            $waterStorage = null;
            $targetLine = null;
            
            // Find the water storage that contains this line
            $waterStorages = WaterStorage::with('device')->get();
            
            foreach ($waterStorages as $storage) {
                if (!empty($storage->irrigation_lines)) {
                    foreach ($storage->irrigation_lines as $line) {
                        if ($line['line_id'] === $lineId) {
                            $waterStorage = $storage;
                            $targetLine = $line;
                            break 2;
                        }
                    }
                }
            }
            
            if (!$waterStorage || !$targetLine) {
                return response()->json([
                    'success' => false,
                    'message' => 'Irrigation line not found'
                ], 404);
            }
            
            // Calculate efficiency metrics
            $waterPerPlant = $targetLine['plant_count'] > 0 ? $targetLine['flow_rate_lpm'] / $targetLine['plant_count'] : 0;
            $plantsPerSqm = $targetLine['coverage_sqm'] > 0 ? $targetLine['plant_count'] / $targetLine['coverage_sqm'] : 0;
            $efficiencyScore = $this->calculateEfficiencyScore(
                $waterPerPlant,
                $plantsPerSqm,
                $targetLine['line_type']
            );
            
            // Get nodes information
            $nodes = isset($targetLine['nodes']) ? $targetLine['nodes'] : [];
            $activeNodes = array_filter($nodes, function($node) {
                return isset($node['status']) && $node['status'] === 'active';
            });
            
            // Calculate node statistics
            $nodeStats = [
                'total_nodes' => count($nodes),
                'active_nodes' => count($activeNodes),
                'maintenance_nodes' => count(array_filter($nodes, function($node) {
                    return isset($node['status']) && $node['status'] === 'maintenance';
                })),
                'inactive_nodes' => count(array_filter($nodes, function($node) {
                    return isset($node['status']) && $node['status'] === 'inactive';
                })),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'line_info' => [
                        'line_id' => $targetLine['line_id'],
                        'line_name' => $targetLine['line_name'],
                        'line_type' => $targetLine['line_type'],
                        'status' => $targetLine['status'],
                        'plant_count' => $targetLine['plant_count'],
                        'coverage_sqm' => $targetLine['coverage_sqm'],
                        'flow_rate_lpm' => $targetLine['flow_rate_lpm'],
                        'water_per_plant_lpm' => round($waterPerPlant, 3),
                        'plants_per_sqm' => round($plantsPerSqm, 1),
                        'efficiency_score' => $efficiencyScore,
                    ],
                    'area_info' => [
                        'area_name' => $waterStorage->area_name,
                        'zone_name' => $waterStorage->zone_name,
                        'tank_name' => $waterStorage->tank_name,
                        'device_name' => $waterStorage->device ? $waterStorage->device->device_name : null,
                        'irrigation_system_type' => $waterStorage->irrigation_system_type,
                        'plant_types' => $waterStorage->plant_types,
                        'area_size_sqm' => $waterStorage->area_size_sqm,
                    ],
                    'nodes' => array_merge($nodeStats, [
                        'node_list' => $nodes,
                    ]),
                    'performance_metrics' => [
                        'efficiency_rating' => $efficiencyScore >= 80 ? 'Excellent' : ($efficiencyScore >= 70 ? 'Good' : ($efficiencyScore >= 60 ? 'Fair' : 'Needs Improvement')),
                        'water_consumption_rating' => $waterPerPlant <= 0.04 ? 'Efficient' : ($waterPerPlant <= 0.06 ? 'Moderate' : 'High'),
                        'plant_density_rating' => $plantsPerSqm >= 2 ? 'High Density' : ($plantsPerSqm >= 1 ? 'Medium Density' : 'Low Density'),
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get line details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
