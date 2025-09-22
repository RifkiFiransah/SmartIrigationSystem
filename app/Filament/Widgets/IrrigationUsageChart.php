<?php

// namespace App\Filament\Widgets;

// use App\Models\SensorData;
// use Filament\Widgets\ChartWidget;
// use Illuminate\Support\Facades\Cache;

// class IrrigationUsageChart extends ChartWidget
// {
//     protected static ?string $heading = 'Irrigation Usage (Last 24h)';
//     protected static string $color = 'success';
//     protected int|string|array $columnSpan = 'full';

//     protected function getData(): array
//     {
//         $now = now();
//         $from = $now->copy()->subHours(24);

//         $cacheKey = 'chart:irrigation_usage:'.$from->timestamp;
//         $payload = Cache::remember($cacheKey, 30, function() use ($from, $now) {
//             $rows = SensorData::query()
//                 ->whereBetween('created_at', [$from, $now])
//                 ->orderBy('created_at')
//                 ->get(['created_at','irrigation_usage_total_l']);

//             if ($rows->isEmpty()) {
//                 // synthetic cumulative curve with small steps
//                 $labels = [];
//                 $cumulative = [];
//                 $delta = [];
//                 $total = 0.0;
//                 for ($i=0; $i<=24; $i++) {
//                     $labels[] = $from->copy()->addHours($i)->format('H:i');
//                     $inc = ($i % 3 === 0) ? (0.2 + mt_rand(0,10)/100) : (mt_rand(0,5)/100); // sporadic irrigation events
//                     $total += $inc;
//                     $cumulative[] = round($total, 3);
//                     $delta[] = round($inc, 3);
//                 }
//                 return [
//                     'labels' => $labels,
//                     'cumulative' => $cumulative,
//                     'delta' => $delta,
//                 ];
//             }

//             $labels = [];
//             $cumulative = [];
//             $delta = [];
//             $prev = null;
//             foreach ($rows as $row) {
//                 $labels[] = $row->created_at->setTimezone('Asia/Jakarta')->format('H:i');
//                 $cumulative[] = round($row->irrigation_usage_total_l, 3);
//                 if ($prev === null) {
//                     $delta[] = 0;
//                 } else {
//                     $d = max(0, $row->irrigation_usage_total_l - $prev);
//                     $delta[] = round($d, 3);
//                 }
//                 $prev = $row->irrigation_usage_total_l;
//             }
//             return [
//                 'labels' => $labels,
//                 'cumulative' => $cumulative,
//                 'delta' => $delta,
//             ];
//         });

//         return [
//             'labels' => $payload['labels'],
//             'datasets' => [
//                 [
//                     'type' => 'line',
//                     'label' => 'Cumulative (L)',
//                     'data' => $payload['cumulative'],
//                     'borderColor' => 'rgba(40,167,69,0.9)',
//                     'backgroundColor' => 'rgba(40,167,69,0.15)',
//                     'tension' => 0.25,
//                     'yAxisID' => 'y1',
//                     'pointRadius' => 2,
//                 ],
//                 [
//                     'type' => 'bar',
//                     'label' => 'Delta (L)',
//                     'data' => $payload['delta'],
//                     'backgroundColor' => 'rgba(0,123,255,0.5)',
//                     'borderColor' => 'rgba(0,123,255,0.9)',
//                     'borderWidth' => 1,
//                     'yAxisID' => 'y2',
//                 ],
//             ],
//         ];
//     }

//     protected function getType(): string
//     {
//         return 'bar'; // base type (mixed chart overrides per dataset)
//     }

//     protected function getOptions(): array
//     {
//         return [
//             'responsive' => true,
//             'maintainAspectRatio' => false,
//             'interaction' => [
//                 'mode' => 'index',
//                 'intersect' => false,
//             ],
//             'scales' => [
//                 'y1' => [
//                     'position' => 'left',
//                     'title' => ['display' => true, 'text' => 'Cumulative (L)'],
//                     'grid' => ['drawOnChartArea' => true],
//                 ],
//                 'y2' => [
//                     'position' => 'right',
//                     'title' => ['display' => true, 'text' => 'Delta (L)'],
//                     'grid' => ['drawOnChartArea' => false],
//                 ],
//                 'x' => [
//                     'ticks' => ['autoSkip' => true, 'maxTicksLimit' => 12],
//                 ],
//             ],
//             'plugins' => [
//                 'legend' => ['display' => true],
//                 'tooltip' => [
//                     'enabled' => false,
//                     'external' => \Filament\Support\RawJs::make(<<<'JS'
//                         function(context) {
//                             if (window.renderCustomLineTooltip) {
//                                 window.renderCustomLineTooltip(context, {
//                                     valueSuffix: ' L'
//                                 });
//                             }
//                         }
//                     JS),
//                     'callbacks' => [
//                         'label' => \Filament\Support\RawJs::make('function(item){return item.dataset.label+": "+item.formattedValue+" L";}'),
//                     ],
//                 ],
//             ],
//             'elements' => [
//                 'bar' => [ 'borderRadius' => 2 ],
//             ],
//         ];
//     }
// }
