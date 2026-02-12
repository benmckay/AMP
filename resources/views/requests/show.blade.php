@extends('layouts.app')

@section('title', 'Request Details')

@section('sidebar')
    @if(Auth::user()->hasRole('ict_admin'))
        <a href="{{ route('dashboard.ict') }}" class="nav-link">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('requests.fulfillment-queue') }}" class="nav-link">
            <i class="bi bi-list-task"></i> Fulfillment Queue
        </a>
    @elseif(Auth::user()->hasRole('hr'))
        <a href="{{ route('dashboard.hr') }}" class="nav-link">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    @else
        <a href="{{ route('dashboard.requester') }}" class="nav-link">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('requests.index') }}" class="nav-link">
            <i class="bi bi-list-ul"></i> My Requests
        </a>
    @endif
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Request Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('requests.index') }}">Requests</a></li>
                <li class="breadcrumb-item active">{{ $request->request_number }}</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-earmark-text"></i> Request Information</span>
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
                    <span class="badge bg-{{ $statusColor }}">{{ ucfirst($request->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Request Number</label>
                            <div><strong>{{ $request->request_number }}</strong></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Request Type</label>
                            <div>{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Template</label>
                            <div>
                                <span class="badge bg-secondary">{{ $request->template->mnemonic }}</span>
                                {{ $request->template->name }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Department</label>
                            <div>{{ $request->department->name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Target User Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Full Name</label>
                            <div><strong>{{ $request->full_name }}</strong></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Email</label>
                            <div>{{ $request->email }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Payroll Number</label>
                            <div>{{ $request->payroll_number ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Username</label>
                            <div>{{ $request->username ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Job Title</label>
                        <div>{{ $request->job_title ?? 'N/A' }}</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Justification</label>
                        <div class="p-3 bg-light rounded">{{ $request->justification }}</div>
                    </div>
                </div>
            </div>

            <!-- Workflow Actions -->
            @if($request->status === 'pending' && Auth::user()->can('approve', $request))
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <i class="bi bi-exclamation-triangle"></i> Action Required
                    </div>
                    <div class="card-body">
                        <p>This request is awaiting your approval.</p>
                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-success"
                                    onclick="approveRequest({{ $request->id }})">
                                <i class="bi bi-check-circle"></i> Approve Request
                            </button>
                            <button type="button" 
                                    class="btn btn-danger"
                                    onclick="rejectRequest({{ $request->id }})">
                                <i class="bi bi-x-circle"></i> Reject Request
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($request->status === 'approved' && Auth::user()->hasRole('ict_admin'))
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-info-circle"></i> Ready for Fulfillment
                    </div>
                    <div class="card-body">
                        <p>This request has been approved and is ready to be fulfilled.</p>
                        <button type="button" 
                                class="btn btn-success"
                                onclick="fulfillRequest({{ $request->id }})">
                            <i class="bi bi-check-circle"></i> Mark as Fulfilled
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Timeline
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <div class="small text-muted">{{ $request->submitted_at->format('M d, Y H:i') }}</div>
                                <div><strong>Request Submitted</strong></div>
                                <div class="small">by {{ $request->requester->name }}</div>
                            </div>
                        </div>

                        @if($request->approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <div class="small text-muted">{{ $request->approved_at->format('M d, Y H:i') }}</div>
                                    <div><strong>Request Approved</strong></div>
                                    <div class="small">by {{ $request->approvedBy->name }}</div>
                                    @if($request->approval_comments)
                                        <div class="small text-muted mt-1">{{ $request->approval_comments }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($request->fulfilled_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <div class="small text-muted">{{ $request->fulfilled_at->format('M d, Y H:i') }}</div>
                                    <div><strong>Request Fulfilled</strong></div>
                                    <div class="small">by {{ $request->fulfilledBy->name }}</div>
                                    @if($request->fulfillment_notes)
                                        <div class="small text-muted mt-1">{{ $request->fulfillment_notes }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($request->cancelled_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <div class="small text-muted">{{ $request->cancelled_at->format('M d, Y H:i') }}</div>
                                    <div><strong>Request Cancelled</strong></div>
                                    <div class="small">by {{ $request->cancelledBy->name }}</div>
                                    @if($request->cancellation_reason)
                                        <div class="small text-muted mt-1">{{ $request->cancellation_reason }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Template Details -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-folder"></i> Template Details
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="text-muted small">Mnemonic</label>
                        <div><strong>{{ $request->template->mnemonic }}</strong></div>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small">Template Name</label>
                        <div>{{ $request->template->name }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small">Department</label>
                        <div>{{ $request->template->department->name }}</div>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small">EHR Access Level</label>
                        <div>
                            <span class="badge bg-info">{{ $request->template->ehr_access_level }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .timeline-marker {
        position: absolute;
        left: -26px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e0e0e0;
    }

    .timeline-content {
        padding-left: 10px;
    }
</style>
@endpush