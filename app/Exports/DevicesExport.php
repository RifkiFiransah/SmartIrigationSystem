<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DevicesExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $devices) {}

    public function collection(): Collection
    {
        return $this->devices->map(function ($d) {
            return [
                $d->id,
                $d->device_id,
                $d->device_name,
                $d->location,
                $d->is_active ? '1' : '0',
                $d->valve_state,
                $d->connection_state,
                $d->connection_state_source,
                optional($d->last_seen_at)->toDateTimeString(),
                optional($d->valve_state_changed_at)->toDateTimeString(),
                $d->description,
            ];
        });
    }

    public function headings(): array
    {
        return ['ID','Device Code','Name','Location','Is Active','Valve State','Conn State','Conn Source','Last Seen','Valve Changed At','Description'];
    }
}
