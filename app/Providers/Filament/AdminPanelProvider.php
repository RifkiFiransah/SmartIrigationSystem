<?php

namespace App\Providers\Filament;

use App\Filament\Resources\DeviceResource;
use App\Filament\Resources\IrrigationControlResource;
use App\Filament\Resources\SensorDataResource;
use App\Filament\Resources\IrrigationValveScheduleResource;
use App\Filament\Resources\WaterStorageResource;
use App\Filament\Widgets\SensorStatsOverview;
use App\Filament\Widgets\TemperatureChart;
use App\Filament\Widgets\SoilMoistureChart;
use App\Filament\Widgets\RealtimeStatusChart;
use App\Filament\Widgets\LightLuxChart;
use App\Filament\Widgets\WindSpeedChart;
use App\Filament\Widgets\WaterHeightChart;
use App\Filament\Widgets\Ina226MetricsChart;
use App\Filament\Widgets\ComprehensiveSensorChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                DeviceResource::class,
                IrrigationControlResource::class, // repurposed to Node Valves
                IrrigationValveScheduleResource::class,
                SensorDataResource::class,
                WaterStorageResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            // ->widgets([
            //     SensorStatsOverview::class,
            //     TemperatureChart::class,
            //     SoilMoistureChart::class,
            //     RealtimeStatusChart::class,
            //     LightLuxChart::class,
            //     WindSpeedChart::class,
            //     WaterHeightChart::class,
            //     Ina226MetricsChart::class,
            //     ComprehensiveSensorChart::class,
            // ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => '<script src="' . asset('js/alpine-fixes.js') . '"></script>'
            );
    }
}
