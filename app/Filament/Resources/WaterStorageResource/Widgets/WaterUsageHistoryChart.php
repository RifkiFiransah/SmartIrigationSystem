<?php
namespace App\Filament\Resources\WaterStorageResource\Widgets;

use App\Models\WaterStorage;
use Filament\Widgets\ChartWidget;

class WaterUsageHistoryChart extends ChartWidget
{
    protected static ?string $heading = 'Riwayat Penggunaan Air (30 Hari)';
    protected static ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $storage = WaterStorage::first();
        if (!$storage) {
            return [ 'datasets' => [], 'labels' => [] ];
        }
        $rows = $storage->getDailyUsage(30);
        $labels = collect($rows)->pluck('date')->toArray();
        $values = collect($rows)->pluck('total_l')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Liter',
                    'data' => $values,
                    'backgroundColor' => 'rgba(59,130,246,0.4)',
                    'borderColor' => 'rgba(59,130,246,1)',
                    'tension' => 0.3,
                    'fill' => true,
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
