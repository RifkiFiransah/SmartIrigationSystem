<?php

namespace App\Filament\Resources\IrrigationControlResource\Pages;

use App\Filament\Resources\IrrigationControlResource;
use App\Models\IrrigationValveSchedule;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIrrigationControl extends CreateRecord
{
    protected static string $resource = IrrigationControlResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $schedules = $data['schedules'] ?? [];
        if (!is_array($schedules) || empty($schedules)) {
            return;
        }

        $valve = $this->record; // created IrrigationValve
        foreach ($schedules as $row) {
            if (empty($row['start_time']) || empty($row['duration_minutes'])) {
                continue;
            }
            IrrigationValveSchedule::create([
                'node_uid' => $valve->node_uid,
                'start_time' => $row['start_time'],
                'duration_minutes' => (int) $row['duration_minutes'],
                'days_of_week' => isset($row['days_of_week']) && is_array($row['days_of_week']) ? array_values($row['days_of_week']) : null,
                'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
            ]);
        }
    }
}
