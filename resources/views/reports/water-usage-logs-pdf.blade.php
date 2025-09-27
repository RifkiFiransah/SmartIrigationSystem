<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Water Usage Logs - Smart Irrigation System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .date-range { text-align: center; margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Water Usage Logs</h1>
        <h2>Smart Irrigation System</h2>
    </div>
    
    <div class="date-range">
        <strong>Periode: {{ $dateRange }}</strong>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Device ID</th>
                <th>Usage Date</th>
                <th>Volume Used (L)</th>
                <th>Source</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->device_id }}</td>
                <td class="text-center">{{ $log->usage_date }}</td>
                <td class="text-right">{{ number_format($log->volume_used_l, 2) }}</td>
                <td>{{ ucfirst($log->source) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>