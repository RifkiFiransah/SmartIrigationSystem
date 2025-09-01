<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\WaterStorage;

try {
    echo "Testing WaterStorage model...\n";
    
    $count = WaterStorage::count();
    echo "Total water storages: $count\n";
    
    if ($count > 0) {
        $storages = WaterStorage::with('device')->get();
        foreach ($storages as $storage) {
            echo "- {$storage->tank_name}: {$storage->current_volume}L / {$storage->total_capacity}L ({$storage->percentage}%)\n";
        }
    }
    
    echo "✅ WaterStorage model works correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
