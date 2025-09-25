<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Data Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1f2937;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #6b7280;
            margin: 5px 0 0 0;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #3b82f6;
        }
        .summary-label {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #3b82f6;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sensor Data Report</h1>
        <p>Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">{{ $sensorData->count() }}</div>
            <div class="summary-label">Total Readings</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $sensorData->unique('device_id')->count() }}</div>
            <div class="summary-label">Unique Devices</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ number_format($sensorData->avg('temperature_celsius'), 1) }}°C</div>
            <div class="summary-label">Avg Temperature</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ number_format($sensorData->avg('humidity_percent'), 1) }}%</div>
            <div class="summary-label">Avg Humidity</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Device</th>
                <th>Temperature (°C)</th>
                <th>Humidity (%)</th>
                <th>Soil Moisture (%)</th>
                <th>pH Level</th>
                <th>Light Intensity</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sensorData as $reading)
            <tr>
                <td>{{ $reading->id }}</td>
                <td>{{ $reading->device?->device_name ?? $reading->device_id }}</td>
                <td>{{ number_format($reading->temperature_celsius, 1) }}</td>
                <td>{{ number_format($reading->humidity_percent, 1) }}</td>
                <td>{{ number_format($reading->soil_moisture_percent, 1) }}</td>
                <td>{{ number_format($reading->ph_level, 2) }}</td>
                <td>{{ $reading->light_intensity ?? '-' }}</td>
                <td>{{ $reading->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Smart Irrigation System - Environmental Sensor Data Report</p>
        <p>This report contains {{ $sensorData->count() }} sensor readings (limited to latest 1000 records)</p>
    </div>
</body>
</html>