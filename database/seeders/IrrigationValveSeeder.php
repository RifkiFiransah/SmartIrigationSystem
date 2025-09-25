<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IrrigationValve;
use App\Models\Device;

class IrrigationValveSeeder extends Seeder
{
    public function run(): void
    {
        // Create exactly 12 irrigation valves, one for each device (1:1 relationship)
        $devices = Device::all();
        
        foreach ($devices as $device) {
            // Generate unique node UID for each valve
            $nodeUid = 'NODE-' . strtoupper(substr(md5($device->id . $device->device_name), 0, 6));
            
            // Determine valve description based on device location
            $description = $this->getValveDescription($device);
            
            IrrigationValve::firstOrCreate(
                ['node_uid' => $nodeUid],
                [
                    'device_id' => $device->id,
                    'gpio_pin' => 2, // Default GPIO pin
                    'status' => 'closed',
                    'mode' => 'manual',
                    'is_active' => true,
                    'description' => $description,
                ]
            );
        }
    }

    private function getValveDescription(Device $device): string
    {
        // Create meaningful descriptions based on device location
        $location = strtolower($device->location ?? '');
        
        if (str_contains($location, 'greenhouse')) {
            return 'Greenhouse Irrigation Valve';
        } elseif (str_contains($location, 'outdoor')) {
            return 'Outdoor Irrigation Valve';
        } elseif (str_contains($location, 'nursery')) {
            return 'Nursery Irrigation Valve';
        } elseif (str_contains($location, 'research') || str_contains($location, 'percobaan')) {
            return 'Research Plot Irrigation Valve';
        } elseif (str_contains($location, 'reservoir') || str_contains($location, 'pompa')) {
            return 'Water System Control Valve';
        } else {
            return 'Main Irrigation Valve';
        }
    }
}
