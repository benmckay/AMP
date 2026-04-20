<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { margin: 0 0 6px; font-size: 20px; }
        .meta { margin-bottom: 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f6f8; }
        .stats td { width: 33%; }
    </style>
</head>
<body>
    <h1>Reports Summary</h1>
    <div class="meta">
        Date range: {{ $from }} to {{ $to }}<br>
        Generated at: {{ $generatedAt->format('Y-m-d H:i:s') }}
    </div>

    <table class="stats">
        <tr>
            <th>Total</th><th>Pending</th><th>Approved</th>
        </tr>
        <tr>
            <td>{{ $stats['total'] }}</td><td>{{ $stats['pending'] }}</td><td>{{ $stats['approved'] }}</td>
        </tr>
        <tr>
            <th>Fulfilled</th><th>Rejected</th><th>Cancelled</th>
        </tr>
        <tr>
            <td>{{ $stats['fulfilled'] }}</td><td>{{ $stats['rejected'] }}</td><td>{{ $stats['cancelled'] }}</td>
        </tr>
    </table>

    <h3 style="margin-top:16px;">Recent Requests</h3>
    <table>
        <thead>
            <tr>
                <th>Request #</th>
                <th>Requester</th>
                <th>Target User</th>
                <th>Template</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentRequests as $request)
                <tr>
                    <td>{{ $request->request_number }}</td>
                    <td>{{ $request->requester?->name ?? 'N/A' }}</td>
                    <td>{{ $request->full_name }}</td>
                    <td>{{ $request->template?->display_name ?? 'N/A' }}</td>
                    <td>{{ $request->status }}</td>
                    <td>{{ optional($request->submitted_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No requests in selected range.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

