<?php

namespace App\Console\Commands;

use App\Models\WaterStorage;
use Illuminate\Console\Command;

class UpdateLegacyWaterStorageZones extends Command
{
    protected $signature = 'water-storage:update-zones';
    protected $description = 'Update legacy water storage records with zone information';

    public function handle()
    {
        $this->info('Updating legacy water storage records with zone information...');
        
        $legacyRecords = WaterStorage::whereNull('zone_name')->get();
        
        if ($legacyRecords->isEmpty()) {
            $this->info('No legacy records found to update.');
            return;
        }
        
        $updateCount = 0;
        
        foreach ($legacyRecords as $record) {
            // Update based on tank name pattern
            $zoneName = 'Legacy Zone - Mixed Area';
            $zoneDescription = 'Zona lama yang belum dikategorikan';
            $maxDailyUsage = 100; // Default usage
            
            // Try to guess zone based on tank name
            if (str_contains(strtolower($record->tank_name), 'greenhouse')) {
                $zoneName = 'Legacy Greenhouse Zone';
                $zoneDescription = 'Zona greenhouse legacy yang belum dikategorikan detail';
                $maxDailyUsage = 150;
            } elseif (str_contains(strtolower($record->tank_name), 'emergency')) {
                $zoneName = 'Emergency Reserve - All Zones';
                $zoneDescription = 'Tangki cadangan darurat untuk semua zona';
                $maxDailyUsage = 0;
            } elseif (str_contains(strtolower($record->tank_name), 'main')) {
                $zoneName = 'Main Agricultural Zone';
                $zoneDescription = 'Zona pertanian utama dengan berbagai jenis tanaman';
                $maxDailyUsage = 200;
            }
            
            $record->update([
                'zone_name' => $zoneName,
                'zone_description' => $zoneDescription,
                'max_daily_usage' => $maxDailyUsage,
                'associated_devices' => []
            ]);
            
            $updateCount++;
            $this->line("Updated: {$record->tank_name} -> {$zoneName}");
        }
        
        $this->info("Successfully updated {$updateCount} legacy records!");
        
        // Show summary
        $this->newLine();
        $this->info('Current zones summary:');
        $zones = WaterStorage::select('zone_name')->distinct()->get();
        foreach ($zones as $zone) {
            $tankCount = WaterStorage::where('zone_name', $zone->zone_name)->count();
            $this->line("- {$zone->zone_name}: {$tankCount} tanks");
        }
    }
}
