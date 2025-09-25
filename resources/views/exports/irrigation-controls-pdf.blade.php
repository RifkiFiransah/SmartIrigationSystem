<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Irrigation Controls Report</title>
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
        .status-running {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-stopped {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-scheduled {
            background-color: #fef3c7;
            color: #92400e;
        }
        .active-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .active-yes {
            background-color: #d1fae5;
            color: #065f46;
        }
        .active-no {
            background-color: #f3f4f6;
            color: #4b5563;
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
        <h1>Irrigation Controls Report</h1>
        <p>Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">{{ $controls->count() }}</div>
            <div class="summary-label">Total Controls</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $controls->where('is_active', true)->count() }}</div>
            <div class="summary-label">Active Sessions</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $controls->where('status', 'running')->count() }}</div>
            <div class="summary-label">Running</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ number_format($controls->avg('duration_minutes'), 0) }} min</div>
            <div class="summary-label">Avg Duration</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Device ID</th>
                <th>Device Name</th>
                <th>Mode</th>
                <th>Status</th>
                <th>Target Moisture (%)</th>
                <th>Duration (min)</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Is Active</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($controls as $control)
            <tr>
                <td>{{ $control->id }}</td>
                <td>{{ $control->device_id }}</td>
                <td>{{ $control->device?->device_name ?? '-' }}</td>
                <td>{{ ucfirst($control->mode) }}</td>
                <td>
                    <span class="status-badge status-{{ $control->status }}">
                        {{ ucfirst($control->status) }}
                    </span>
                </td>
                <td>{{ $control->target_moisture_pct }}%</td>
                <td>{{ $control->duration_minutes }}</td>
                <td>{{ $control->start_time ? $control->start_time->format('Y-m-d H:i') : '-' }}</td>
                <td>{{ $control->end_time ? $control->end_time->format('Y-m-d H:i') : '-' }}</td>
                <td>
                    <span class="active-badge active-{{ $control->is_active ? 'yes' : 'no' }}">
                        {{ $control->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>{{ $control->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Smart Irrigation System - Irrigation Control Sessions Report</p>
        <p>This report contains {{ $controls->count() }} irrigation control records</p>
    </div>
</body>
</html>