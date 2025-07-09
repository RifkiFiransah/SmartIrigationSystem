<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use App\Models\Device;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SensorStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $latestData = SensorData::latest('recorded_at')->first();
        $activeDevices = Device::where('is_active', true)->count();
        $criticalAlerts = SensorData::where('status', 'critical')
            ->where('recorded_at', '>=', now()->subHours(24))
            ->count();

        return [
            // Stat::make('Device Aktif', $activeDevices)
            //     ->description('Total device yang aktif')
            //     ->descriptionIcon('heroicon-m-cpu-chip')
            //     ->color('success'),

            Stat::make('Suhu Terkini', $latestData ? number_format($latestData->temperature, 1) . '°C' : 'N/A')
                ->description($latestData ? 'Terakhir: ' . $latestData->recorded_at->diffForHumans() : 'Tidak ada data')
                ->descriptionIcon('heroicon-m-fire')
                ->color($latestData && $latestData->temperature > 35 ? 'danger' : 'primary'),

            Stat::make('Kelembaban Tanah', $latestData ? number_format($latestData->soil_moisture, 1) . '%' : 'N/A')
                ->description($latestData ? 'Status: ' . ucfirst($latestData->status) : 'Tidak ada data')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color($latestData && $latestData->soil_moisture < 25 ? 'danger' : 'success'),

            Stat::make('Debit Air', $latestData ? number_format($latestData->water_flow, 1) . ' L/j' : 'N/A')
                ->description($latestData ? 'Terakhir: ' . $latestData->recorded_at->diffForHumans() : 'Tidak ada data')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($latestData && $latestData->water_flow < 25 ? 'danger' : 'success'),

            // Stat::make('Alert Kritis (24j)', $criticalAlerts)
            //     ->description('Alert dalam 24 jam terakhir')
            //     ->descriptionIcon('heroicon-m-exclamation-triangle')
            //     ->color($criticalAlerts > 0 ? 'danger' : 'success'),
        ];
    }
}
