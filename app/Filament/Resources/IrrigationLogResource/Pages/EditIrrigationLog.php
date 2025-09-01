<?php

namespace App\Filament\Resources\IrrigationLogResource\Pages;

use App\Filament\Resources\IrrigationLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIrrigationLog extends EditRecord
{
    protected static string $resource = IrrigationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
