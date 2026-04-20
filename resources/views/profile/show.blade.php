@extends('layouts.app')

@section('title', 'Profile')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('profile') }}" class="nav-link active">
        <i class="bi bi-person"></i> Profile
    </a>
    <a href="{{ route('settings') }}" class="nav-link">
        <i class="bi bi-gear"></i> Settings
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">My Profile</h1>
        <p class="text-muted mb-0">Manage your account details and view your account activity summary.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $requestStats['total_requests'] }}</div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $requestStats['pending_requests'] }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $requestStats['fulfilled_requests'] }}</div>
                <div class="stat-label">Fulfilled</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $requestStats['audit_events'] }}</div>
                <div class="stat-label">Audit Events</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Account Details</span>
            <small class="text-muted">Member since {{ $user->created_at?->format('M d, Y') }}</small>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $user->phone) }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="payroll_number" class="form-label">Payroll Number</label>
                    <input type="text" id="payroll_number" name="payroll_number" class="form-control @error('payroll_number') is-invalid @enderror"
                           value="{{ old('payroll_number', $user->payroll_number) }}">
                    @error('payroll_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="department" class="form-label">Department</label>
                    <input type="text" id="department" name="department" class="form-control @error('department') is-invalid @enderror"
                           value="{{ old('department', $user->department) }}">
                    @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Roles</div>
        <div class="card-body">
            @forelse($user->roles as $role)
                <span class="badge bg-info me-2">{{ $role->name }}</span>
            @empty
                <span class="text-muted">No roles assigned.</span>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header">Department Assignments</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Assigned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->departmentAssignments as $assignment)
                            <tr>
                                <td>{{ $assignment->department?->name ?? 'N/A' }}</td>
                                <td class="text-capitalize">{{ $assignment->role }}</td>
                                <td>
                                    <span class="badge {{ $assignment->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $assignment->assigned_at?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No department assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

