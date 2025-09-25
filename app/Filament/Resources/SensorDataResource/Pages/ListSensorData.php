<?php

namespace App\Filament\Resources\SensorDataResource\Pages;

use App\Filament\Resources\SensorDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSensorData extends ListRecords
{
    protected static string $resource = SensorDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('exportSensorDataExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $sensorData = \App\Models\SensorData::with('device')
                            ->orderBy('recorded_at', 'desc')
                            ->limit(10000)
                            ->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\SensorDataExport(collect($sensorData)), 
                            'sensor_data_' . now()->format('Ymd_His') . '.xlsx'
                        );
                    }),
                Actions\Action::make('exportSensorDataPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->action(function () {
                        $sensorData = \App\Models\EnvironmentReading::with('device')->orderBy('created_at', 'desc')->limit(1000)->get();
                        $pdfService = app(\App\Services\PdfService::class);
                        return $pdfService->generatePdf('exports.sensor-data-pdf', [
                            'sensorData' => $sensorData
                        ], 'sensor_data_' . now()->format('Ymd_His') . '.pdf');
                    }),
            ])
            ->label('Export Data')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary'),
        ];
    }
}
