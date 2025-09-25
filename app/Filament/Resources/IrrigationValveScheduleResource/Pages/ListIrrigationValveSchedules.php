<?php

namespace App\Filament\Resources\IrrigationValveScheduleResource\Pages;

use App\Filament\Resources\IrrigationValveScheduleResource;
use App\Models\IrrigationValveSchedule;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIrrigationValveSchedules extends ListRecords
{
    protected static string $resource = IrrigationValveScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('exportValveSchedulesExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $schedules = IrrigationValveSchedule::with(['valve'])->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\IrrigationValveSchedulesExport($schedules), 
                            'valve_schedules_' . now()->format('Ymd_His') . '.xlsx'
                        );
                    }),
                Actions\Action::make('exportValveSchedulesPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->action(function () {
                        $schedules = \App\Models\IrrigationValveSchedule::with(['valve'])->get();
                        $pdfService = app(\App\Services\PdfService::class);
                        return $pdfService->generatePdf('exports.valve-schedules-pdf', [
                            'schedules' => $schedules
                        ], 'valve_schedules_' . now()->format('Ymd_His') . '.pdf');
                    }),
            ])
            ->label('Export Data')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary'),
        ];
    }
}
