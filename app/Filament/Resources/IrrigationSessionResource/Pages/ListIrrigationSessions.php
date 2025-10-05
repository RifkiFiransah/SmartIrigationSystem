<?php

namespace App\Filament\Resources\IrrigationSessionResource\Pages;

use App\Filament\Resources\IrrigationSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIrrigationSessions extends ListRecords
{
    protected static string $resource = IrrigationSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
