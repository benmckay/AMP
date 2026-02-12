@extends('layouts.app')

@section('title', 'HR Dashboard')

@section('sidebar')
    <a href="{{ route('dashboard.hr') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.reactivations') }}" class="nav-link">
        <i class="bi bi-arrow-clockwise"></i> Reactivations
        @if($stats['pending_reactivations'] > 0)
            <span class="badge bg-warning ms-2">{{ $stats['pending_reactivations'] }}</span>
        @endif
    </a>
    <a href="{{ route('requests.terminations') }}" class="nav-link">
        <i class="bi bi-x-circle"></i> Terminations
        @if($stats['pending_terminations'] > 0)
            <span class="badge bg-danger ms-2">{{ $stats['pending_terminations'] }}</span>
        @endif
    </a>
    <a href="{{ route('requests.create-termination') }}" class="nav-link">
        <i class="bi bi-plus-circle"></i> New Termination
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">HR Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">HR Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="border-left-color: #ffc107;">
                <div class="stat-value" style="color: #ffc107;">{{ $stats['pending_reactivations'] }}</div>
                <div class="stat-label">Pending Reactivations</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left-color: #dc3545;">
                <div class="stat-value" style="color: #dc3545;">{{ $stats['pending_terminations'] }}</div>
                <div class="stat-label">Pending Terminations</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left-color: #28a745;">
                <div class="stat-value" style="color: #28a745;">{{ $stats['completed_this_month'] }}</div>
                <div class="stat-label">Completed This Month</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Reactivation Requests -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-arrow-clockwise"></i> Pending Reactivations</span>
                    <span class="badge bg-warning">{{ $stats['pending_reactivations'] }} Pending</span>
                </div>
                <div class="card-body">
                    @if($reactivationRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request #</th>
                                        <th>User</th>
                                        <th>Template</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reactivationRequests as $request)
                                        <tr>
                                            <td><strong>{{ $request->request_number }}</strong></td>
                                            <td>
                                                {{ $request->full_name }}
                                                <br>
                                                <small class="text-muted">{{ $request->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $request->template->mnemonic }}</span>
                                            </td>
                                            <td>{{ $request->submitted_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('requests.show', $request->id) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success"
                                                            onclick="approveReactivation({{ $request->id }})">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="rejectReactivation({{ $request->id }})">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $reactivationRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No pending reactivations</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Termination Requests -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-x-circle"></i> Pending Terminations</span>
                    <span class="badge bg-danger">{{ $stats['pending_terminations'] }} Pending</span>
                </div>
                <div class="card-body">
                    @if($terminationRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request #</th>
                                        <th>User</th>
                                        <th>Template</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($terminationRequests as $request)
                                        <tr>
                                            <td><strong>{{ $request->request_number }}</strong></td>
                                            <td>
                                                {{ $request->full_name }}
                                                <br>
                                                <small class="text-muted">{{ $request->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $request->template->mnemonic }}</span>
                                            </td>
                                            <td>{{ $request->submitted_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('requests.show', $request->id) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success"
                                                            onclick="approveTermination({{ $request->id }})">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $terminationRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No pending terminations</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function approveReactivation(requestId) {
        if (confirm('Are you sure you want to approve this reactivation request?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/requests/${requestId}/approve`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function rejectReactivation(requestId) {
        const reason = prompt('Please provide a reason for rejection:');
        if (reason) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/requests/${requestId}/reject`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = reason;
            
            form.appendChild(csrf);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function approveTermination(requestId) {
        if (confirm('Are you sure you want to approve this termination request?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/requests/${requestId}/approve`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush