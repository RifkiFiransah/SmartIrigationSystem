<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulatin - Smart Irrigation System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .date-range { text-align: center; margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .summary { margin-bottom: 20px; }
        .summary-item { display: inline-block; margin-right: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Bulatin</h1>
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
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Device</th>
                <th>Records</th>
                <th>Ground Temp Avg</th>
                <th>Soil Moisture Avg</th>
                <th>Battery Voltage Avg</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
            <tr>
                <td>{{ $row['tanggal'] }}</td>
                <td>{{ $row['device_name'] }}</td>
                <td>{{ $row['records_count'] ?? 0 }}</td>
                <td>{{ $row['ground_temp_avg'] ? number_format($row['ground_temp_avg'], 1) : '—' }}</td>
                <td>{{ $row['soil_moisture_avg'] ? number_format($row['soil_moisture_avg'], 1) : '—' }}</td>
                <td>{{ $row['battery_voltage_avg'] ? number_format($row['battery_voltage_avg'], 2) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>