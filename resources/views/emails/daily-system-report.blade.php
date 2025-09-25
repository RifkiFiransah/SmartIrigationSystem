<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Harian Sistem Irigasi</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .header p { margin: 10px 0 0; opacity: 0.9; }
        .content { padding: 30px; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .summary-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; text-align: center; }
        .summary-card h3 { margin: 0 0 8px; font-size: 14px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card .value { font-size: 24px; font-weight: 600; color: #1e293b; }
        .table-section h2 { margin: 0 0 15px; color: #1e293b; font-size: 18px; }
        .table-container { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f1f5f9; color: #475569; font-weight: 600; padding: 12px 8px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .footer { background: #f8fafc; padding: 20px 30px; border-radius: 0 0 8px 8px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b; }
        .no-data { text-align: center; padding: 40px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Laporan Harian Sistem Irigasi</h1>
            <p>{{ $fromDate->format('d F Y') }} - {{ $toDate->format('d F Y') }}</p>
        </div>
        
        <div class="content">
            {{-- Summary Cards --}}
            @if(!empty($summary))
            <div class="summary-grid">
                <div class="summary-card">
                    <h3>Total Records</h3>
                    <div class="value">{{ number_format($summary['total_records'] ?? 0) }}</div>
                </div>
                <div class="summary-card">
                    <h3>Total Devices</h3>
                    <div class="value">{{ $summary['total_devices'] ?? 0 }}</div>
                </div>
                <div class="summary-card">
                    <h3>Irrigation Usage (L)</h3>
                    <div class="value">{{ isset($summary['total_irrigation_usage_delta_l']) ? number_format($summary['total_irrigation_usage_delta_l'], 2) : '—' }}</div>
                </div>
                <div class="summary-card">
                    <h3>Water Usage (L)</h3>
                    <div class="value">{{ isset($summary['total_water_usage_log_sum_l']) ? number_format($summary['total_water_usage_log_sum_l'], 2) : '—' }}</div>
                </div>
                <div class="summary-card">
                    <h3>Avg Soil Moisture (%)</h3>
                    <div class="value">{{ isset($summary['avg_soil_moisture_pct']) ? number_format($summary['avg_soil_moisture_pct'], 1) : '—' }}</div>
                </div>
                <div class="summary-card">
                    <h3>Avg Ground Temp (°C)</h3>
                    <div class="value">{{ isset($summary['avg_ground_temp_c']) ? number_format($summary['avg_ground_temp_c'], 1) : '—' }}</div>
                </div>
            </div>
            @endif

            {{-- Data Table --}}
            <div class="table-section">
                <h2>📋 Detail Data per Device</h2>
                @if(!empty($rows))
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Device</th>
                                <th class="text-right">Records</th>
                                <th class="text-right">Temp Avg (°C)</th>
                                <th class="text-right">Moisture Avg (%)</th>
                                <th class="text-right">Water Height (cm)</th>
                                <th class="text-right">Battery (V)</th>
                                <th class="text-right">Irrigation (L)</th>
                                <th class="text-right">Usage Log (L)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            <tr>
                                <td>{{ $row['tanggal'] }}</td>
                                <td>{{ $row['device_name'] }}</td>
                                <td class="text-right">{{ number_format($row['records_count']) }}</td>
                                <td class="text-right">{{ $row['ground_temp_avg'] ? number_format($row['ground_temp_avg'], 1) : '—' }}</td>
                                <td class="text-right">{{ $row['soil_moisture_avg'] ? number_format($row['soil_moisture_avg'], 1) : '—' }}</td>
                                <td class="text-right">{{ $row['water_height_avg'] ? number_format($row['water_height_avg'], 1) : '—' }}</td>
                                <td class="text-right">{{ $row['battery_voltage_avg'] ? number_format($row['battery_voltage_avg'], 2) : '—' }}</td>
                                <td class="text-right">{{ $row['irrigation_usage_delta_l'] ? number_format($row['irrigation_usage_delta_l'], 2) : '0.00' }}</td>
                                <td class="text-right">{{ $row['water_usage_log_sum_l'] ? number_format($row['water_usage_log_sum_l'], 2) : '0.00' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="no-data">
                    <p>🚫 Tidak ada data untuk periode ini</p>
                </div>
                @endif
            </div>
        </div>
        
        <div class="footer">
            <p>📧 Email ini dikirim secara otomatis oleh Smart Irrigation System<br>
            Dibuat pada {{ now()->format('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>