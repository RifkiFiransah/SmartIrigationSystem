<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class WeatherOverviewChart extends ChartWidget
{
    protected static ?string $heading = 'Deprecated Weather Overview';
    public static function canView(): bool { return false; }
    protected function getType(): string { return 'line'; }
    protected function getData(): array { return ['labels'=>[],'datasets'=>[]]; }
    protected function getOptions(): array { return []; }
}