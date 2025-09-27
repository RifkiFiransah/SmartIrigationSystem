<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        \App\Models\IrrigationValveSchedule::observe(\App\Observers\IrrigationValveScheduleObserver::class);
        
        // Configure Filament to avoid Alpine.js issues
        if (class_exists(\Filament\FilamentServiceProvider::class)) {
            // Force UTF-8 encoding for all HTML responses
            \Illuminate\Support\Facades\Response::macro('html', function ($content) {
                return \Illuminate\Support\Facades\Response::make($content)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('X-Content-Type-Options', 'nosniff');
            });
        }
    }
}
