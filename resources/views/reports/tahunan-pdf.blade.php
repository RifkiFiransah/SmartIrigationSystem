<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tahunan - Smart Irrigation System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .date-range { text-align: center; margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .summary { margin-bottom: 20px; }
        .summary-item { display: inline-block; margin-right: 15px; margin-bottom: 10px; padding: 8px; background-color: #f9f9f9; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Tahunan</h1>
        <h2>Smart Irrigation System</h2>
    </div>
    
    <div class="date-range">
        <strong>Periode: {{ $dateRange }}</strong>
    </div>
    
    @if(!empty($summary))
    <div class="summary">
        <h3>Ringkasan</h3>
        <div class="summary-item">
            <strong>Total Records:</strong> {{ number_format($summary['total_records'] ?? 0) }}
        </div>
        <div class="summary-item">
            <strong>Total Devices:</strong> {{ $summary['total_devices'] ?? 0 }}
        </div>
        <div class="summary-item">
            <strong>Avg Temperature:</strong> {{ isset($summary['avg_ground_temp_c']) ? number_format($summary['avg_ground_temp_c'], 1) : '—' }}°C
        </div>
        <div class="summary-item">
            <strong>Avg Moisture:</strong> {{ isset($summary['avg_soil_moisture_pct']) ? number_format($summary['avg_soil_moisture_pct'], 1) : '—' }}%
        </div>
        <div class="summary-item">
            <strong>Total Water Usage:</strong> {{ isset($summary['total_water_usage_log_sum_l']) ? number_format($summary['total_water_usage_log_sum_l'], 1) : '0' }}L
        </div>
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Device</th>
                <th>Records</th>
                <th>Temp Avg</th>
                <th>Temp Min</th>
                <th>Temp Max</th>
                <th>Soil Avg</th>
                <th>Soil Min</th>
                <th>Soil Max</th>
                <th>Water Height</th>
                <th>Battery V</th>
                <th>Irrigation (L)</th>
                <th>Water Usage (L)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
            <tr>
                <td>{{ $row['tanggal'] }}</td>
                <td>{{ $row['device_name'] }}</td>
                <td>{{ $row['records_count'] ?? 0 }}</td>
                <td>{{ $row['ground_temp_avg'] ? number_format($row['ground_temp_avg'], 1) : '—' }}</td>
                <td>{{ $row['ground_temp_min'] ? number_format($row['ground_temp_min'], 1) : '—' }}</td>
                <td>{{ $row['ground_temp_max'] ? number_format($row['ground_temp_max'], 1) : '—' }}</td>
                <td>{{ $row['soil_moisture_avg'] ? number_format($row['soil_moisture_avg'], 1) : '—' }}</td>
                <td>{{ $row['soil_moisture_min'] ? number_format($row['soil_moisture_min'], 1) : '—' }}</td>
                <td>{{ $row['soil_moisture_max'] ? number_format($row['soil_moisture_max'], 1) : '—' }}</td>
                <td>{{ $row['water_height_avg'] ? number_format($row['water_height_avg'], 1) : '—' }}</td>
                <td>{{ $row['battery_voltage_avg'] ? number_format($row['battery_voltage_avg'], 2) : '—' }}</td>
                <td>{{ $row['irrigation_usage_delta_l'] ? number_format($row['irrigation_usage_delta_l'], 1) : '—' }}</td>
                <td>{{ $row['water_usage_log_sum_l'] ? number_format($row['water_usage_log_sum_l'], 1) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>