<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Devices - Smart Irrigation System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .date-range { text-align: center; margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .status-active { color: #16a34a; font-weight: bold; }
        .status-inactive { color: #dc2626; font-weight: bold; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Devices</h1>
        <h2>Smart Irrigation System</h2>
    </div>
    
    <div class="date-range">
        <strong>Generated: {{ $dateRange }}</strong>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Device ID</th>
                <th>Device Name</th>
                <th>Location</th>
                <th>Status</th>
                <th>Valve State</th>
                <th>Connection</th>
                <th>Last Seen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devices as $device)
            <tr>
                <td class="text-center">{{ $device['id'] ?? $device->id }}</td>
                <td>{{ $device['device_id'] ?? $device->device_id }}</td>
                <td>{{ $device['device_name'] ?? $device->device_name }}</td>
                <td>{{ $device['location'] ?? $device->location }}</td>
                <td class="{{ ($device['is_active'] ?? $device->is_active) ? 'status-active' : 'status-inactive' }}">
                    {{ ($device['is_active'] ?? $device->is_active) ? 'Active' : 'Inactive' }}
                </td>
                <td>{{ ucfirst($device['valve_state'] ?? $device->valve_state ?? 'unknown') }}</td>
                <td>{{ ucfirst($device['connection_state'] ?? $device->connection_state ?? 'unknown') }}</td>
                <td>{{ $device['last_seen_at'] ?? $device->last_seen_at ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>