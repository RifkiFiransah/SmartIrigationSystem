<?php

namespace App\Filament\Resources\WaterUsageLogResource\Pages;

use App\Filament\Resources\WaterUsageLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWaterUsageLog extends EditRecord
{
    protected static string $resource = WaterUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
