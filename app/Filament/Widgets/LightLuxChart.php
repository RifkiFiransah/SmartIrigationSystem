<?php
namespace App\Filament\Widgets;
use Filament\Widgets\ChartWidget;
class LightLuxChart extends ChartWidget { public static function canView(): bool { return false; } protected function getType(): string { return 'line'; } protected function getData(): array { return ['labels'=>[],'datasets'=>[]]; } protected function getOptions(): array { return []; } }
