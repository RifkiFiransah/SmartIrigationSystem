<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SensorData;

class ClearSensorData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensor:clear {--confirm : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all sensor data from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('confirm')) {
            if (!$this->confirm('Are you sure you want to delete ALL sensor data? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Clearing sensor data...');
        
        try {
            $count = SensorData::count();
            SensorData::truncate();
            
            $this->info("Successfully cleared {$count} sensor data records.");
            
            // Reset auto increment
            DB::statement('ALTER TABLE sensor_data AUTO_INCREMENT = 1');
            $this->info('Auto increment reset.');
            
        } catch (\Exception $e) {
            $this->error('Failed to clear sensor data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
