<?php
// namespace App\Filament\Widgets;

// use App\Services\BMKGWeatherService;
// use Filament\Widgets\ChartWidget;
// use Illuminate\Support\Facades\App;

// class ExternalEnvironmentChart extends ChartWidget
// {
//     protected static ?string $heading = 'Lingkungan Eksternal (Wind & Lux 24h)';
//     protected int|string|array $columnSpan = 'full';

//     protected function getType(): string { return 'line'; }

//     protected function getData(): array
//     {
//         /** @var BMKGWeatherService $service */
//         $service = App::make(BMKGWeatherService::class);
//         $series = $service->getHourly(-6.2, 106.8166, 24);
//         $hours = $series['hours'] ?? [];
//         $labels=[]; $wind=[]; $lux=[];
//         foreach ($hours as $row) {
//             $labels[] = date('H:i', strtotime($row['time']));
//             $wind[] = $row['wind_speed'];
//             $lux[] = $row['light_lux'];
//         }
//         return [
//             'labels' => $labels,
//             'datasets' => [
//                 [
//                     'label' => 'Wind (m/s)',
//                     'data' => $wind,
//                     'borderColor' => 'rgba(54,162,235,1)',
//                     'backgroundColor' => 'rgba(54,162,235,0.1)',
//                     'tension' => 0.35,
//                     'yAxisID' => 'y1',
//                     'pointRadius' => 0,
//                 ],
//                 [
//                     'label' => 'Lux',
//                     'data' => $lux,
//                     'borderColor' => 'rgba(255,193,7,1)',
//                     'backgroundColor' => 'rgba(255,193,7,0.2)',
//                     'tension' => 0.3,
//                     'yAxisID' => 'y2',
//                     'pointRadius' => 0,
//                 ],
//             ],
//         ];
//     }

//     protected function getOptions(): array
//     {
//         return [
//             'responsive' => true,
//             'maintainAspectRatio' => false,
//             'interaction' => [ 'mode' => 'index', 'intersect' => false ],
//             'scales' => [
//                 'y1' => [ 'title'=>['display'=>true,'text'=>'Wind (m/s)'], 'position'=>'left', 'grid'=>['drawOnChartArea'=>true] ],
//                 'y2' => [ 'title'=>['display'=>true,'text'=>'Lux'], 'position'=>'right', 'grid'=>['drawOnChartArea'=>false] ],
//             ],
//             'plugins' => [
//                 'legend' => ['display'=>true],
//                 'tooltip' => [
//                     'enabled' => false,
//                     'external' => \Filament\Support\RawJs::make(<<<'JS'
//                         function(context){ if(window.renderCustomLineTooltip){ window.renderCustomLineTooltip(context, {}); } }
//                     JS)
//                 ],
//             ],
//             'elements' => [ 'point' => [ 'radius'=>0, 'hitRadius'=>6 ] ],
//         ];
//     }
// }
