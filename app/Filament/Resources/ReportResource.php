<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Device;
use Filament\Resources\Resource;

class ReportResource extends Resource
{
    protected static ?string $model = Device::class; // Using Device as dummy model

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Generate Laporan';
    protected static ?string $slug = 'reports';
    protected static ?int $navigationSort = 4;

    public static function getPages(): array
    {
        return [
            'index' => Pages\GenerateReport::route('/'),
        ];
    }
}
