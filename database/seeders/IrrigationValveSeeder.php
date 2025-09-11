<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IrrigationValve;
use App\Models\WaterStorage;

class IrrigationValveSeeder extends Seeder
{
    public function run(): void
    {
        // Create or sync valves for each node in irrigation_lines
        $storages = WaterStorage::all();
        foreach ($storages as $storage) {
            $lines = $storage->irrigation_lines ?? [];
            foreach ($lines as $line) {
                $nodes = $line['nodes'] ?? [];
                foreach ($nodes as $node) {
                    $nodeUid = $node['node_id'] ?? null;
                    if (!$nodeUid) continue;

                    IrrigationValve::firstOrCreate(
                        ['node_uid' => $nodeUid],
                        [
                            'device_id' => $storage->device_id,
                            'gpio_pin' => null,
                            'status' => 'closed',
                            'mode' => 'manual',
                            'is_active' => true,
                            'description' => ($line['line_name'] ?? 'Line') . ' - ' . ($node['location'] ?? $nodeUid),
                        ]
                    );
                }
            }
        }
    }
}
