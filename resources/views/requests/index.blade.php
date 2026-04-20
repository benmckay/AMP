@extends('layouts.app')

@section('title', 'My Requests')

@section('sidebar')
    <a href="{{ route('dashboard.requester') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.create') }}" class="nav-link">
        <i class="bi bi-plus-circle"></i> New Request
    </a>
    <a href="{{ route('requests.index') }}" class="nav-link active">
        <i class="bi bi-list-ul"></i> My Requests
    </a>
    <a href="{{ route('templates.browse') }}" class="nav-link">
        <i class="bi bi-folder"></i> Browse Templates
    </a>
@endsection

@section('content')
    <h1 class="page-title">My Requests</h1>
    <div class="card">
        <div class="card-body">
            @if($requests->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Request #</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td>{{ $request->request_number }}</td>
                                    <td>{{ $request->template?->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-{{ $request->status_color }}">{{ ucfirst($request->status) }}</span></td>
                                    <td>{{ optional($request->submitted_at)->format('M d, Y') }}</td>
                                    <td><a href="{{ route('requests.show', $request->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $requests->links() }}</div>
            @else
                <p class="text-muted mb-0">No requests found.</p>
            @endif
        </div>
    </div>
@endsection
