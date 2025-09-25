<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Models\Device;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('exportDevicesExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $devices = \App\Models\Device::all();
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\DevicesExport($devices), 
                            'devices_' . date('Ymd_His') . '.xlsx'
                        );
                    }),
                Actions\Action::make('exportDevicesPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->action(function () {
                        $devices = \App\Models\Device::all();
                        $pdfService = app(\App\Services\PdfService::class);
                        return $pdfService->generatePdf('exports.devices-pdf', [
                            'devices' => $devices
                        ], 'devices_' . date('Ymd_His') . '.pdf');
                    }),
            ])
            ->label('Export Data')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary'),
        ];
    }


}
