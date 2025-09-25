<?php

namespace App\Filament\Resources\IrrigationValveScheduleResource\Pages;

use App\Filament\Resources\IrrigationValveScheduleResource;
use Filament\Resources\Pages\EditRecord;

class EditIrrigationValveSchedule extends EditRecord
{
    protected static string $resource = IrrigationValveScheduleResource::class;

    protected function afterSave(): void
    {
        // Log schedule update
        try {
            $logService = app(\App\Services\IrrigationValveLogService::class);
            $logService->logScheduleAction('schedule_update', 'admin_panel', $this->record);
        } catch (\Throwable $e) {
            // Log error silently
            logger()->error('Failed to log schedule update', [
                'schedule_id' => $this->record->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
