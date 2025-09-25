<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Storages Report</title>
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
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-maintenance {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-offline {
            background-color: #fee2e2;
            color: #991b1b;
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
        <h1>Water Storages Report</h1>
        <p>Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">{{ $waterStorages->count() }}</div>
            <div class="summary-label">Total Storages</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $waterStorages->where('status', 'active')->count() }}</div>
            <div class="summary-label">Active</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ number_format($waterStorages->sum('capacity_liters'), 0) }}L</div>
            <div class="summary-label">Total Capacity</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ number_format($waterStorages->sum('current_volume_liters'), 0) }}L</div>
            <div class="summary-label">Current Volume</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tank Name</th>
                <th>Device ID</th>
                <th>Capacity (L)</th>
                <th>Current Volume (L)</th>
                <th>Status</th>
                <th>Max Daily Usage (L)</th>
                <th>Height (cm)</th>
                <th>Last Height (cm)</th>
                <th>Zone Name</th>
                <th>Area Size (m²)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($waterStorages as $storage)
            <tr>
                <td>{{ $storage->id }}</td>
                <td>{{ $storage->tank_name }}</td>
                <td>{{ $storage->device_id }}</td>
                <td>{{ number_format($storage->capacity_liters, 0) }}</td>
                <td>{{ number_format($storage->current_volume_liters, 0) }}</td>
                <td>
                    <span class="status-badge status-{{ $storage->status }}">
                        {{ ucfirst($storage->status) }}
                    </span>
                </td>
                <td>{{ number_format($storage->max_daily_usage, 0) }}</td>
                <td>{{ number_format($storage->height_cm, 1) }}</td>
                <td>{{ number_format($storage->last_height_cm, 1) }}</td>
                <td>{{ $storage->zone_name }}</td>
                <td>{{ number_format($storage->area_size_sqm, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Smart Irrigation System - Water Storage Management Report</p>
        <p>This report contains {{ $waterStorages->count() }} water storage records</p>
    </div>
</body>
</html>