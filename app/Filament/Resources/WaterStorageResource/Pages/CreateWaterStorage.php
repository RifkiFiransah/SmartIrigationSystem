<?php

namespace App\Filament\Resources\WaterStorageResource\Pages;

use App\Filament\Resources\WaterStorageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWaterStorage extends CreateRecord
{
    protected static string $resource = WaterStorageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
