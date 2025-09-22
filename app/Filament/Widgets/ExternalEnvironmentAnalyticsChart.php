<?php
// namespace App\Filament\Widgets;

// use App\Services\BMKGWeatherService;
// use Filament\Widgets\ChartWidget;
// use Illuminate\Support\Facades\App;

// class ExternalEnvironmentAnalyticsChart extends ChartWidget
// {
//     protected static ?string $heading = 'Analitik Lingkungan Eksternal (24h)';
//     protected int|string|array $columnSpan = 'full';

//     protected function getType(): string { return 'line'; }

//     protected function getData(): array
//     {
//         /** @var BMKGWeatherService $service */
//         $service = App::make(BMKGWeatherService::class);
//         $series = $service->getHourly(-6.2,106.8166,24);
//         $hours = $series['hours'] ?? [];
//         $labels=[]; $wind=[]; $lux=[];
//         foreach($hours as $row){
//             $ts = strtotime($row['time']);
//             $labels[] = date('H:i',$ts);
//             $wind[] = $row['wind_speed'];
//             $lux[] = $row['light_lux'];
//         }
//         $avgWind = count($wind)? array_sum($wind)/count($wind):0;
//         $avgLux = count($lux)? array_sum($lux)/count($lux):0;
//         $avgWindArr = array_fill(0, count($wind), round($avgWind,2));
//         $avgLuxArr = array_fill(0, count($lux), round($avgLux));

//         return [
//             'labels'=>$labels,
//             'datasets'=>[
//                 [ 'label'=>'Wind (m/s)', 'data'=>$wind, 'borderColor'=>'rgba(54,162,235,1)','backgroundColor'=>'rgba(54,162,235,0.08)','tension'=>0.35,'yAxisID'=>'y1','pointRadius'=>0 ],
//                 [ 'label'=>'Lux', 'data'=>$lux, 'borderColor'=>'rgba(255,193,7,1)','backgroundColor'=>'rgba(255,193,7,0.15)','tension'=>0.3,'yAxisID'=>'y2','pointRadius'=>0 ],
//                 [ 'label'=>'Avg Wind', 'data'=>$avgWindArr, 'borderColor'=>'rgba(54,162,235,0.4)','borderDash'=>[6,4],'pointRadius'=>0,'tension'=>0,'yAxisID'=>'y1' ],
//                 [ 'label'=>'Avg Lux', 'data'=>$avgLuxArr, 'borderColor'=>'rgba(255,193,7,0.4)','borderDash'=>[6,4],'pointRadius'=>0,'tension'=>0,'yAxisID'=>'y2' ],
//             ],
//         ];
//     }

//     protected function getOptions(): array
//     {
//         return [
//             'responsive'=>true,
//             'maintainAspectRatio'=>false,
//             'interaction'=>['mode'=>'index','intersect'=>false],
//             'scales'=>[
//                 'y1'=>['title'=>['display'=>true,'text'=>'Wind (m/s)'],'position'=>'left','grid'=>['drawOnChartArea'=>true]],
//                 'y2'=>['title'=>['display'=>true,'text'=>'Lux'],'position'=>'right','grid'=>['drawOnChartArea'=>false]],
//             ],
//             'plugins'=>[
//                 'legend'=>['display'=>true],
//                 'tooltip'=>[
//                     'enabled'=>false,
//                     'external'=> \Filament\Support\RawJs::make(<<<'JS'
//                         function(context){ if(window.renderCustomLineTooltip){ window.renderCustomLineTooltip(context, {}); } }
//                     JS)
//                 ],
//             ],
//             'elements'=>['point'=>['radius'=>0,'hitRadius'=>6]],
//         ];
//     }
// }
