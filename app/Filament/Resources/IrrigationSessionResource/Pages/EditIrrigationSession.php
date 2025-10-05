<?php

namespace App\Filament\Resources\IrrigationSessionResource\Pages;

use App\Filament\Resources\IrrigationSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIrrigationSession extends EditRecord
{
    protected static string $resource = IrrigationSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
