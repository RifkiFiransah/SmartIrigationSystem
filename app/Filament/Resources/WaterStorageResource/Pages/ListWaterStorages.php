<?php

namespace App\Filament\Resources\WaterStorageResource\Pages;

use App\Filament\Resources\WaterStorageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaterStorages extends ListRecords
{
    protected static string $resource = WaterStorageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WaterStorageResource\Widgets\WaterStorageOverview::class,
        ];
    }
}
