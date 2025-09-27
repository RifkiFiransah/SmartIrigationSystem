<!DOCTYPE html>
<html>
<head>
    <title>Export Water Usage Logs - {{ now()->format('Y-m-d') }}</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #0284c7;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f9ff;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary {
            background-color: #f0f9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #0284c7;
        }
        .usage-high {
            color: #dc2626;
            font-weight: bold;
        }
        .usage-medium {
            color: #ca8a04;
            font-weight: bold;
        }
        .usage-low {
            color: #16a34a;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Log Penggunaan Air</h1>
        <p><strong>Tanggal Export:</strong> {{ now()->format('d F Y H:i:s') }}</p>
        <p><strong>Total Log:</strong> {{ count($waterUsageLogs) }}</p>
    </div>

    <div class="summary">
        <h3>Statistik Penggunaan</h3>
        <p>
            <strong>Total Air Digunakan:</strong> {{ number_format($waterUsageLogs->sum('volume_used_l'), 2) }} L |
            <strong>Rata-rata per Log:</strong> {{ count($waterUsageLogs) > 0 ? number_format($waterUsageLogs->sum('volume_used_l') / count($waterUsageLogs), 2) : '0' }} L |
            <strong>Devices Aktif:</strong> {{ $waterUsageLogs->pluck('device_name')->unique()->count() }}
        </p>
    </div>

    @if(count($waterUsageLogs) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%">ID</th>
                                                <th style="width: 15%">Device</th>
                            <th style="width: 15%">Tank</th>
                    <th style="width: 15%">Tanggal</th>
                    <th style="width: 15%">Air Digunakan (L)</th>
                    <th style="width: 15%">Durasi (Menit)</th>
                    <th style="width: 27%">Dicatat Pada</th>
                </tr>
            </thead>
            <tbody>
                @foreach($waterUsageLogs as $log)
                    @php
                        $waterUsed = $log->volume_used_l;
                        $usageClass = $waterUsed >= 100 ? 'usage-high' : ($waterUsed >= 50 ? 'usage-medium' : 'usage-low');
                    @endphp
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td><strong>{{ $log->device_name ?? '-' }}</strong></td>
                        <td><strong>{{ $log->tank_name ?? '-' }}</strong></td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($log->usage_date)->format('d/m/Y') }}</td>
                        <td class="text-right {{ $usageClass }}">{{ number_format($log->volume_used_l, 2) }}</td>
                        <td class="text-right">{{ ucfirst($log->source ?? '-') }}</td>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px;">
            <h4 style="margin: 0 0 10px 0; color: #333;">Keterangan Warna:</h4>
            <p style="margin: 5px 0;">
                <span class="usage-low">Hijau:</span> Penggunaan Rendah (&lt; 50L) |
                <span class="usage-medium">Kuning:</span> Penggunaan Sedang (50-99L) |
                <span class="usage-high">Merah:</span> Penggunaan Tinggi (≥100L)
            </p>
        </div>
    @else
        <div style="text-align: center; padding: 50px; color: #666;">
            <h3>Tidak ada log penggunaan air</h3>
            <p>Belum ada catatan penggunaan air dalam sistem.</p>
        </div>
    @endif

    <div class="footer">
        <p><strong>Smart Irrigation System</strong> | Generated by System Report</p>
        <p>© {{ now()->year }} - Export Water Usage Logs Report</p>
    </div>
</body>
</html>