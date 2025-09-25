<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Irrigation Valve Schedules Report</title>
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
        .days-badge {
            display: inline-block;
            padding: 1px 4px;
            margin: 1px;
            background-color: #e0e7ff;
            color: #3730a3;
            border-radius: 3px;
            font-size: 9px;
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
        <h1>Irrigation Valve Schedules Report</h1>
        <p>Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">{{ $schedules->count() }}</div>
            <div class="summary-label">Total Schedules</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $schedules->where('is_active', true)->count() }}</div>
            <div class="summary-label">Active Schedules</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $schedules->unique('valve_id')->count() }}</div>
            <div class="summary-label">Unique Valves</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ number_format($schedules->avg('duration_minutes'), 0) }} min</div>
            <div class="summary-label">Avg Duration</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Valve ID</th>
                <th>Device ID</th>
                <th>Schedule Name</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration (min)</th>
                <th>Days of Week</th>
                <th>Is Active</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $schedule)
            <tr>
                <td>{{ $schedule->id }}</td>
                <td>{{ $schedule->valve_id }}</td>
                <td>{{ $schedule->valve?->device_id ?? '-' }}</td>
                <td>{{ $schedule->schedule_name ?? '-' }}</td>
                <td>{{ $schedule->start_time }}</td>
                <td>{{ $schedule->end_time }}</td>
                <td>{{ $schedule->duration_minutes }}</td>
                <td>
                    @if($schedule->days_of_week)
                        @foreach(explode(',', $schedule->days_of_week) as $day)
                            <span class="days-badge">{{ trim($day) }}</span>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td>
                    <span class="active-badge active-{{ $schedule->is_active ? 'yes' : 'no' }}">
                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>{{ $schedule->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Smart Irrigation System - Valve Schedules Report</p>
        <p>This report contains {{ $schedules->count() }} valve schedule records</p>
    </div>
</body>
</html>