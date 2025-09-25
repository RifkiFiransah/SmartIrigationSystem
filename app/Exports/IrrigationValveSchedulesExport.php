<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IrrigationValveSchedulesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $schedules;

    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }

    public function collection()
    {
        return $this->schedules;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Valve ID',
            'Device ID',
            'Schedule Name',
            'Start Time',
            'End Time',
            'Duration (min)',
            'Days of Week',
            'Is Active',
            'Created At',
        ];
    }

    public function map($schedule): array
    {
        return [
            $schedule->id,
            $schedule->valve_id,
            $schedule->valve?->device_id ?? '',
            $schedule->schedule_name ?? '',
            $schedule->start_time,
            $schedule->end_time,
            $schedule->duration_minutes,
            $schedule->days_of_week,
            $schedule->is_active ? 'Active' : 'Inactive',
            $schedule->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}