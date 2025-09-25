<?php

namespace App\Observers;

use App\Models\IrrigationValveSchedule;

class IrrigationValveScheduleObserver
{
    /**
     * Handle the IrrigationValveSchedule "created" event.
     */
    public function created(IrrigationValveSchedule $irrigationValveSchedule): void
    {
        //
    }

    /**
     * Handle the IrrigationValveSchedule "updated" event.
     */
    public function updated(IrrigationValveSchedule $irrigationValveSchedule): void
    {
        //
    }

    /**
     * Handle the IrrigationValveSchedule "deleted" event.
     */
    public function deleted(IrrigationValveSchedule $irrigationValveSchedule): void
    {
        // Log schedule deletion
        try {
            $logService = app(\App\Services\IrrigationValveLogService::class);
            $logService->logScheduleAction('schedule_delete', 'admin_panel', $irrigationValveSchedule);
        } catch (\Throwable $e) {
            // Log error silently
            logger()->error('Failed to log schedule deletion', [
                'schedule_id' => $irrigationValveSchedule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the IrrigationValveSchedule "restored" event.
     */
    public function restored(IrrigationValveSchedule $irrigationValveSchedule): void
    {
        //
    }

    /**
     * Handle the IrrigationValveSchedule "force deleted" event.
     */
    public function forceDeleted(IrrigationValveSchedule $irrigationValveSchedule): void
    {
        //
    }
}
