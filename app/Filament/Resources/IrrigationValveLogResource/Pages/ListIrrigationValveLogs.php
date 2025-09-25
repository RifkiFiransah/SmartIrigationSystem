<?php

namespace App\Filament\Resources\IrrigationValveLogResource\Pages;

use App\Filament\Resources\IrrigationValveLogResource;
use App\Models\IrrigationValveLog;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIrrigationValveLogs extends ListRecords
{
    protected static string $resource = IrrigationValveLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('exportValveLogsExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $logs = IrrigationValveLog::with(['valve'])->orderBy('created_at', 'desc')->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\IrrigationValveLogsExport($logs), 
                            'valve_logs_' . now()->format('Ymd_His') . '.xlsx'
                        );
                    }),
                Actions\Action::make('exportValveLogsPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->action(function () {
                        $logs = \App\Models\IrrigationValveLog::with(['valve'])->orderBy('created_at', 'desc')->get();
                        $pdfService = app(\App\Services\PdfService::class);
                        return $pdfService->generatePdf('exports.valve-logs-pdf', [
                            'logs' => $logs
                        ], 'valve_logs_' . now()->format('Ymd_His') . '.pdf');
                    }),
            ])
            ->label('Export Data')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary'),
        ];
    }
}
