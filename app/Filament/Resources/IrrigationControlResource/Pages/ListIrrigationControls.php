<?php

namespace App\Filament\Resources\IrrigationControlResource\Pages;

use App\Filament\Resources\IrrigationControlResource;
use App\Models\Device;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;

class ListIrrigationControls extends Page
{
    protected static string $resource = IrrigationControlResource::class;
    protected static string $view = 'filament.resources.irrigation-control-resource.pages.list-irrigation-controls';
    
    protected $listeners = ['refreshNodes' => '$refresh'];

    public function toggleConnection($deviceId, $state)
    {
        $device = Device::find($deviceId);
        if ($device) {
            $device->setConnectionState($state, 'manual');
            
            Notification::make()
                ->title('Connection Status Updated')
                ->body("Device {$device->device_name} is now " . ucfirst($state))
                ->success()
                ->send();
                
            $this->dispatch('refreshNodes');
        }
    }

    public function toggleValve($deviceId)
    {
        $device = Device::find($deviceId);
        if ($device) {
            $oldState = $device->valve_state;
            $device->toggleValve();
            $newState = $device->fresh()->valve_state;
            
            Notification::make()
                ->title('Valve Status Updated')
                ->body("Device {$device->device_name} valve is now " . ucfirst($newState))
                ->success()
                ->send();
                
            $this->dispatch('refreshNodes');
        }
    }
}
