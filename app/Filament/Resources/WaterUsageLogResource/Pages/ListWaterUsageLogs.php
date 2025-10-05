<?php

namespace App\Filament\Resources\WaterUsageLogResource\Pages;

use App\Filament\Resources\WaterUsageLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaterUsageLogs extends ListRecords
{
    protected static string $resource = WaterUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
