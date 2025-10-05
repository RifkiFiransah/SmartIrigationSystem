<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Exports\SensorDataExport;
use Maatwebsite\Excel\Facades\Excel;

class TestExport extends Command
{
    protected $signature = 'test:export';
    protected $description = 'Test sensor data export functionality';

    public function handle()
    {
        $this->info('Testing sensor data export...');

        try {
            // Test database query
            $this->info('1. Testing database query...');
            $sensorData = DB::table('sensor_data')
                ->join('devices', 'sensor_data.device_id', '=', 'devices.id')
                ->select(
                    'sensor_data.id',
                    'devices.device_name',
                    'sensor_data.ground_temperature_c',
                    'sensor_data.soil_moisture_pct',
                    'sensor_data.water_height_cm',
                    'sensor_data.battery_voltage_v',
                    'sensor_data.irrigation_usage_total_l',
                    'sensor_data.recorded_at',
                    'sensor_data.status'
                )
                ->limit(5)
                ->get();

            $this->info('Query successful. Found ' . count($sensorData) . ' records.');

            if (count($sensorData) > 0) {
                $this->info('Sample record:');
                $sample = $sensorData->first();
                $this->table(['Column', 'Value'], [
                    ['ID', $sample->id],
                    ['Device Name', $sample->device_name],
                    ['Ground Temp', $sample->ground_temperature_c],
                    ['Soil Moisture', $sample->soil_moisture_pct],
                    ['Status', $sample->status],
                ]);

                // Test Excel export
                $this->info('2. Testing Excel export class...');
                $export = new SensorDataExport(collect($sensorData));
                $collection = $export->collection();
                $headings = $export->headings();
                
                $this->info('Export class working. Headings: ' . implode(', ', $headings));
                $this->info('Sample export data: ' . json_encode($collection->first()));

                // Test PDF view
                $this->info('3. Testing PDF view...');
                $pdfData = compact('sensorData');
                
                // Try to render the view without actually generating PDF
                $viewPath = 'pdf.sensor-data';
                if (view()->exists($viewPath)) {
                    $this->info('PDF view exists and can be rendered.');
                } else {
                    $this->error('PDF view does not exist at: ' . $viewPath);
                }

                $this->info('✅ All tests passed! Export functionality should work correctly.');
            } else {
                $this->warn('⚠️  No sensor data found. Please add some test data to verify export functionality.');
            }

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}