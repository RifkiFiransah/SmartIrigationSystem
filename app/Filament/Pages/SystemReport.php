<?php

namespace App\Filament\Pages;

use App\Exports\DevicesExport;
use App\Exports\SensorDataExport;
use App\Exports\WaterStoragesExport;
use App\Exports\WaterUsageLogsExport;
use App\Exports\SummaryReportExport;
use App\Services\SystemReportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SystemReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.system-report';
    protected static ?string $title = 'System Report';

    public ?array $data = [];
    public array $summary = [];
    public array $rows = [];
    public array $chartData = [];
    public bool $generated = false;

    public function mount(): void
    {
        // Initialize form with default date range
        $latestSensorDate = DB::table('sensor_data')
            ->selectRaw('MAX(DATE(recorded_at)) as max_date')
            ->value('max_date');

        $toDate = $latestSensorDate ? Carbon::parse($latestSensorDate) : now();
        $fromDate = $toDate->copy()->subDays(6);

        $this->form->fill([
            'date_from' => $fromDate->startOfDay(),
            'date_to' => $toDate->endOfDay(),
            'device_ids' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\DateTimePicker::make('date_from')
                            ->label('From Date')
                            ->required(),
                        Forms\Components\DateTimePicker::make('date_to')
                            ->label('To Date')
                            ->required(),
                        Forms\Components\Select::make('device_ids')
                            ->label('Devices')
                            ->multiple()
                            ->options(
                                \App\Models\Device::pluck('device_name', 'id')->toArray()
                            ),
                    ])
            ])
            ->statePath('data');
    }

    public function generate()
    {
        try {
            $data = $this->form->getState();
            
            // Simple report generation without the service for now
            $fromDate = Carbon::parse($data['date_from']);
            $toDate = Carbon::parse($data['date_to']);
            $deviceIds = $data['device_ids'] ?? [];
            
            // Generate basic summary
            $query = DB::table('sensor_data')
                ->whereBetween('recorded_at', [$fromDate, $toDate]);
                
            if (!empty($deviceIds)) {
                $query->whereIn('device_id', $deviceIds);
            }
            
            $this->summary = [
                'total_records' => $query->count(),
                'total_devices' => $query->distinct('device_id')->count('device_id'),
                'avg_soil_moisture_pct' => $query->avg('soil_moisture_pct') ?? 0,
            ];
            
            // Generate basic rows
            $this->rows = $query->select(
                DB::raw('DATE(recorded_at) as tanggal'),
                'device_id',
                DB::raw('COUNT(*) as records_count'),
                DB::raw('AVG(ground_temperature_c) as ground_temp_avg')
            )
            ->groupBy(DB::raw('DATE(recorded_at)'), 'device_id')
            ->get()->toArray();
            
            $this->chartData = [
                'labels' => [],
                'datasets' => []
            ];
            
            $this->generated = true;

            Notification::make()
                ->title('Report Generated')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Report generation failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Generation Failed')
                ->body('Unable to generate report: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportSummaryExcel()
    {
        try {
            if (!$this->generated) {
                Notification::make()
                    ->title('No Data')
                    ->body('Please generate a report first.')
                    ->warning()
                    ->send();
                return null;
            }

            $filename = 'summary_report_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new SummaryReportExport($this->rows), $filename);

        } catch (\Exception $e) {
            Log::error('Summary Excel export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export summary to Excel: ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }
    }

    public function exportSummaryPdf()
    {
        try {
            if (!$this->generated) {
                Notification::make()
                    ->title('No Data')
                    ->body('Please generate a report first.')
                    ->warning()
                    ->send();
                return null;
            }

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.summary-report', [
                'summary' => $this->summary,
                'rows' => $this->rows
            ]);

            $filename = 'summary_report_' . now()->format('Ymd_His') . '.pdf';
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Summary PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF: ' . $e->getMessage() . '. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportSummaryExcel();
        }
    }

    public function exportDevicesExcel()
    {
        try {
            $devices = DB::table('devices')
                ->select('id', 'device_name', 'location', 'is_active', 'created_at')
                ->get();

            $filename = 'devices_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new DevicesExport($devices), $filename);

        } catch (\Exception $e) {
            Log::error('Devices Excel export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export devices to Excel: ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }
    }

    public function exportDevicesPdf()
    {
        try {
            $devices = DB::table('devices')
                ->select('id', 'device_name', 'location', 'is_active', 'created_at')
                ->get();

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.devices', compact('devices'));

            $filename = 'devices_' . now()->format('Ymd_His') . '.pdf';
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Devices PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF: ' . $e->getMessage() . '. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportDevicesExcel();
        }
    }

    public function exportWaterStoragesExcel()
    {
        try {
            $waterStorages = DB::table('water_storages')
                ->select(
                    'id',
                    'tank_name',
                    'device_id',
                    'zone_name',
                    'capacity_liters',
                    'current_volume_liters',
                    'percentage',
                    'status',
                    'area_name',
                    'area_size_sqm',
                    'plant_types',
                    'height_cm',
                    'last_height_cm',
                    'max_daily_usage',
                    'created_at'
                )
                ->get();

            if ($waterStorages->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No water storages found.')
                    ->warning()
                    ->send();
                return null;
            }

            $filename = 'water_storages_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new WaterStoragesExport($waterStorages), $filename);

        } catch (\Exception $e) {
            Log::error('Water Storage Excel export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export water storages to Excel: ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }
    }

    public function exportWaterStoragesPdf()
    {
        try {
            $waterStorages = DB::table('water_storages')
                ->select(
                    'id',
                    'tank_name',
                    'device_id',
                    'zone_name',
                    'capacity_liters',
                    'current_volume_liters',
                    'percentage',
                    'status',
                    'area_name',
                    'created_at'
                )
                ->get();

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.water-storages', compact('waterStorages'));

            $filename = 'water_storages_' . now()->format('Ymd_His') . '.pdf';
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Water Storage PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF: ' . $e->getMessage() . '. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportWaterStoragesExcel();
        }
    }

    public function exportSensorDataExcel()
    {
        try {
            $sensorData = DB::table('sensor_data')
                ->join('devices', 'sensor_data.device_id', '=', 'devices.id')
                ->select(
                    'sensor_data.id',
                    'devices.device_name',
                    'sensor_data.ground_temperature_c',
                    'sensor_data.soil_moisture_pct',
                    'sensor_data.water_height_cm',
                    'sensor_data.battery_voltage_v',
                    'sensor_data.irrigation_usage_total_l',
                    'sensor_data.recorded_at',
                    'sensor_data.status'
                )
                ->orderBy('sensor_data.recorded_at', 'desc')
                ->limit(50000)
                ->get();

            if ($sensorData->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No sensor data found.')
                    ->warning()
                    ->send();
                return null;
            }

            $filename = 'sensor_data_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new SensorDataExport($sensorData), $filename);

        } catch (\Exception $e) {
            Log::error('Sensor Data Excel export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export sensor data to Excel: ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }
    }

    public function exportSensorDataPdf()
    {
        try {
            $sensorData = DB::table('sensor_data')
                ->join('devices', 'sensor_data.device_id', '=', 'devices.id')
                ->select(
                    'sensor_data.id',
                    'devices.device_name',
                    'sensor_data.ground_temperature_c',
                    'sensor_data.soil_moisture_pct',
                    'sensor_data.water_height_cm',
                    'sensor_data.battery_voltage_v',
                    'sensor_data.irrigation_usage_total_l',
                    'sensor_data.recorded_at',
                    'sensor_data.status'
                )
                ->orderBy('sensor_data.recorded_at', 'desc')
                ->limit(1000)
                ->get();

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.sensor-data', compact('sensorData'));

            $filename = 'sensor_data_' . now()->format('Ymd_His') . '.pdf';
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Sensor Data PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF: ' . $e->getMessage() . '. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportSensorDataExcel();
        }
    }

    public function exportWaterUsageLogsExcel()
    {
        try {
            $waterUsageLogs = DB::table('water_usage_logs')
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
                )
                ->orderBy('water_usage_logs.usage_date', 'desc')
                ->get();

            if ($waterUsageLogs->isEmpty()) {
                Notification::make()
                    ->title('No Data Found')
                    ->body('No water usage logs found.')
                    ->warning()
                    ->send();
                return null;
            }

            $filename = 'water_usage_logs_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new WaterUsageLogsExport($waterUsageLogs), $filename);

        } catch (\Exception $e) {
            Log::error('Water Usage Logs Excel export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to export water usage logs to Excel: ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }
    }

    public function exportWaterUsageLogsPdf()
    {
        try {
            $waterUsageLogs = DB::table('water_usage_logs')
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
                )
                ->orderBy('water_usage_logs.usage_date', 'desc')
                ->limit(1000)
                ->get();

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.water-usage-logs', compact('waterUsageLogs'));

            $filename = 'water_usage_logs_' . now()->format('Ymd_His') . '.pdf';
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Water Usage Logs PDF export failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('PDF Generation Failed')
                ->body('Unable to generate PDF: ' . $e->getMessage() . '. Downloading Excel instead.')
                ->warning()
                ->send();

            return $this->exportWaterUsageLogsExcel();
        }
    }
}