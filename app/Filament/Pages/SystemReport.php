<?php

namespace App\Filament\Pages;

use App\Models\Device;
use App\Services\SystemReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SummaryReportExport;
use App\Exports\DevicesExport;
use App\Exports\WaterStoragesExport;
use App\Exports\SensorDataExport;
use App\Exports\WaterUsageLogsExport;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SystemReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 11;
    protected static string $view = 'filament.pages.system-report';
    protected static ?string $title = 'Laporan Sistem (Advanced)';

    public ?array $filters = [];
    public array $rows = [];
    public array $summary = [];
    public bool $generated = false;
    public array $chartData = [];
    
    // Form properties for validation
    public $date_from;
    public $date_to;
    public $device_ids = [];
    public $only_active = false;

    public function mount(): void
    {
        $this->date_from = Carbon::now()->subDays(6)->startOfDay(); // 7 hari termasuk hari ini
        $this->date_to = Carbon::now()->endOfDay();
        $this->device_ids = [];
        $this->only_active = false;
        
        $this->filters = [
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'device_ids' => $this->device_ids,
            'only_active' => $this->only_active,
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\DatePicker::make('date_from')->label('Dari Tanggal')->maxDate(fn ($get) => $get('date_to')),
            Forms\Components\DatePicker::make('date_to')->label('Sampai Tanggal')->minDate(fn ($get) => $get('date_from')),
            Forms\Components\Select::make('device_ids')
                ->label('Device')
                ->multiple()
                ->options(function (callable $get) {
                    $q = Device::query()->orderBy('device_name');
                    if ($get('only_active')) {
                        $q->where('is_active', true);
                    }
                    return $q->pluck('device_name', 'id');
                }),
            Forms\Components\Toggle::make('only_active')
                ->label('Hanya Device Aktif')
                ->inline(false),
        ];
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        $from = Carbon::parse($data['date_from'])->startOfDay();
        $to = Carbon::parse($data['date_to'])->endOfDay();
        $deviceIds = array_filter($data['device_ids'] ?? []);
        $onlyActive = (bool)($data['only_active'] ?? false);
        
        // Update filters for export methods
        $this->filters = [
            'date_from' => $from,
            'date_to' => $to,
            'device_ids' => $deviceIds,
            'only_active' => $onlyActive,
        ];

        if ($from->gt($to)) {
            Notification::make()->danger()->title('Range tanggal tidak valid')->send();
            return;
        }

        $cacheKey = 'system_report:' . md5(json_encode([$from, $to, $deviceIds, $onlyActive]));
        $service = app(SystemReportService::class);
        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($service, $from, $to, $deviceIds, $onlyActive) {
            return $service->buildAggregation($from, $to, $deviceIds, $onlyActive);
        });

        $this->rows = $result['rows'];
        $this->summary = $result['summary'];
        $this->chartData = $this->buildChartData($result['rows']);
        $this->generated = true;

        Notification::make()->success()->title('Laporan berhasil dibuat')->send();
    }

    // Aggregation moved to service

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! $this->generated) {
            $this->generate();
        }

        $filename = 'system_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'tanggal','device_name','records_count',
            'ground_temp_avg','ground_temp_min','ground_temp_max',
            'soil_moisture_avg','soil_moisture_min','soil_moisture_max',
            'water_height_avg','battery_voltage_avg','battery_voltage_min',
            'irrigation_usage_delta_l','water_usage_log_sum_l'
        ];

        return response()->stream(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($this->rows as $row) {
                $line = [];
                foreach ($columns as $col) {
                    $line[] = $row[$col] ?? '';
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, 200, $headers);
    }

    protected function baseFilter(): array
    {
        return [
            'device_ids' => $this->filters['device_ids'] ?? [],
            'only_active' => $this->filters['only_active'] ?? false,
        ];
    }

    public function exportDevicesCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $service = app(SystemReportService::class);
        $rows = $service->getDevices($this->baseFilter());
        $filename = 'devices_' . now()->format('Ymd_His') . '.csv';
        $headers = ['Content-Type'=>'text/csv','Content-Disposition'=>"attachment; filename=\"$filename\""];
        $columns = ['id','device_id','device_name','location','is_active','valve_state','connection_state','connection_state_source','last_seen_at'];
        return response()->stream(function() use ($rows,$columns){
            $out=fopen('php://output','w'); fputcsv($out,$columns); foreach($rows as $r){ $line=[]; foreach($columns as $c){ $line[] = $r[$c] ?? ($r->$c ?? ''); } fputcsv($out,$line);} fclose($out); },200,$headers);
    }

    public function exportWaterStoragesCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $service = app(SystemReportService::class);
        $rows = $service->getWaterStorages($this->baseFilter());
        $filename = 'water_storages_' . now()->format('Ymd_His') . '.csv';
        $headers = ['Content-Type'=>'text/csv','Content-Disposition'=>"attachment; filename=\"$filename\""];
        $columns = ['id','tank_name','device_id','capacity_liters','current_volume_liters','status','max_daily_usage','height_cm','last_height_cm','zone_name','area_size_sqm'];
        return response()->stream(function() use ($rows,$columns){ $out=fopen('php://output','w'); fputcsv($out,$columns); foreach($rows as $r){ $line=[]; foreach($columns as $c){ $line[]=$r[$c] ?? ($r->$c ?? ''); } fputcsv($out,$line);} fclose($out); },200,$headers);
    }

    public function exportSensorDataCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $service = app(SystemReportService::class);
        $from = Carbon::parse($this->filters['date_from'])->startOfDay();
        $to = Carbon::parse($this->filters['date_to'])->endOfDay();
        $result = $service->getSensorData($this->baseFilter(), $from, $to, 50000);
        $rows = $result['rows'];
        $filename = 'sensor_data_' . now()->format('Ymd_His') . '.csv';
    $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        $columns = ['device_id','recorded_at','ground_temperature_c','soil_moisture_pct','water_height_cm','battery_voltage_v','irrigation_usage_total_l'];
        if ($result['truncated']) {
            Notification::make()->warning()->title('Dataset besar, hanya ' . $result['limit'] . ' baris pertama diexport')->send();
        }
        return response()->stream(function() use ($rows,$columns){ $out=fopen('php://output','w'); fputcsv($out,$columns); foreach($rows as $r){ $line=[]; foreach($columns as $c){ $line[]=$r->$c ?? ''; } fputcsv($out,$line);} fclose($out); },200,$headers);
    }

    public function exportWaterUsageLogsCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $service = app(SystemReportService::class);
        $from = Carbon::parse($this->filters['date_from'])->startOfDay();
        $to = Carbon::parse($this->filters['date_to'])->endOfDay();
        $rows = $service->getWaterUsageLogs($this->baseFilter(), $from, $to);
        $filename = 'water_usage_logs_' . now()->format('Ymd_His') . '.csv';
    $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        $columns = ['device_id','usage_date','volume_used_l','source'];
        return response()->stream(function() use ($rows,$columns){ $out=fopen('php://output','w'); fputcsv($out,$columns); foreach($rows as $r){ $line=[]; foreach($columns as $c){ $line[]=$r->$c ?? ''; } fputcsv($out,$line);} fclose($out); },200,$headers);
    }

    // ---------------- Excel Export Methods ----------------
    public function exportSummaryExcel()
    {
        if (! $this->generated) $this->generate();
        return Excel::download(new SummaryReportExport($this->rows), 'summary_report_'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportDevicesExcel()
    {
        $service = app(SystemReportService::class);
        $devices = $service->getDevices($this->baseFilter());
        return Excel::download(new DevicesExport($devices), 'devices_'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportWaterStoragesExcel()
    {
        $service = app(SystemReportService::class);
        $storages = $service->getWaterStorages($this->baseFilter());
        return Excel::download(new WaterStoragesExport($storages), 'water_storages_'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportSensorDataExcel()
    {
        $service = app(SystemReportService::class);
        $from = Carbon::parse($this->filters['date_from'])->startOfDay();
        $to = Carbon::parse($this->filters['date_to'])->endOfDay();
        $result = $service->getSensorData($this->baseFilter(), $from, $to, 50000);
        return Excel::download(new SensorDataExport(collect($result['rows'])), 'sensor_data_'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportWaterUsageLogsExcel()
    {
        $service = app(SystemReportService::class);
        $from = Carbon::parse($this->filters['date_from'])->startOfDay();
        $to = Carbon::parse($this->filters['date_to'])->endOfDay();
        $logs = $service->getWaterUsageLogs($this->baseFilter(), $from, $to);
        return Excel::download(new WaterUsageLogsExport($logs), 'water_usage_logs_'.now()->format('Ymd_His').'.xlsx');
    }

    protected function buildChartData(array $rows): array
    {
        // Group by date for trend charts
        $dateGroups = [];
        foreach ($rows as $row) {
            $date = $row['tanggal'];
            if (!isset($dateGroups[$date])) {
                $dateGroups[$date] = [
                    'date' => $date,
                    'ground_temp_avg' => [],
                    'soil_moisture_avg' => [],
                    'water_usage_total' => 0,
                    'records_count' => 0,
                ];
            }
            if ($row['ground_temp_avg']) $dateGroups[$date]['ground_temp_avg'][] = (float)$row['ground_temp_avg'];
            if ($row['soil_moisture_avg']) $dateGroups[$date]['soil_moisture_avg'][] = (float)$row['soil_moisture_avg'];
            $dateGroups[$date]['water_usage_total'] += (float)($row['water_usage_log_sum_l'] ?? 0);
            $dateGroups[$date]['records_count'] += (int)($row['records_count'] ?? 0);
        }

        // Aggregate daily averages
        $dates = [];
        $groundTempData = [];
        $soilMoistureData = [];
        $waterUsageData = [];
        $recordsData = [];

        foreach ($dateGroups as $group) {
            $dates[] = $group['date'];
            $groundTempData[] = !empty($group['ground_temp_avg']) ? round(array_sum($group['ground_temp_avg']) / count($group['ground_temp_avg']), 1) : null;
            $soilMoistureData[] = !empty($group['soil_moisture_avg']) ? round(array_sum($group['soil_moisture_avg']) / count($group['soil_moisture_avg']), 1) : null;
            $waterUsageData[] = round($group['water_usage_total'], 1);
            $recordsData[] = $group['records_count'];
        }

        return [
            'labels' => $dates,
            'datasets' => [
                'ground_temp' => $groundTempData,
                'soil_moisture' => $soilMoistureData,
                'water_usage' => $waterUsageData,
                'records_count' => $recordsData,
            ]
        ];
    }
}
