<?php

namespace App\Filament\Resources\IrrigationControlResource\Pages;

use App\Filament\Resources\IrrigationControlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIrrigationControls extends ListRecords
{
    protected static string $resource = IrrigationControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
