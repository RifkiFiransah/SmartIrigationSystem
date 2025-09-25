<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Device Export</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #2563eb; margin: 0; }
        .header p { color: #6b7280; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .status { padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        .status.active { background-color: #dcfce7; color: #166534; }
        .status.inactive { background-color: #fee2e2; color: #dc2626; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📱 Laporan Data Device</h1>
        <p>Smart Irrigation System</p>
        <p>Tanggal Export: {{ now()->format('d F Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Device ID</th>
                <th>Nama Device</th>
                <th>Lokasi</th>
                <th>Status Aktif</th>
                <th>Valve</th>
                <th>Koneksi</th>
                <th>Terakhir Dilihat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devices as $device)
            <tr>
                <td>{{ $device->id }}</td>
                <td>{{ $device->device_id }}</td>
                <td>{{ $device->device_name }}</td>
                <td>{{ $device->location }}</td>
                <td>
                    <span class="status {{ $device->is_active ? 'active' : 'inactive' }}">
                        {{ $device->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </td>
                <td>{{ ucfirst($device->valve_state) }}</td>
                <td>{{ ucfirst($device->connection_state) }}</td>
                <td>{{ $device->last_seen_at?->format('d/m/Y H:i') ?? 'Tidak ada' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($devices->count() == 0)
        <p style="text-align: center; margin-top: 50px; color: #6b7280;">Tidak ada data device.</p>
    @endif

    <div class="footer">
        <p>Total: {{ $devices->count() }} device(s) | Dibuat oleh Smart Irrigation System</p>
    </div>
</body>
</html>