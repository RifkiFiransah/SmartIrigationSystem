<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Water Storages - Smart Irrigation System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .date-range { text-align: center; margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .status-active { color: #16a34a; font-weight: bold; }
        .status-inactive { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Water Storages</h1>
        <h2>Smart Irrigation System</h2>
    </div>
    
    <div class="date-range">
        <strong>Generated: {{ $dateRange }}</strong>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tank Name</th>
                <th>Device ID</th>
                <th>Capacity (L)</th>
                <th>Current Vol (L)</th>
                <th>Status</th>
                <th>Max Daily (L)</th>
                <th>Height (cm)</th>
                <th>Zone</th>
                <th>Area (m²)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($storages as $storage)
            <tr>
                <td class="text-center">{{ $storage['id'] ?? $storage->id }}</td>
                <td>{{ $storage['tank_name'] ?? $storage->tank_name }}</td>
                <td>{{ $storage['device_id'] ?? $storage->device_id }}</td>
                <td class="text-right">{{ number_format($storage['capacity_liters'] ?? $storage->capacity_liters ?? 0, 0) }}</td>
                <td class="text-right">{{ number_format($storage['current_volume_liters'] ?? $storage->current_volume_liters ?? 0, 1) }}</td>
                <td class="{{ ($storage['status'] ?? $storage->status) == 'active' ? 'status-active' : 'status-inactive' }}">
                    {{ ucfirst($storage['status'] ?? $storage->status ?? 'unknown') }}
                </td>
                <td class="text-right">{{ number_format($storage['max_daily_usage'] ?? $storage->max_daily_usage ?? 0, 1) }}</td>
                <td class="text-right">{{ number_format($storage['height_cm'] ?? $storage->height_cm ?? 0, 1) }}</td>
                <td>{{ $storage['zone_name'] ?? $storage->zone_name ?? '—' }}</td>
                <td class="text-right">{{ number_format($storage['area_size_sqm'] ?? $storage->area_size_sqm ?? 0, 1) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>