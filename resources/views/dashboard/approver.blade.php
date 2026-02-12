@extends('layouts.app')

@section('title', 'Approver Dashboard')

@section('sidebar')
    <a href="{{ route('dashboard.approver') }}" class="nav-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('approvals.pending') }}" class="nav-link">
        <i class="bi bi-clock-history"></i> Pending Approvals
        @if($stats['pending_approvals'] > 0)
            <span class="badge bg-warning ms-2">{{ $stats['pending_approvals'] }}</span>
        @endif
    </a>
    <a href="{{ route('approvals.history') }}" class="nav-link">
        <i class="bi bi-list-check"></i> Approval History
    </a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Approver Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Approver Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="border-left-color: #ffc107;">
                <div class="stat-value" style="color: #ffc107;">{{ $stats['pending_approvals'] }}</div>
                <div class="stat-label">Pending Approvals</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left-color: #28a745;">
                <div class="stat-value" style="color: #28a745;">{{ $stats['approved_today'] }}</div>
                <div class="stat-label">Approved Today</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-left-color: #17a2b8;">
                <div class="stat-value" style="color: #17a2b8;">{{ $stats['approved_this_month'] }}</div>
                <div class="stat-label">Approved This Month</div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history"></i> Pending Approvals</span>
            <span class="badge bg-warning">{{ $stats['pending_approvals'] }} Pending</span>
        </div>
        <div class="card-body">
            @if($pendingApprovals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Request #</th>
                                <th>Requester</th>
                                <th>Template</th>
                                <th>Target User</th>
                                <th>Department</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingApprovals as $request)
                                <tr>
                                    <td>
                                        <strong>{{ $request->request_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $request->request_type }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $request->requester->name }}</div>
                                        <small class="text-muted">{{ $request->requester->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $request->template->mnemonic }}</span>
                                        <small class="d-block text-muted">{{ $request->template->name }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $request->full_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $request->email }}</small>
                                    </td>
                                    <td>{{ $request->department->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $request->submitted_at->format('M d, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $request->submitted_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('requests.show', $request->id) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="approveRequest({{ $request->id }})"
                                                    title="Approve">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="rejectRequest({{ $request->id }})"
                                                    title="Reject">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $pendingApprovals->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle" style="font-size: 3rem; color: #28a745;"></i>
                    <p class="text-muted mt-3">No pending approvals. Great job!</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> Approve Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve this access request?</p>
                        <div class="mb-3">
                            <label for="approvalComments" class="form-label">Comments (Optional)</label>
                            <textarea class="form-control" 
                                      id="approvalComments" 
                                      name="comments" 
                                      rows="3"
                                      placeholder="Add any comments about this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle"></i> Reject Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Please provide a reason for rejecting this request:</p>
                        <div class="mb-3">
                            <label for="rejectionReason" class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" 
                                      id="rejectionReason" 
                                      name="reason" 
                                      rows="4"
                                      required
                                      placeholder="Explain why this request is being rejected..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function approveRequest(requestId) {
        const modal = new bootstrap.Modal(document.getElementById('approveModal'));
        const form = document.getElementById('approveForm');
        form.action = `/requests/${requestId}/approve`;
        modal.show();
    }

    function rejectRequest(requestId) {
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        const form = document.getElementById('rejectForm');
        form.action = `/requests/${requestId}/reject`;
        modal.show();
    }
</script>
@endpush