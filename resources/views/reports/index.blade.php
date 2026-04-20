@extends('layouts.app')

@section('title', 'Reports')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('reports.index') }}" class="nav-link active">
        <i class="bi bi-graph-up"></i> Reports
    </a>
    <a href="{{ route('audit-logs.index') }}" class="nav-link">
        <i class="bi bi-shield-check"></i> Audit Logs
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h1 class="page-title mb-0">Reports</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.export.csv', request()->query()) }}" class="btn btn-outline-primary">
                    <i class="bi bi-filetype-csv"></i> Export CSV
                </a>
                <a href="{{ route('reports.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
                    <i class="bi bi-filetype-pdf"></i> Export PDF
                </a>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="from" class="form-label">From</label>
                    <input type="date" id="from" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="to" class="form-label">To</label>
                    <input type="date" id="to" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['approved'] }}</div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['fulfilled'] }}</div>
                <div class="stat-label">Fulfilled</div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['rejected'] }}</div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['cancelled'] }}</div>
                <div class="stat-label">Cancelled</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Average Fulfillment Time</h6>
                <small class="text-muted">Based on fulfilled requests in selected range</small>
            </div>
            <div class="fs-4 fw-semibold">
                {{ $avgFulfillmentHours ? number_format($avgFulfillmentHours, 1) . ' hrs' : 'N/A' }}
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Requests Over Time</span>
                    <i class="bi bi-calendar3 text-muted"></i>
                </div>
                <div class="card-body">
                    <canvas id="requestsChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Status Distribution</span>
                    <i class="bi bi-pie-chart text-muted"></i>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">By Status</div>
                <div class="card-body">
                    @forelse($byStatus as $item)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-capitalize">{{ str_replace('_', ' ', $item->status) }}</span>
                            <strong>{{ $item->total }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No data in selected range.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">By Request Type</div>
                <div class="card-body">
                    @forelse($byType as $item)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-capitalize">{{ str_replace('_', ' ', $item->request_type) }}</span>
                            <strong>{{ $item->total }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No data in selected range.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Top Departments</div>
                <div class="card-body">
                    @forelse($byDepartment as $item)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>{{ $item->department_name ?? 'Unassigned' }}</span>
                            <strong>{{ $item->total }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No data in selected range.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Recent Requests</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>User</th>
                            <th>Template</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                            <tr>
                                <td>{{ $request->request_number }}</td>
                                <td>
                                    <div>{{ $request->full_name }}</div>
                                    <small class="text-muted">{{ $request->email }}</small>
                                </td>
                                <td>{{ $request->template?->display_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $request->status_color }} text-capitalize">
                                        {{ str_replace('_', ' ', $request->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ optional($request->submitted_at)->format('M d, Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No requests found for selected range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Requests Over Time Chart
            const requestsCtx = document.getElementById('requestsChart').getContext('2d');
            new Chart(requestsCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($requestsByDate->pluck('date')) !!},
                    datasets: [{
                        label: 'Total Requests',
                        data: {!! json_encode($requestsByDate->pluck('total')) !!},
                        borderColor: '#008B8B',
                        backgroundColor: 'rgba(0, 139, 139, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
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

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusData = {!! json_encode($byStatus) !!};
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusData.map(i => i.status.charAt(0).toUpperCase() + i.status.slice(1)),
                    datasets: [{
                        data: statusData.map(i => i.total),
                        backgroundColor: [
                            '#ffc107', // pending
                            '#17a2b8', // approved
                            '#28a745', // fulfilled
                            '#dc3545', // rejected
                            '#6c757d', // cancelled
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
@endpush
