<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Device;
use App\Services\SystemReportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SummaryReportExport;

class GenerateReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ReportResource::class;
    protected static string $view = 'filament.resources.report-resource.pages.generate-report';
    protected static ?string $title = 'Generate Laporan';

    public ?array $data = [];
    public array $reportData = [];
    public array $summary = [];
    public bool $generated = false;
    public $selectedQuickDate = null;
    public bool $isProcessingQuickDate = false;

    // Export Period Properties for All Types
    public string $devicesExportPeriod = 'all';
    public ?string $devicesCustomFromDate = null;
    public ?string $devicesCustomToDate = null;

    public string $waterStoragesExportPeriod = 'current';
    public ?string $waterStoragesCustomFromDate = null;
    public ?string $waterStoragesCustomToDate = null;

    public string $sensorDataExportPeriod = 'week';
    public ?string $sensorDataCustomFromDate = null;
    public ?string $sensorDataCustomToDate = null;

    public string $waterUsageExportPeriod = 'month';
    public ?string $waterUsageCustomFromDate = null;
    public ?string $waterUsageCustomToDate = null;

    public function mount(): void
    {
        // Get the latest date with sensor data
        $latestSensorDate = DB::table('sensor_data')
            ->selectRaw('MAX(DATE(recorded_at)) as max_date')
            ->value('max_date');

        $toDate = $latestSensorDate ? Carbon::parse($latestSensorDate) : now();
        $fromDate = $toDate->copy()->subDays(6);

        $this->form->fill([
            'date_from' => $fromDate->startOfDay(),
            'date_to' => $toDate->endOfDay(),
            'device_ids' => [],
            'only_active' => false,
        ]);
    }

    public function setQuickDate($period)
    {
        // Prevent double-click and multiple executions
        if ($this->isProcessingQuickDate) {
            return;
        }

        $this->isProcessingQuickDate = true;

        try {
            $this->selectedQuickDate = $period;

            switch ($period) {
                case 'today':
                    $dateFrom = now()->startOfDay();
                    $dateTo = now()->endOfDay();
                    break;
                case 'week':
                    $dateFrom = now()->startOfWeek()->startOfDay();
                    $dateTo = now()->endOfWeek()->endOfDay();
                    break;
                case 'month':
                    $dateFrom = now()->startOfMonth()->startOfDay();
                    $dateTo = now()->endOfMonth()->endOfDay();
                    break;
                default:
                    return;
            }

            // Update the data property
            $this->data['date_from'] = $dateFrom;
            $this->data['date_to'] = $dateTo;

            // Fill the form with updated data
            $this->form->fill($this->data);

            // Reset generation state to allow new report generation
            $this->generated = false;
            $this->reportData = [];
            $this->summary = [];

            // Auto-generate report for quick actions
            $this->generate();
        } finally {
            // Reset the processing flag after a short delay
            $this->dispatch('reset-processing-flag');
        }
    }

    public function resetProcessingFlag()
    {
        $this->isProcessingQuickDate = false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Buat Laporan Sistem Irigasi')
                    ->description('Pilih periode dan device untuk generate laporan sistem')
                    ->schema([
                        Forms\Components\Section::make('Pilih Periode Cepat')
                            ->description('Klik salah satu untuk mengatur tanggal secara otomatis')
                            ->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('today')
                                        ->label('Hari Ini')
                                        ->icon('heroicon-o-calendar')
                                        ->color('primary')
                                        ->disabled(fn() => $this->isProcessingQuickDate)
                                        ->requiresConfirmation(false)
                                        ->action(function () {
                                            if (!$this->isProcessingQuickDate) {
                                                $this->setQuickDate('today');
                                            }
                                        })
                                        ->extraAttributes([
                                            'data-prevent-double-click' => 'true',
                                            'onclick' => 'if(this.dataset.processing=="true") return false; this.dataset.processing="true"; this.style.pointerEvents="none"; setTimeout(() => {this.style.pointerEvents="auto"; this.dataset.processing="false";}, 3000);'
                                        ]),
                                    Forms\Components\Actions\Action::make('week')
                                        ->label('Minggu Ini')
                                        ->icon('heroicon-o-calendar-days')
                                        ->color('success')
                                        ->disabled(fn() => $this->isProcessingQuickDate)
                                        ->requiresConfirmation(false)
                                        ->action(function () {
                                            if (!$this->isProcessingQuickDate) {
                                                $this->setQuickDate('week');
                                            }
                                        })
                                        ->extraAttributes([
                                            'data-prevent-double-click' => 'true',
                                            'onclick' => 'if(this.dataset.processing=="true") return false; this.dataset.processing="true"; this.style.pointerEvents="none"; setTimeout(() => {this.style.pointerEvents="auto"; this.dataset.processing="false";}, 3000);'
                                        ]),
                                    Forms\Components\Actions\Action::make('month')
                                        ->label('Bulan Ini')
                                        ->icon('heroicon-o-calendar-date-range')
                                        ->color('warning')
                                        ->disabled(fn() => $this->isProcessingQuickDate)
                                        ->requiresConfirmation(false)
                                        ->action(function () {
                                            if (!$this->isProcessingQuickDate) {
                                                $this->setQuickDate('month');
                                            }
                                        })
                                        ->extraAttributes([
                                            'data-prevent-double-click' => 'true',
                                            'onclick' => 'if(this.dataset.processing=="true") return false; this.dataset.processing="true"; this.style.pointerEvents="none"; setTimeout(() => {this.style.pointerEvents="auto"; this.dataset.processing="false";}, 3000);'
                                        ]),
                                ])
                                    ->alignCenter()
                                    ->fullWidth(),
                            ])
                            ->columns(1),

                        Forms\Components\Section::make('Kustomisasi Periode Laporan')
                            ->description('Atur tanggal mulai dan akhir laporan secara manual')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_from')
                                            ->label('Pilih Bulan')
                                            ->default(now()->subDays(6))
                                            ->maxDate(fn($get) => $get('date_to')),
                                        Forms\Components\DatePicker::make('date_to')
                                            ->label('Sampai Tanggal')
                                            ->default(now())
                                            ->minDate(fn($get) => $get('date_from')),
                                    ]),
                                Forms\Components\Select::make('device_ids')
                                    ->label('Tampilan Device')
                                    ->multiple()
                                    ->options(function (callable $get) {
                                        $q = Device::query()->orderBy('device_name');
                                        if ($get('only_active')) {
                                            $q->where('is_active', true);
                                        }
                                        return $q->pluck('device_name', 'id');
                                    })
                                    ->placeholder('Tampilkan di Browser'),
                                Forms\Components\Toggle::make('only_active')
                                    ->label('Sertakan grafik dan visualisasi')
                                    ->inline(false),
                            ])
                            ->columns(1),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        $from = Carbon::parse($data['date_from'])->startOfDay();
        $to = Carbon::parse($data['date_to'])->endOfDay();
        $deviceIds = array_filter($data['device_ids'] ?? []);
        $onlyActive = (bool)($data['only_active'] ?? false);

        if ($from->gt($to)) {
            Notification::make()->danger()->title('Range tanggal tidak valid')->send();
            return;
        }

        $cacheKey = 'system_report:' . md5(json_encode([$from, $to, $deviceIds, $onlyActive]));
        $service = app(SystemReportService::class);
        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($service, $from, $to, $deviceIds, $onlyActive) {
            return $service->buildAggregation($from, $to, $deviceIds, $onlyActive);
        });

        // Sanitize data to prevent UTF-8 encoding issues
        Log::info('Starting data sanitization', ['rows_count' => count($result['rows'])]);
        $this->reportData = $this->sanitizeData($result['rows'], 'reportData');
        $this->summary = $this->sanitizeData($result['summary'], 'summary');
        $this->generated = true;
        Log::info('Data sanitization completed successfully');

        // Check if we have sensor data
        $hasSensorData = collect($result['rows'])->where('records_count', '>', 0)->count() > 0;

        if (!$hasSensorData) {
            Notification::make()
                ->warning()
                ->title('Laporan dibuat')
                ->body('Tidak ada data sensor untuk periode ini. Hanya menampilkan data penggunaan air.')
                ->send();
        } else {
            Notification::make()->success()->title('Laporan berhasil dibuat')->send();
        }
    }

    public function exportSummaryExcel()
    {
        try {
            if (!$this->generated) {
                $this->generate();
            }

            // Get data based on selected period
            $aggregatedData = $this->getAggregatedSummaryData();

            // Create Excel export with custom period data
            $exportData = collect($aggregatedData)->map(function ($row) {
                return [
                    $row['period'] ?? null,
                    $row['device_name'] ?? null,
                    $row['records_count'] ?? 0,
                    $row['avg_ground_temp_c'] ?? null,
                    $row['avg_soil_moisture_pct'] ?? null,
                    $row['avg_battery_voltage_v'] ?? null,
                    $row['total_water_usage_l'] ?? 0,
                ];
            });

            $filename = 'ringkasan_' . $this->summaryExportPeriod . '_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new \App\Exports\SummaryReportExport($exportData->toArray()), $filename);
        } catch (\Exception $e) {
            Log::error('Summary Excel export failed', [
                'error' => $e->getMessage(),
                'period' => $this->summaryExportPeriod,
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export summary to Excel.')
                ->danger()
                ->send();
        }
    }

    public function exportSummaryPdf()
    {
        try {
            if (!$this->generated) {
                $this->generate();
            }

            // Get data based on selected period
            $aggregatedData = $this->getAggregatedSummaryData();
            $cleanData = $this->ultraCleanData($aggregatedData);

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('reports.summary-custom-pdf', [
                'reportData' => $cleanData,
                'period' => $this->summaryExportPeriod,
                'periodLabel' => $this->getPeriodLabel(),
                'dateRange' => $this->getDateRangeForPeriod(),
                'customFromDate' => $this->summaryCustomFromDate,
                'customToDate' => $this->summaryCustomToDate
            ]);

            $filename = 'ringkasan_' . $this->summaryExportPeriod . '_' . now()->format('Ymd_His') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('Summary PDF export failed', [
                'error' => $e->getMessage(),
                'period' => $this->summaryExportPeriod,
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF. Trying Excel export instead.')
                ->warning()
                ->send();

            return $this->exportSummaryExcel();
        }
    }

    private function getAggregatedSummaryData()
    {
        // Get base query data
        $baseQuery = $this->getBaseReportQuery();

        switch ($this->summaryExportPeriod) {
            case 'daily':
                return $this->aggregateByDay($baseQuery);
            case 'weekly':
                return $this->aggregateByWeek($baseQuery);
            case 'monthly':
                return $this->aggregateByMonth($baseQuery);
            case 'custom':
                return $this->aggregateByCustomRange($baseQuery);
            default:
                return $this->aggregateByDay($baseQuery);
        }
    }

    private function aggregateByDay($query)
    {
        return $query->selectRaw("
            DATE(sensor_data.recorded_at) as period,
            devices.device_name,
            COUNT(sensor_data.id) as records_count,
            ROUND(AVG(sensor_data.ground_temp_c), 2) as avg_ground_temp_c,
            ROUND(AVG(sensor_data.soil_moisture_pct), 2) as avg_soil_moisture_pct,
            ROUND(AVG(sensor_data.battery_voltage_v), 2) as avg_battery_voltage_v,
            COALESCE(SUM(water_usage_logs.usage_liters), 0) as total_water_usage_l
        ")
            ->groupBy('period', 'devices.id', 'devices.device_name')
            ->orderBy('period', 'desc')
            ->orderBy('devices.device_name')
            ->get()
            ->toArray();
    }

    private function aggregateByWeek($query)
    {
        return $query->selectRaw("
            CONCAT(YEAR(sensor_data.recorded_at), '-W', LPAD(WEEK(sensor_data.recorded_at), 2, '0')) as period,
            devices.device_name,
            COUNT(sensor_data.id) as records_count,
            ROUND(AVG(sensor_data.ground_temp_c), 2) as avg_ground_temp_c,
            ROUND(AVG(sensor_data.soil_moisture_pct), 2) as avg_soil_moisture_pct,
            ROUND(AVG(sensor_data.battery_voltage_v), 2) as avg_battery_voltage_v,
            COALESCE(SUM(water_usage_logs.usage_liters), 0) as total_water_usage_l
        ")
            ->groupBy('period', 'devices.id', 'devices.device_name')
            ->orderBy('period', 'desc')
            ->orderBy('devices.device_name')
            ->get()
            ->toArray();
    }

    private function aggregateByMonth($query)
    {
        return $query->selectRaw("
            DATE_FORMAT(sensor_data.recorded_at, '%Y-%m') as period,
            devices.device_name,
            COUNT(sensor_data.id) as records_count,
            ROUND(AVG(sensor_data.ground_temp_c), 2) as avg_ground_temp_c,
            ROUND(AVG(sensor_data.soil_moisture_pct), 2) as avg_soil_moisture_pct,
            ROUND(AVG(sensor_data.battery_voltage_v), 2) as avg_battery_voltage_v,
            COALESCE(SUM(water_usage_logs.usage_liters), 0) as total_water_usage_l
        ")
            ->groupBy('period', 'devices.id', 'devices.device_name')
            ->orderBy('period', 'desc')
            ->orderBy('devices.device_name')
            ->get()
            ->toArray();
    }

    private function aggregateByCustomRange($query)
    {
        $fromDate = $this->summaryCustomFromDate ?? $this->data['date_from'];
        $toDate = $this->summaryCustomToDate ?? $this->data['date_to'];

        return $query->selectRaw("
            CONCAT(?, ' - ', ?) as period,
            devices.device_name,
            COUNT(sensor_data.id) as records_count,
            ROUND(AVG(sensor_data.ground_temp_c), 2) as avg_ground_temp_c,
            ROUND(AVG(sensor_data.soil_moisture_pct), 2) as avg_soil_moisture_pct,
            ROUND(AVG(sensor_data.battery_voltage_v), 2) as avg_battery_voltage_v,
            COALESCE(SUM(water_usage_logs.usage_liters), 0) as total_water_usage_l
        ", [$fromDate, $toDate])
            ->whereBetween('sensor_data.recorded_at', [$fromDate, $toDate])
            ->groupBy('devices.id', 'devices.device_name')
            ->orderBy('devices.device_name')
            ->get()
            ->toArray();
    }

    private function getBaseReportQuery()
    {
        $query = DB::table('sensor_data')
            ->join('devices', 'sensor_data.device_id', '=', 'devices.id')
            ->leftJoin('water_usage_logs', function ($join) {
                $join->on('water_usage_logs.device_id', '=', 'devices.id')
                    ->whereRaw('DATE(water_usage_logs.recorded_at) = DATE(sensor_data.recorded_at)');
            });

        // Apply date filters
        if (!empty($this->data['date_from'])) {
            $query->where('sensor_data.recorded_at', '>=', $this->data['date_from']);
        }
        if (!empty($this->data['date_to'])) {
            $query->where('sensor_data.recorded_at', '<=', $this->data['date_to']);
        }

        // Apply device filters
        if (!empty($this->data['device_ids'])) {
            $query->whereIn('devices.id', $this->data['device_ids']);
        }

        return $query;
    }

    private function getPeriodLabel()
    {
        switch ($this->summaryExportPeriod) {
            case 'daily':
                return 'Harian (Per Hari)';
            case 'weekly':
                return 'Mingguan (Per Minggu)';
            case 'monthly':
                return 'Bulanan (Per Bulan)';
            case 'custom':
                return 'Custom Range';
            default:
                return 'Harian (Per Hari)';
        }
    }

    private function getDateRangeForPeriod()
    {
        if ($this->summaryExportPeriod === 'custom') {
            return ($this->summaryCustomFromDate ?? 'N/A') . ' - ' . ($this->summaryCustomToDate ?? 'N/A');
        }

        return ($this->data['date_from'] ?? 'N/A') . ' - ' . ($this->data['date_to'] ?? 'N/A');
    }

    // ============================
    // Export Methods for Different Data Types
    // ============================

    public function exportDevicesExcel()
    {
        try {
            $query = Device::query()
                ->select(
                    'id',
                    'device_id',
                    'device_name',
                    'location',
                    'is_active',
                    'valve_state',
                    'connection_state',
                    'connection_state_source',
                    'last_seen_at',
                    'valve_state_changed_at',
                    'description',
                    'created_at'
                );

            // Apply period filtering
            switch ($this->devicesExportPeriod) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'custom':
                    if ($this->devicesCustomFromDate) {
                        $query->whereDate('created_at', '>=', $this->devicesCustomFromDate);
                    }
                    if ($this->devicesCustomToDate) {
                        $query->whereDate('created_at', '<=', $this->devicesCustomToDate);
                    }
                    break;
                    // 'all' - no additional filtering
            }

            $devices = $query->get();

            if ($devices->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No devices found for the selected criteria.')
                    ->warning()
                    ->send();
                return null;
            }

            $filename = 'devices_' . $this->devicesExportPeriod . '_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new \App\Exports\DevicesExport($devices), $filename);
        } catch (\Exception $e) {
            Log::error('Device Excel export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export devices to Excel.')
                ->danger()
                ->send();
        }
    }

    public function exportDevicesPdf()
    {
        try {
            $query = Device::query()
                ->select(
                    'id',
                    'device_id',
                    'device_name',
                    'location',
                    'is_active',
                    'valve_state',
                    'connection_state',
                    'connection_state_source',
                    'last_seen_at',
                    'valve_state_changed_at',
                    'description',
                    'created_at'
                );

            // Apply period filtering
            switch ($this->devicesExportPeriod) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'custom':
                    if ($this->devicesCustomFromDate) {
                        $query->whereDate('created_at', '>=', $this->devicesCustomFromDate);
                    }
                    if ($this->devicesCustomToDate) {
                        $query->whereDate('created_at', '<=', $this->devicesCustomToDate);
                    }
                    break;
            }

            $devices = $query->get();

            if ($devices->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No devices found for the selected criteria.')
                    ->warning()
                    ->send();
                return null;
            }

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.devices', compact('devices'));

            $filename = 'devices_' . $this->devicesExportPeriod . '_' . now()->format('Ymd_His') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('Device PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportDevicesExcel();
        }
    }

    public function exportWaterStoragesExcel()
    {
        try {
            $query = DB::table('water_storages')
                ->select(
                    'id',
                    'tank_name',
                    'zone_name',
                    'capacity_liters',
                    'current_volume_liters',
                    'percentage',
                    'status',
                    'area_name',
                    'plant_types',
                    'created_at'
                );

            // Apply period filtering
            switch ($this->waterStoragesExportPeriod) {
                case 'active':
                    $query->where('status', '!=', 'inactive');
                    break;
                case 'low_level':
                    $query->where('percentage', '<', 30);
                    break;
                case 'high_capacity':
                    $query->where('capacity_liters', '>', 500);
                    break;
                case 'custom':
                    if ($this->waterStoragesCustomFromDate) {
                        $query->whereDate('created_at', '>=', $this->waterStoragesCustomFromDate);
                    }
                    if ($this->waterStoragesCustomToDate) {
                        $query->whereDate('created_at', '<=', $this->waterStoragesCustomToDate);
                    }
                    break;
                    // 'current' - no additional filtering
            }

            $waterStorages = $query->get();

            if ($waterStorages->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No water storages found for the selected criteria.')
                    ->warning()
                    ->send();
                return null;
            }

            $filename = 'water_storages_' . $this->waterStoragesExportPeriod . '_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new \App\Exports\WaterStoragesExport($waterStorages), $filename);
        } catch (\Exception $e) {
            Log::error('Water Storage Excel export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export water storages to Excel.')
                ->danger()
                ->send();
        }
    }

    public function exportWaterStoragesPdf()
    {
        try {
            $query = DB::table('water_storages')
                ->select(
                    'id',
                    'tank_name',
                    'zone_name',
                    'capacity_liters',
                    'current_volume_liters',
                    'percentage',
                    'status',
                    'area_name',
                    'plant_types',
                    'created_at'
                );

            // Apply period filtering
            switch ($this->waterStoragesExportPeriod) {
                case 'active':
                    $query->where('status', '!=', 'inactive');
                    break;
                case 'low_level':
                    $query->where('percentage', '<', 30);
                    break;
                case 'high_capacity':
                    $query->where('capacity_liters', '>', 500);
                    break;
                case 'custom':
                    if ($this->waterStoragesCustomFromDate) {
                        $query->whereDate('created_at', '>=', $this->waterStoragesCustomFromDate);
                    }
                    if ($this->waterStoragesCustomToDate) {
                        $query->whereDate('created_at', '<=', $this->waterStoragesCustomToDate);
                    }
                    break;
            }

            $waterStorages = $query->get();

            if ($waterStorages->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No water storages found for the selected criteria.')
                    ->warning()
                    ->send();
                return null;
            }

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.water-storages', compact('waterStorages'));

            $filename = 'water_storages_' . $this->waterStoragesExportPeriod . '_' . now()->format('Ymd_His') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('Water Storage PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportWaterStoragesExcel();
        }
    }

    public function exportSensorDataExcel()
    {
        try {
            $query = DB::table('sensor_data')
                ->join('devices', 'sensor_data.device_id', '=', 'devices.id')
                ->select(
                    'sensor_data.id',
                    'devices.name as device_name',
                    'sensor_data.ground_temp_c',
                    'sensor_data.air_temp_c',
                    'sensor_data.air_humidity_pct',
                    'sensor_data.soil_moisture_pct',
                    'sensor_data.battery_voltage_v',
                    'sensor_data.recorded_at'
                );

            // Apply period filtering
            switch ($this->sensorDataExportPeriod) {
                case 'today':
                    $query->whereDate('sensor_data.recorded_at', now());
                    break;
                case 'week':
                    $query->whereBetween('sensor_data.recorded_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('sensor_data.recorded_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'latest':
                    $query->limit(1000);
                    break;
                case 'custom':
                    if ($this->sensorDataCustomFromDate) {
                        $query->whereDate('sensor_data.recorded_at', '>=', $this->sensorDataCustomFromDate);
                    }
                    if ($this->sensorDataCustomToDate) {
                        $query->whereDate('sensor_data.recorded_at', '<=', $this->sensorDataCustomToDate);
                    }
                    break;
            }

            $sensorData = $query->orderBy('sensor_data.recorded_at', 'desc')
                ->limit(50000) // Limit to 50k rows as mentioned in UI
                ->get();

            $filename = 'sensor_data_' . $this->sensorDataExportPeriod . '_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new \App\Exports\SensorDataExport($sensorData), $filename);
        } catch (\Exception $e) {
            Log::error('Sensor Data Excel export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export sensor data to Excel.')
                ->danger()
                ->send();
        }
    }

    public function exportSensorDataPdf()
    {
        try {
            $query = DB::table('sensor_data')
                ->join('devices', 'sensor_data.device_id', '=', 'devices.id')
                ->select(
                    'sensor_data.id',
                    'devices.name as device_name',
                    'sensor_data.ground_temp_c',
                    'sensor_data.air_temp_c',
                    'sensor_data.air_humidity_pct',
                    'sensor_data.soil_moisture_pct',
                    'sensor_data.battery_voltage_v',
                    'sensor_data.recorded_at'
                );

            // Apply period filtering
            switch ($this->sensorDataExportPeriod) {
                case 'today':
                    $query->whereDate('sensor_data.recorded_at', now());
                    break;
                case 'week':
                    $query->whereBetween('sensor_data.recorded_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('sensor_data.recorded_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'latest':
                    $query->limit(500); // Reduced for PDF
                    break;
                case 'custom':
                    if ($this->sensorDataCustomFromDate) {
                        $query->whereDate('sensor_data.recorded_at', '>=', $this->sensorDataCustomFromDate);
                    }
                    if ($this->sensorDataCustomToDate) {
                        $query->whereDate('sensor_data.recorded_at', '<=', $this->sensorDataCustomToDate);
                    }
                    break;
            }

            $sensorData = $query->orderBy('sensor_data.recorded_at', 'desc')
                ->limit(1000) // Limit for PDF to avoid memory issues
                ->get();

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.sensor-data', compact('sensorData'));

            $filename = 'sensor_data_' . $this->sensorDataExportPeriod . '_' . now()->format('Ymd_His') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('Sensor Data PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportSensorDataExcel();
        }
    }

    public function exportWaterUsageLogsExcel()
    {
        try {
            $query = DB::table('water_usage_logs')
                ->leftJoin('devices', 'water_usage_logs.device_id', '=', 'devices.id')
                ->leftJoin('water_storages', 'water_usage_logs.water_storage_id', '=', 'water_storages.id')
                ->select(
                    'water_usage_logs.id',
                    'devices.device_name',
                    'water_storages.tank_name',
                    'water_usage_logs.usage_date',
                    'water_usage_logs.volume_used_l',
                    'water_usage_logs.source',
                    'water_usage_logs.created_at'
                );

            // Apply period filtering
            switch ($this->waterUsageExportPeriod) {
                case 'today':
                    $query->whereDate('water_usage_logs.usage_date', now());
                    break;
                case 'week':
                    $query->whereBetween('water_usage_logs.usage_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('water_usage_logs.usage_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'high_usage':
                    $query->where('water_usage_logs.volume_used_l', '>', 100);
                    break;
                case 'custom':
                    if ($this->waterUsageCustomFromDate) {
                        $query->whereDate('water_usage_logs.usage_date', '>=', $this->waterUsageCustomFromDate);
                    }
                    if ($this->waterUsageCustomToDate) {
                        $query->whereDate('water_usage_logs.usage_date', '<=', $this->waterUsageCustomToDate);
                    }
                    break;
            }

            $waterUsageLogs = $query->orderBy('water_usage_logs.usage_date', 'desc')
                ->get();

            if ($waterUsageLogs->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No water usage logs found for the selected criteria.')
                    ->warning()
                    ->send();
                return null;
            }

            $filename = 'water_usage_logs_' . $this->waterUsageExportPeriod . '_' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new \App\Exports\WaterUsageLogsExport($waterUsageLogs), $filename);
        } catch (\Exception $e) {
            Log::error('Water Usage Log Excel export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export water usage logs to Excel.')
                ->danger()
                ->send();
        }
    }

    public function exportWaterUsageLogsPdf()
    {
        try {
            $query = DB::table('water_usage_logs')
                ->leftJoin('devices', 'water_usage_logs.device_id', '=', 'devices.id')
                ->leftJoin('water_storages', 'water_usage_logs.water_storage_id', '=', 'water_storages.id')
                ->select(
                    'water_usage_logs.id',
                    'devices.device_name',
                    'water_storages.tank_name',
                    'water_usage_logs.usage_date',
                    'water_usage_logs.volume_used_l',
                    'water_usage_logs.source',
                    'water_usage_logs.created_at'
                );

            // Apply period filtering
            switch ($this->waterUsageExportPeriod) {
                case 'today':
                    $query->whereDate('water_usage_logs.usage_date', now());
                    break;
                case 'week':
                    $query->whereBetween('water_usage_logs.usage_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('water_usage_logs.usage_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'high_usage':
                    $query->where('water_usage_logs.volume_used_l', '>', 100);
                    break;
                case 'custom':
                    if ($this->waterUsageCustomFromDate) {
                        $query->whereDate('water_usage_logs.usage_date', '>=', $this->waterUsageCustomFromDate);
                    }
                    if ($this->waterUsageCustomToDate) {
                        $query->whereDate('water_usage_logs.usage_date', '<=', $this->waterUsageCustomToDate);
                    }
                    break;
            }

            $waterUsageLogs = $query->orderBy('water_usage_logs.usage_date', 'desc')
                ->get();

            if ($waterUsageLogs->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No water usage logs found for the selected criteria.')
                    ->warning()
                    ->send();
                return null;
            }

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.water-usage-logs', compact('waterUsageLogs'));

            $filename = 'water_usage_logs_' . $this->waterUsageExportPeriod . '_' . now()->format('Ymd_His') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('Water Usage Log PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportWaterUsageLogsExcel();
        }
    }

    /**
     * Force clean data - very aggressive UTF-8 sanitization
     */
    private function forceCleanData($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? $this->forceCleanData($key) : $key;
                $result[$cleanKey] = $this->forceCleanData($value);
            }
            return $result;
        }

        if (is_string($data)) {
            // Step 1: Convert to UTF-8 if not already
            if (!mb_check_encoding($data, 'UTF-8')) {
                $data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data, 'UTF-8,ISO-8859-1,ASCII', true) ?: 'UTF-8');
            }

            // Step 2: Remove or replace problematic characters
            $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data); // Remove control characters
            $data = preg_replace('/[^\x20-\x7E\x{00A0}-\x{FFFF}]/u', '?', $data); // Replace non-printable with ?

            // Step 3: Clean up any remaining issues
            $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8'); // Force reconvert

            // Step 4: Final validation and fallback
            if (!mb_check_encoding($data, 'UTF-8')) {
                $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            }

            return $data;
        }

        return $data;
    }

    /**
     * Sanitize data to prevent UTF-8 encoding issues
     */
    private function sanitizeData($data, $path = '')
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $currentPath = $path ? $path . '.' . $key : $key;
                $result[$key] = $this->sanitizeData($value, $currentPath);
            }
            return $result;
        }

        if (is_string($data)) {
            try {
                // First check if the string is valid UTF-8
                if (!mb_check_encoding($data, 'UTF-8')) {
                    Log::warning('Invalid UTF-8 detected', ['path' => $path, 'data' => bin2hex($data)]);
                    // Try to convert from common encodings
                    $data = mb_convert_encoding($data, 'UTF-8', ['UTF-8', 'ISO-8859-1', 'Windows-1252']);
                }

                // Remove control characters and other problematic bytes
                $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/', '', $data);

                // Final validation
                if (!mb_check_encoding($data, 'UTF-8')) {
                    Log::error('Still invalid UTF-8 after sanitization', ['path' => $path]);
                    $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8'); // Force conversion
                }

                // Test if it can be JSON encoded
                $test = json_encode($data);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON encoding failed after sanitization', [
                        'path' => $path,
                        'error' => json_last_error_msg(),
                        'data_hex' => bin2hex($data)
                    ]);
                    // Fallback: remove all non-ASCII characters
                    $data = preg_replace('/[^\x20-\x7E]/', '', $data);
                }

                return $data;
            } catch (\Exception $e) {
                Log::error('Exception during sanitization', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                    'data_hex' => bin2hex($data)
                ]);
                // Fallback: return safe ASCII-only string
                return preg_replace('/[^\x20-\x7E]/', '', $data);
            }
        }

        return $data;
    }

    /**
     * Ultra aggressive data cleaning - remove ALL non-ASCII characters
     */
    private function ultraCleanData($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? preg_replace('/[^\x20-\x7E]/', '', $key) : $key;
                $result[$cleanKey] = $this->ultraCleanData($value);
            }
            return $result;
        }

        if (is_string($data)) {
            // Only keep ASCII printable characters
            $cleaned = preg_replace('/[^\x20-\x7E]/', '', $data);
            // Additional safety: ensure no null bytes
            $cleaned = str_replace(["\0", "\x00"], '', $cleaned);
            return $cleaned;
        }

        if (is_numeric($data)) {
            return $data;
        }

        if (is_bool($data) || is_null($data)) {
            return $data;
        }

        // For any other type, convert to string and clean
        return $this->ultraCleanData((string) $data);
    }

    /**
     * Override to sanitize all component state before response
     */
    public function dehydrate()
    {
        Log::info('Dehydrate starting', [
            'reportData_count' => count($this->reportData),
            'summary_keys' => array_keys($this->summary)
        ]);

        try {
            // Layer 1: Standard sanitization
            $this->reportData = $this->sanitizeData($this->reportData, 'dehydrate.reportData');
            $this->summary = $this->sanitizeData($this->summary, 'dehydrate.summary');
            $this->data = $this->sanitizeData($this->data, 'dehydrate.data');

            // Layer 2: Test JSON encoding
            $testData = [
                'reportData' => $this->reportData,
                'summary' => $this->summary,
                'generated' => $this->generated,
                'data' => $this->data
            ];

            $json = json_encode($testData);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('JSON encoding failed - applying forceCleanData', [
                    'error' => json_last_error_msg(),
                    'component' => static::class
                ]);

                // Layer 3: Force clean
                $this->reportData = $this->forceCleanData($this->reportData);
                $this->summary = $this->forceCleanData($this->summary);
                $this->data = $this->forceCleanData($this->data);

                // Test again
                $testData2 = [
                    'reportData' => $this->reportData,
                    'summary' => $this->summary,
                    'generated' => $this->generated,
                    'data' => $this->data
                ];

                $json2 = json_encode($testData2);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON encoding still failed - applying ultraCleanData', [
                        'error' => json_last_error_msg(),
                        'component' => static::class
                    ]);

                    // Layer 4: Ultra aggressive cleaning
                    $this->reportData = $this->ultraCleanData($this->reportData);
                    $this->summary = $this->ultraCleanData($this->summary);
                    $this->data = $this->ultraCleanData($this->data);

                    // Final test
                    $testData3 = [
                        'reportData' => $this->reportData,
                        'summary' => $this->summary,
                        'generated' => $this->generated,
                        'data' => $this->data
                    ];

                    $json3 = json_encode($testData3);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::critical('JSON encoding failed after all cleaning attempts - resetting data', [
                            'error' => json_last_error_msg(),
                            'component' => static::class
                        ]);

                        // Last resort: reset all data
                        $this->reportData = [];
                        $this->summary = ['total_records' => 0, 'total_devices' => 0, 'total_irrigation_usage_delta_l' => 0, 'total_water_usage_log_sum_l' => 0, 'avg_soil_moisture_pct' => 0];
                        $this->data = ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d'), 'device_ids' => [], 'only_active' => false];
                        $this->generated = false;
                    } else {
                        Log::info('JSON encoding successful after ultraCleanData');
                    }
                } else {
                    Log::info('JSON encoding successful after forceCleanData');
                }
            } else {
                Log::info('JSON encoding successful after standard sanitization');
            }
        } catch (\Exception $e) {
            Log::error('Exception in dehydrate - resetting all data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'component' => static::class
            ]);

            // Emergency fallback: reset everything to safe defaults
            $this->reportData = [];
            $this->summary = ['total_records' => 0, 'total_devices' => 0, 'total_irrigation_usage_delta_l' => 0, 'total_water_usage_log_sum_l' => 0, 'avg_soil_moisture_pct' => 0];
            $this->data = ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d'), 'device_ids' => [], 'only_active' => false];
            $this->generated = false;
        }

        Log::info('Dehydrate completed', [
            'final_reportData_count' => count($this->reportData),
            'final_summary_keys' => array_keys($this->summary)
        ]);
    }
}
