<?php

namespace App\Filament\Resources\WaterStorageResource\Widgets;

use App\Models\WaterStorage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WaterStorageOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTanks = WaterStorage::count();
        $totalCapacity = WaterStorage::sum('total_capacity');
        $totalCurrentVolume = WaterStorage::sum('current_volume');
        $averagePercentage = $totalCapacity > 0 ? round(($totalCurrentVolume / $totalCapacity) * 100, 1) : 0;
        
        $lowTanks = WaterStorage::whereRaw('(current_volume / total_capacity) * 100 <= 25')->count();
        $emptyTanks = WaterStorage::whereRaw('(current_volume / total_capacity) * 100 <= 10')->count();

        return [
            Stat::make('Total Tanks', $totalTanks)
                ->description('Number of water storage tanks')
                ->icon('heroicon-o-beaker')
                ->color('primary'),
                
            Stat::make('Total Capacity', number_format($totalCapacity, 1) . ' L')
                ->description('Combined capacity of all tanks')
                ->icon('heroicon-o-inbox-stack')
                ->color('info'),
                
            Stat::make('Current Volume', number_format($totalCurrentVolume, 1) . ' L')
                ->description($averagePercentage . '% of total capacity')
                ->icon('heroicon-o-cube')
                ->color($averagePercentage >= 50 ? 'success' : ($averagePercentage >= 25 ? 'warning' : 'danger')),
                
            Stat::make('Low Water Tanks', $lowTanks)
                ->description('Tanks with ≤25% water level')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($lowTanks > 0 ? 'warning' : 'success'),
                
            Stat::make('Empty Tanks', $emptyTanks)
                ->description('Tanks with ≤10% water level')
                ->icon('heroicon-o-x-circle')
                ->color($emptyTanks > 0 ? 'danger' : 'success'),
        ];
    }
}
