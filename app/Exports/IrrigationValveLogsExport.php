<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IrrigationValveLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $logs;

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Valve ID',
            'Device ID',
            'Action',
            'Old State',
            'New State',
            'Duration (min)',
            'Water Used (L)',
            'Timestamp',
            'Notes',
        ];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->valve_id,
            $log->valve?->device_id ?? '',
            $log->action,
            $log->old_state,
            $log->new_state,
            $log->duration_minutes,
            $log->water_used_liters,
            $log->created_at?->format('Y-m-d H:i:s'),
            $log->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}