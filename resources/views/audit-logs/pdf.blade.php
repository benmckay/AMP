<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Logs Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { margin: 0 0 6px; font-size: 18px; }
        .meta { margin-bottom: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; vertical-align: top; }
        th { background: #f3f6f8; }
    </style>
</head>
<body>
    <h1>Audit Logs</h1>
    <div class="meta">
        Date range: {{ $from }} to {{ $to }}<br>
        Action filter: {{ $action !== '' ? $action : 'Any' }}<br>
        Search filter: {{ $search !== '' ? $search : 'Any' }}<br>
        Generated at: {{ $generatedAt->format('Y-m-d H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Model</th>
                <th>IP</th>
                <th>Changes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($auditLogs as $log)
                <tr>
                    <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->user?->name ?? 'System' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ class_basename((string) $log->model_type) }} #{{ $log->model_id }}</td>
                    <td>{{ $log->ip_address }}</td>
                    <td>{{ is_array($log->changes) ? json_encode($log->changes) : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No audit logs in selected range.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

