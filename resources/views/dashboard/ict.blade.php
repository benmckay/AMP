@extends('layouts.app')

@section('title', 'ICT Admin Dashboard')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.fulfillment-queue') }}" class="nav-link">
        <i class="bi bi-list-task"></i> Fulfillment Queue
        @if($stats['awaiting_fulfillment'] > 0)
            <span class="badge bg-info ms-2">{{ $stats['awaiting_fulfillment'] }}</span>
        @endif
    </a>
    <a href="{{ route('templates.index') }}" class="nav-link">
        <i class="bi bi-folder"></i> Manage Templates
    </a>
    <a href="{{ route('users.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Manage Users
    </a>
    <a href="{{ route('departments.index') }}" class="nav-link">
        <i class="bi bi-building"></i> Departments
    </a>
    <a href="{{ route('reports.index') }}" class="nav-link">
        <i class="bi bi-graph-up"></i> Reports
    </a>
    <a href="{{ route('audit-logs.index') }}" class="nav-link">
        <i class="bi bi-shield-check"></i> Audit Logs
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">ICT Admin Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">ICT Admin Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #17a2b8;">
                <div class="stat-value" style="color: #17a2b8;">{{ $stats['awaiting_fulfillment'] }}</div>
                <div class="stat-label">Awaiting Fulfillment</div>
                <a href="{{ route('requests.fulfillment-queue') }}" class="btn btn-sm btn-link p-0 mt-2">
                    View Queue <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #28a745;">
                <div class="stat-value" style="color: #28a745;">{{ $stats['fulfilled_today'] }}</div>
                <div class="stat-label">Fulfilled Today</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #007bff;">
                <div class="stat-value" style="color: #007bff;">{{ $stats['fulfilled_this_month'] }}</div>
                <div class="stat-label">Fulfilled This Month</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #6f42c1;">
                <div class="stat-value" style="color: #6f42c1;">{{ $stats['active_templates'] }}</div>
                <div class="stat-label">Active Templates</div>
                <small class="text-muted d-block mt-1">{{ $stats['total_templates'] }} total</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Fulfillment Queue -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-task"></i> Fulfillment Queue</span>
                    <a href="{{ route('requests.fulfillment-queue') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($fulfillmentQueue->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request #</th>
                                        <th>Template</th>
                                        <th>User</th>
                                        <th>Approved By</th>
                                        <th>Approved</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fulfillmentQueue as $request)
                                        <tr>
                                            <td>
                                                <strong>{{ $request->request_number }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $request->template->mnemonic }}</span>
                                                <small class="d-block text-muted">{{ $request->template->department->name }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $request->full_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $request->email }}</small>
                                            </td>
                                            <td>{{ $request->approvedBy->name }}</td>
                                            <td>
                                                {{ $request->approved_at->format('M d, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $request->approved_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-success"
                                                        onclick="fulfillRequest({{ $request->id }})">
                                                    <i class="bi bi-check-circle"></i> Fulfill
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $fulfillmentQueue->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                            <p class="text-muted mt-3">No requests awaiting fulfillment!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Department Statistics -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-building"></i> Department Overview
                </div>
                <div class="card-body">
                    @foreach($departmentStats as $dept)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $dept->name }}</h6>
                                    <small class="text-muted">{{ $dept->code }}</small>
                                </div>
                                <span class="badge bg-primary">{{ $dept->templates_count }} templates</span>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-file-earmark"></i> {{ $dept->access_requests_count }} requests
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Recently Fulfilled
                </div>
                <div class="card-body">
                    @foreach($recentFulfilled as $request)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $request->request_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $request->template->display_name }}</small>
                                </div>
                                <small class="text-muted">{{ $request->fulfilled_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Fulfill Modal -->
    <div class="modal fade" id="fulfillModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> Fulfill Access Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="fulfillForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Confirm that you have configured the user's access according to the selected template.
                        </div>
                        
                        <div class="mb-3">
                            <label for="fulfillmentNotes" class="form-label">Fulfillment Notes</label>
                            <textarea class="form-control" 
                                      id="fulfillmentNotes" 
                                      name="notes" 
                                      rows="4"
                                      placeholder="Add any notes about the fulfillment process..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Mark as Fulfilled
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function fulfillRequest(requestId) {
        const modal = new bootstrap.Modal(document.getElementById('fulfillModal'));
        const form = document.getElementById('fulfillForm');
        form.action = `/requests/${requestId}/fulfill`;
        modal.show();
    }
</script>
@endpush