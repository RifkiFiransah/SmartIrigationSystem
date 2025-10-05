<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Exports\WaterStoragesExport;
use Maatwebsite\Excel\Facades\Excel;

class TestWaterStorageExport extends Command
{
    protected $signature = 'test:water-storage-export';
    protected $description = 'Test water storage export functionality';

    public function handle()
    {
        $this->info('Testing water storage export...');

        try {
            // Test database query (matching the export method)
            $this->info('1. Testing database query...');
            $waterStorages = DB::table('water_storages')
                ->select(
                    'id',
                    'tank_name',
                    'device_id',
                    'zone_name',
                    'capacity_liters',
                    'current_volume_liters',
                    'percentage',
                    'status',
                    'area_name',
                    'area_size_sqm',
                    'plant_types',
                    'height_cm',
                    'last_height_cm',
                    'max_daily_usage',
                    'created_at'
                )
                ->limit(5)
                ->get();

            $this->info('Query successful. Found ' . count($waterStorages) . ' water storage records.');

            if (count($waterStorages) > 0) {
                $this->info('Sample record:');
                $sample = $waterStorages->first();
                $this->table(['Column', 'Value'], [
                    ['ID', $sample->id],
                    ['Tank Name', $sample->tank_name],
                    ['Status', $sample->status],
                    ['Capacity', $sample->capacity_liters],
                    ['Current Volume', $sample->current_volume_liters],
                    ['Zone', $sample->zone_name ?? 'N/A'],
                ]);

                // Test Excel export class
                $this->info('2. Testing WaterStoragesExport class...');
                $export = new WaterStoragesExport(collect($waterStorages));
                $collection = $export->collection();
                $headings = $export->headings();
                
                $this->info('Export class working. Number of headings: ' . count($headings));
                $this->info('Headings: ' . implode(', ', array_slice($headings, 0, 5)) . '...');
                
                $firstRow = $collection->first();
                if ($firstRow) {
                    $this->info('Sample export row: ' . json_encode(array_slice($firstRow, 0, 5)) . '...');
                }

                $this->info('✅ All tests passed! Water storage export should work correctly.');
            } else {
                $this->warn('⚠️  No water storage data found. Please add some test data to verify export functionality.');
            }

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}