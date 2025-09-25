<?php

namespace App\Filament\Resources\WaterStorageResource\Pages;

use App\Filament\Resources\WaterStorageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaterStorages extends ListRecords
{
    protected static string $resource = WaterStorageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('exportWaterStoragesExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $storages = \App\Models\WaterStorage::all();
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\WaterStoragesExport($storages), 
                            'water_storages_' . now()->format('Ymd_His') . '.xlsx'
                        );
                    }),
                Actions\Action::make('exportWaterStoragesPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->action(function () {
                        $waterStorages = \App\Models\WaterStorage::all();
                        $pdfService = app(\App\Services\PdfService::class);
                        return $pdfService->generatePdf('exports.water-storages-pdf', [
                            'waterStorages' => $waterStorages
                        ], 'water_storages_' . now()->format('Ymd_His') . '.pdf');
                    }),
            ])
            ->label('Export Data')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WaterStorageResource\Widgets\WaterStorageOverview::class,
        ];
    }
}
