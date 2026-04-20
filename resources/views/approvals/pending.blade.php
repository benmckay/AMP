@extends('layouts.app')

@section('title', 'Pending Approvals')

@section('sidebar')
    <a href="{{ route('dashboard.approver') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('approvals.pending') }}" class="nav-link active">
        <i class="bi bi-clock-history"></i> Pending Approvals
    </a>
    <a href="{{ route('approvals.history') }}" class="nav-link">
        <i class="bi bi-list-check"></i> Approval History
    </a>
@endsection

@section('content')
    <h1 class="page-title">Pending Approvals</h1>
    <div class="card">
        <div class="card-body">
            @if($requests->count())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Request #</th><th>User</th><th>Template</th><th>Submitted</th><th>Action</th></tr></thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td>{{ $request->request_number }}</td>
                                    <td>{{ $request->full_name }}</td>
                                    <td>{{ $request->template?->name ?? 'N/A' }}</td>
                                    <td>{{ optional($request->submitted_at)->format('M d, Y') }}</td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('requests.show', $request->id) }}">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $requests->links() }}</div>
            @else
                <p class="text-muted mb-0">No pending approvals.</p>
            @endif
        </div>
    </div>
@endsection
