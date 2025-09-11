<?php
namespace App\Filament\Resources\WaterStorageResource\Widgets;

use App\Models\WaterUsageLog;
use App\Models\WaterStorage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class WaterUsageSummary extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $storage = WaterStorage::first();
        if (!$storage) {
            return [
                Stat::make('Penggunaan 7 Hari', '0 L'),
                Stat::make('Rata-rata / Hari', '0 L'),
                Stat::make('Terakhir', '—'),
            ];
        }

        $since = now()->subDays(7)->toDateString();
        $logs7d = $storage->usageLogs()->where('usage_date', '>=', $since)->get();
        $total7d = (float) $logs7d->sum('volume_used_l');
        $avg7d = $total7d / max($logs7d->unique('usage_date')->count(), 1);
        $last = $storage->usageLogs()->latest()->first();

        return [
            Stat::make('Penggunaan 7 Hari', number_format($total7d, 2).' L')
                ->description('Total pemakaian')
                ->color('primary'),
            Stat::make('Rata-rata / Hari', number_format($avg7d, 2).' L')
                ->description('Rata-rata 7 hari')
                ->color('info'),
            Stat::make('Terakhir', $last ? number_format($last->volume_used_l, 2).' L' : '—')
                ->description($last ? $last->usage_date->toDateString() : 'Belum ada')
                ->color('success'),
        ];
    }
}
