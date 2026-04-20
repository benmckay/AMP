@extends('layouts.app')

@section('title', 'Audit Logs')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reports.index') }}" class="nav-link">
        <i class="bi bi-graph-up"></i> Reports
    </a>
    <a href="{{ route('audit-logs.index') }}" class="nav-link active">
        <i class="bi bi-shield-check"></i> Audit Logs
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h1 class="page-title mb-0">Audit Logs</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('audit-logs.export.csv', request()->query()) }}" class="btn btn-outline-primary">
                    <i class="bi bi-filetype-csv"></i> Export CSV
                </a>
                <a href="{{ route('audit-logs.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
                    <i class="bi bi-filetype-pdf"></i> Export PDF
                </a>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Audit Logs</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="from" class="form-label">From</label>
                    <input type="date" id="from" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="to" class="form-label">To</label>
                    <input type="date" id="to" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="action" class="form-label">Action</label>
                    <input type="text" id="action" name="action" value="{{ $action }}" class="form-control" placeholder="e.g. otp.verified">
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="user, model, ip">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Events</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['unique_users'] }}</div>
                <div class="stat-label">Distinct Users</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['unique_actions'] }}</div>
                <div class="stat-label">Action Types</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Top Actions</span>
                    <i class="bi bi-activity text-muted"></i>
                </div>
                <div class="card-body">
                    <canvas id="actionsChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Top Actions</div>
                <div class="card-body">
                    @forelse($topActions as $item)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>{{ $item->action }}</span>
                            <strong>{{ $item->total }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No actions found in selected range.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Audit Events</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                                <td>
                                    {{ optional($log->created_at)->format('M d, Y H:i:s') }}
                                    <br>
                                    <small class="text-muted">{{ optional($log->created_at)->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div>{{ $log->user->name }}</div>
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ $log->action }}</span></td>
                                <td>
                                    <div>{{ class_basename((string) $log->model_type) ?: 'N/A' }}</div>
                                    <small class="text-muted">ID: {{ $log->model_id ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                <td>
                                    @if(is_array($log->changes) && count($log->changes) > 0)
                                        <details>
                                            <summary class="text-primary">View</summary>
                                            <pre class="small bg-light p-2 rounded mt-2 mb-0">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $auditLogs->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('actionsChart').getContext('2d');
            const topActions = {!! json_encode($topActions) !!};
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topActions.map(i => i.action),
                    datasets: [{
                        label: 'Total Events',
                        data: topActions.map(i => i.total),
                        backgroundColor: '#008B8B',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        });
    </script>
@endpush
