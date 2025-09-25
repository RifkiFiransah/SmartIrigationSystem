<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IrrigationControlsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $controls;

    public function __construct($controls)
    {
        $this->controls = $controls;
    }

    public function collection()
    {
        return $this->controls;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Device Name',
            'Mode',
            'Status',
            'Target Moisture (%)',
            'Duration (minutes)',
            'Start Time',
            'End Time',
            'Is Active',
            'Created At',
        ];
    }

    public function map($control): array
    {
        return [
            $control->id,
            $control->device_id,
            $control->device?->device_name ?? '',
            $control->mode,
            $control->status,
            $control->target_moisture_pct,
            $control->duration_minutes,
            $control->start_time,
            $control->end_time,
            $control->is_active ? 'Active' : 'Inactive',
            $control->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}