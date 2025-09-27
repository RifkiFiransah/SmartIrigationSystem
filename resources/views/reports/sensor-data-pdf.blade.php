<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Sensor Data - Smart Irrigation System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 15px; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .date-range { text-align: center; margin-bottom: 15px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 9px; }
        th, td { border: 1px solid #ddd; padding: 3px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .warning { background-color: #fef3c7; padding: 8px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Sensor Data</h1>
        <h2>Smart Irrigation System</h2>
    </div>
    
    <div class="date-range">
        <strong>Periode: {{ $dateRange }}</strong>
    </div>
    
    @if($truncated)
    <div class="warning">
        <strong>Peringatan:</strong> Dataset besar, hanya {{ $limit }} baris pertama yang ditampilkan dalam PDF ini.
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>Device ID</th>
                <th>Recorded At</th>
                <th>Ground Temp (°C)</th>
                <th>Soil Moisture (%)</th>
                <th>Water Height (cm)</th>
                <th>Battery (V)</th>
                <th>Irrigation (L)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sensorData as $data)
            <tr>
                <td>{{ $data->device_id }}</td>
                <td>{{ $data->recorded_at }}</td>
                <td class="text-right">{{ $data->ground_temperature_c ? number_format($data->ground_temperature_c, 1) : '—' }}</td>
                <td class="text-right">{{ $data->soil_moisture_pct ? number_format($data->soil_moisture_pct, 1) : '—' }}</td>
                <td class="text-right">{{ $data->water_height_cm ? number_format($data->water_height_cm, 1) : '—' }}</td>
                <td class="text-right">{{ $data->battery_voltage_v ? number_format($data->battery_voltage_v, 2) : '—' }}</td>
                <td class="text-right">{{ $data->irrigation_usage_total_l ? number_format($data->irrigation_usage_total_l, 2) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>