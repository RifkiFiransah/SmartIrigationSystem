<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Irrigation Valve Logs Report</title>
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
        .action-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .action-open {
            background-color: #d1fae5;
            color: #065f46;
        }
        .action-close {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .action-maintain {
            background-color: #fef3c7;
            color: #92400e;
        }
        .state-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .state-open {
            background-color: #dcfce7;
            color: #166534;
        }
        .state-closed {
            background-color: #fecaca;
            color: #dc2626;
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
        <h1>Irrigation Valve Logs Report</h1>
        <p>Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">{{ $logs->count() }}</div>
            <div class="summary-label">Total Log Entries</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $logs->unique('valve_id')->count() }}</div>
            <div class="summary-label">Unique Valves</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ number_format($logs->sum('water_used_liters'), 0) }}L</div>
            <div class="summary-label">Total Water Used</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ number_format($logs->avg('duration_minutes'), 0) }} min</div>
            <div class="summary-label">Avg Duration</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Valve ID</th>
                <th>Device ID</th>
                <th>Action</th>
                <th>Old State</th>
                <th>New State</th>
                <th>Duration (min)</th>
                <th>Water Used (L)</th>
                <th>Timestamp</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->valve_id }}</td>
                <td>{{ $log->valve?->device_id ?? '-' }}</td>
                <td>
                    <span class="action-badge action-{{ strtolower($log->action) }}">
                        {{ ucfirst($log->action) }}
                    </span>
                </td>
                <td>
                    @if($log->old_state)
                        <span class="state-badge state-{{ strtolower($log->old_state) }}">
                            {{ ucfirst($log->old_state) }}
                        </span>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($log->new_state)
                        <span class="state-badge state-{{ strtolower($log->new_state) }}">
                            {{ ucfirst($log->new_state) }}
                        </span>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $log->duration_minutes ?? '-' }}</td>
                <td>{{ $log->water_used_liters ? number_format($log->water_used_liters, 1) : '-' }}</td>
                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $log->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Smart Irrigation System - Valve Activity Logs Report</p>
        <p>This report contains {{ $logs->count() }} valve log entries</p>
    </div>
</body>
</html>