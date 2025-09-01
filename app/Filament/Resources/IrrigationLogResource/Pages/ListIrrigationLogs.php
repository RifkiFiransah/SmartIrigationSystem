<?php

namespace App\Filament\Resources\IrrigationLogResource\Pages;

use App\Filament\Resources\IrrigationLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIrrigationLogs extends ListRecords
{
    protected static string $resource = IrrigationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
