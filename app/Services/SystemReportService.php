<?php

namespace App\Services;

use App\Models\Device;
use App\Models\WaterStorage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SystemReportService
{
    public function buildAggregation(Carbon $from, Carbon $to, array $deviceIds = [], bool $onlyActive = false): array
    {
        $sensorQuery = DB::table('sensor_data')
            ->selectRaw('DATE(recorded_at) as tanggal, device_id')
            ->selectRaw('COUNT(*) as records_count')
            ->selectRaw('AVG(ground_temperature_c) as ground_temp_avg')
            ->selectRaw('MIN(ground_temperature_c) as ground_temp_min')
            ->selectRaw('MAX(ground_temperature_c) as ground_temp_max')
            ->selectRaw('AVG(soil_moisture_pct) as soil_moisture_avg')
            ->selectRaw('MIN(soil_moisture_pct) as soil_moisture_min')
            ->selectRaw('MAX(soil_moisture_pct) as soil_moisture_max')
            ->selectRaw('AVG(water_height_cm) as water_height_avg')
            ->selectRaw('AVG(battery_voltage_v) as battery_voltage_avg')
            ->selectRaw('MIN(battery_voltage_v) as battery_voltage_min')
            ->selectRaw('MAX(irrigation_usage_total_l) - MIN(irrigation_usage_total_l) as irrigation_usage_delta_l')
            ->whereBetween('recorded_at', [$from, $to])
            ->groupBy('tanggal', 'device_id');

        if ($deviceIds) {
            $sensorQuery->whereIn('device_id', $deviceIds);
        }
        if ($onlyActive) {
            $sensorQuery->whereIn('device_id', Device::where('is_active', true)->pluck('id'));
        }
        $sensorRows = $sensorQuery->get()->map(fn ($r) => (array) $r);

        $usageQuery = DB::table('water_usage_logs')
            ->selectRaw('usage_date as tanggal, device_id')
            ->selectRaw('SUM(volume_used_l) as water_usage_log_sum_l')
            ->whereBetween('usage_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->groupBy('tanggal', 'device_id');
        if ($deviceIds) {
            $usageQuery->whereIn('device_id', $deviceIds);
        }
        if ($onlyActive) {
            $usageQuery->whereIn('device_id', Device::where('is_active', true)->pluck('id'));
        }
        $usageRows = $usageQuery->get()->map(fn ($r) => (array) $r);

        $index = [];
        foreach ($sensorRows as $row) {
            $key = $row['tanggal'] . ':' . $row['device_id'];
            $index[$key] = $row + ['water_usage_log_sum_l' => 0.0];
        }
        foreach ($usageRows as $row) {
            $key = $row['tanggal'] . ':' . $row['device_id'];
            if (! isset($index[$key])) {
                $index[$key] = [
                    'tanggal' => $row['tanggal'],
                    'device_id' => $row['device_id'],
                    'records_count' => 0,
                    'ground_temp_avg' => null,
                    'ground_temp_min' => null,
                    'ground_temp_max' => null,
                    'soil_moisture_avg' => null,
                    'soil_moisture_min' => null,
                    'soil_moisture_max' => null,
                    'water_height_avg' => null,
                    'battery_voltage_avg' => null,
                    'battery_voltage_min' => null,
                    'irrigation_usage_delta_l' => null,
                    'water_usage_log_sum_l' => 0.0,
                ];
            }
            $index[$key]['water_usage_log_sum_l'] = (float) $row['water_usage_log_sum_l'];
        }

        $deviceListQuery = Device::query();
        if ($onlyActive) $deviceListQuery->where('is_active', true);
        $deviceNames = $deviceListQuery->pluck('device_name', 'id');

        $rows = collect($index)->values()->map(function ($row) use ($deviceNames) {
            $row['device_name'] = $deviceNames[$row['device_id']] ?? ('Device #' . $row['device_id']);
            if (! is_null($row['irrigation_usage_delta_l']) && $row['irrigation_usage_delta_l'] < 0) {
                $row['irrigation_usage_delta_l'] = null;
            }
            return $row;
        })->sortBy(['tanggal', 'device_name'])->values()->all();

        $summary = [
            'total_records' => array_sum(array_column($rows, 'records_count')),
            'total_devices' => collect($rows)->pluck('device_id')->unique()->count(),
            'total_irrigation_usage_delta_l' => collect($rows)->sum(fn ($r) => $r['irrigation_usage_delta_l'] ?? 0),
            'total_water_usage_log_sum_l' => collect($rows)->sum(fn ($r) => $r['water_usage_log_sum_l'] ?? 0),
            'avg_soil_moisture_pct' => round(collect($rows)->avg(fn ($r) => $r['soil_moisture_avg'] ?? null), 2),
        ];

        return compact('rows', 'summary');
    }

    public function getDevices(array $filter): \Illuminate\Support\Collection
    {
        $q = Device::query();
        if ($filter['only_active'] ?? false) $q->where('is_active', true);
        if (!empty($filter['device_ids'])) $q->whereIn('id', $filter['device_ids']);
        return $q->select([
            'id','device_id','device_name','location','is_active','valve_state','connection_state','connection_state_source','last_seen_at','valve_state_changed_at','description'
        ])->orderBy('device_name')->get();
    }

    public function getWaterStorages(array $filter): \Illuminate\Support\Collection
    {
        $q = WaterStorage::query();
        if ($filter['only_active'] ?? false) {
            // filter device aktif jika relasi device ada
            $q->whereHas('device', function($d){ $d->where('is_active', true); });
        }
        if (!empty($filter['device_ids'])) {
            $q->whereIn('device_id', $filter['device_ids']);
        }
        return $q->select([
            'id','tank_name','device_id','capacity_liters','current_volume_liters','status','max_daily_usage','height_cm','last_height_cm','zone_name','area_size_sqm'
        ])->orderBy('tank_name')->get();
    }

    public function getSensorData(array $filter, Carbon $from, Carbon $to, int $limit = 50000): array
    {
        $baseQuery = DB::table('sensor_data')
            ->whereBetween('recorded_at', [$from, $to]);
        if (!empty($filter['device_ids'])) $baseQuery->whereIn('device_id', $filter['device_ids']);
        if ($filter['only_active'] ?? false) {
            $baseQuery->whereIn('device_id', Device::where('is_active', true)->pluck('id'));
        }
        $count = (clone $baseQuery)->count();
        $truncated = false;
        if ($count > $limit) {
            $truncated = true;
        }
        $rows = (clone $baseQuery)
            ->select(['device_id','recorded_at','ground_temperature_c','soil_moisture_pct','water_height_cm','battery_voltage_v','irrigation_usage_total_l'])
            ->orderBy('recorded_at')
            ->when($truncated, fn($q)=>$q->limit($limit))
            ->get();
        return ['rows'=>$rows,'count'=>$count,'truncated'=>$truncated,'limit'=>$limit];
    }

    public function getWaterUsageLogs(array $filter, Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        $q = DB::table('water_usage_logs')
            ->whereBetween('usage_date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);
        if (!empty($filter['device_ids'])) $q->whereIn('device_id', $filter['device_ids']);
        if ($filter['only_active'] ?? false) {
            $q->whereIn('device_id', Device::where('is_active', true)->pluck('id'));
        }
        return $q->select(['device_id','usage_date','volume_used_l','source'])->orderBy('usage_date')->get();
    }

    public function counts(array $filter, Carbon $from, Carbon $to): array
    {
        $devices = $this->getDevices($filter)->count();
        $storages = $this->getWaterStorages($filter)->count();
        $sensorCount = DB::table('sensor_data')
            ->when(!empty($filter['device_ids']), fn($q)=>$q->whereIn('device_id',$filter['device_ids']))
            ->when($filter['only_active'] ?? false, fn($q)=>$q->whereIn('device_id', Device::where('is_active',true)->pluck('id')))
            ->whereBetween('recorded_at', [$from, $to])
            ->count();
        $usageCount = DB::table('water_usage_logs')
            ->when(!empty($filter['device_ids']), fn($q)=>$q->whereIn('device_id',$filter['device_ids']))
            ->when($filter['only_active'] ?? false, fn($q)=>$q->whereIn('device_id', Device::where('is_active',true)->pluck('id')))
            ->whereBetween('usage_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->count();
        return [
            'devices' => $devices,
            'water_storages' => $storages,
            'sensor_data' => $sensorCount,
            'water_usage_logs' => $usageCount,
        ];
    }
}
