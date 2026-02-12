@extends('layouts.app')

@section('title', 'Requester Dashboard')

@section('sidebar')
    <a href="{{ route('dashboard.requester') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.create') }}" class="nav-link">
        <i class="bi bi-plus-circle"></i> New Request
    </a>
    <a href="{{ route('requests.index') }}" class="nav-link">
        <i class="bi bi-list-ul"></i> My Requests
    </a>
    <a href="{{ route('templates.browse') }}" class="nav-link">
        <i class="bi bi-folder"></i> Browse Templates
    </a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Requester Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('requests.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Access Request
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_requests'] }}</div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #ffc107;">
                <div class="stat-value" style="color: #ffc107;">{{ $stats['pending'] }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #17a2b8;">
                <div class="stat-value" style="color: #17a2b8;">{{ $stats['approved'] }}</div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #28a745;">
                <div class="stat-value" style="color: #28a745;">{{ $stats['fulfilled'] }}</div>
                <div class="stat-label">Fulfilled</div>
            </div>
        </div>
    </div>

    <!-- My Departments -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-building"></i> My Departments
        </div>
        <div class="card-body">
            @if($departments->count() > 0)
                <div class="row g-3">
                    @foreach($departments as $dept)
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-folder" style="color: var(--primary-color);"></i>
                                        {{ $dept->name }}
                                    </h5>
                                    <p class="card-text text-muted">{{ $dept->description }}</p>
                                    <a href="{{ route('requests.create', ['department' => $dept->id]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-plus"></i> New Request
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    You are not assigned to any department as a requester. Please contact your administrator.
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Requests -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history"></i> Recent Requests</span>
            <a href="{{ route('requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
            @if($myRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Request #</th>
                                <th>Template</th>
                                <th>User Name</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myRequests as $request)
                                <tr>
                                    <td>
                                        <strong>{{ $request->request_number }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $request->template->mnemonic }}</span>
                                        <small class="d-block text-muted">{{ $request->template->name }}</small>
                                    </td>
                                    <td>{{ $request->full_name }}</td>
                                    <td>{{ $request->department->name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $statusColor = match($request->status) {
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'fulfilled' => 'success',
                                                'rejected' => 'danger',
                                                'cancelled' => 'secondary',
                                                default => 'light'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->submitted_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('requests.show', $request->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No requests yet. Create your first request!</p>
                    <a href="{{ route('requests.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Request
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection