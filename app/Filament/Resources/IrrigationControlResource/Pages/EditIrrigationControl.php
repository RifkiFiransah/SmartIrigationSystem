<?php

namespace App\Filament\Resources\IrrigationControlResource\Pages;

use App\Filament\Resources\IrrigationControlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIrrigationControl extends EditRecord
{
    protected static string $resource = IrrigationControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
