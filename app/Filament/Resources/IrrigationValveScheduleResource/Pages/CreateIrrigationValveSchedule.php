<?php

namespace App\Filament\Resources\IrrigationValveScheduleResource\Pages;

use App\Filament\Resources\IrrigationValveScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIrrigationValveSchedule extends CreateRecord
{
    protected static string $resource = IrrigationValveScheduleResource::class;

    protected function afterCreate(): void
    {
        // Log schedule creation
        try {
            $logService = app(\App\Services\IrrigationValveLogService::class);
            $logService->logScheduleAction('schedule_create', 'admin_panel', $this->record);
        } catch (\Throwable $e) {
            // Log error silently
            logger()->error('Failed to log schedule creation', [
                'schedule_id' => $this->record->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
