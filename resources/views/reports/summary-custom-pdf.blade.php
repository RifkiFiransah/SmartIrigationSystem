<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Ringkasan {{ $periodLabel }} - Smart Irrigation System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .date-range { text-align: center; margin-bottom: 20px; color: #666; }
        .period-info { text-align: center; margin-bottom: 20px; padding: 10px; background-color: #e3f2fd; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .summary { margin-bottom: 20px; }
        .summary-item { display: inline-block; margin-right: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 4px; }
        .no-data { text-align: center; padding: 20px; color: #666; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Ringkasan Custom</h1>
        <h2>Smart Irrigation System</h2>
    </div>
    
    <div class="period-info">
        <strong>Periode Agregasi: {{ $periodLabel }}</strong>
    </div>
    
    <div class="date-range">
        <strong>Range Data: {{ $dateRange }}</strong>
    </div>
    
    @if(!empty($reportData) && count($reportData) > 0)
        <table>
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Device</th>
                    <th class="text-right">Records</th>
                    <th class="text-right">Avg Temp (°C)</th>
                    <th class="text-right">Avg Moisture (%)</th>
                    <th class="text-right">Avg Battery (V)</th>
                    <th class="text-right">Water Usage (L)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $row)
                <tr>
                    <td>{{ $row['period'] ?? '—' }}</td>
                    <td>{{ $row['device_name'] ?? '—' }}</td>
                    <td class="text-right">{{ number_format($row['records_count'] ?? 0) }}</td>
                    <td class="text-right">{{ $row['avg_ground_temp_c'] ? number_format($row['avg_ground_temp_c'], 1) : '—' }}</td>
                    <td class="text-right">{{ $row['avg_soil_moisture_pct'] ? number_format($row['avg_soil_moisture_pct'], 1) : '—' }}</td>
                    <td class="text-right">{{ $row['avg_battery_voltage_v'] ? number_format($row['avg_battery_voltage_v'], 2) : '—' }}</td>
                    <td class="text-right">{{ number_format($row['total_water_usage_l'] ?? 0, 1) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="summary">
            <h3>Ringkasan Data</h3>
            <div class="summary-item">
                <strong>Total Entries:</strong> {{ count($reportData) }}
            </div>
            <div class="summary-item">
                <strong>Period Type:</strong> {{ $periodLabel }}
            </div>
            @if($period === 'custom')
            <div class="summary-item">
                <strong>Custom Range:</strong> {{ $customFromDate }} - {{ $customToDate }}
            </div>
            @endif
        </div>
    @else
        <div class="no-data">
            <h3>Tidak Ada Data</h3>
            <p>Tidak ada data sensor yang ditemukan untuk periode yang dipilih.</p>
        </div>
    @endif
    
    <div class="footer">
        <p>Generated on {{ now()->format('d/m/Y H:i:s') }} | Smart Irrigation System</p>
    </div>
</body>
</html>