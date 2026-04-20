@extends('layouts.app')

@section('title', 'Fulfillment Queue')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.fulfillment-queue') }}" class="nav-link active">
        <i class="bi bi-list-task"></i> Fulfillment Queue
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
        <h1 class="page-title">Fulfillment Queue</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dashboard.ict') }}">ICT Dashboard</a></li>
                <li class="breadcrumb-item active">Fulfillment Queue</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Request #</th>
                                <th>User</th>
                                <th>Template</th>
                                <th>Approved By</th>
                                <th>Approved At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td><strong>{{ $request->request_number }}</strong></td>
                                    <td>
                                        {{ $request->full_name }}
                                        <br>
                                        <small class="text-muted">{{ $request->email }}</small>
                                    </td>
                                    <td>
                                        {{ $request->template?->display_name ?? $request->template?->name ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">{{ $request->template?->department?->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $request->approvedBy?->name ?? 'N/A' }}</td>
                                    <td>{{ optional($request->approved_at)->format('M d, Y H:i') ?? 'N/A' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('requests.fulfill', $request->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Fulfill
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $requests->links() }}</div>
            @else
                <p class="text-muted mb-0">No approved requests are waiting for fulfillment.</p>
            @endif
        </div>
    </div>
@endsection
