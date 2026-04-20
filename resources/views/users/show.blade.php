@extends('layouts.app')

@section('title', 'User Details')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="{{ route('users.index') }}" class="nav-link active"><i class="bi bi-people"></i> Manage Users</a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">User Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Name:</strong>
                    <div>{{ $user->name }}</div>
                </div>
                <div class="col-md-6">
                    <strong>Email:</strong>
                    <div>{{ $user->email }}</div>
                </div>
                <div class="col-md-6">
                    <strong>Phone:</strong>
                    <div>{{ $user->phone ?: 'N/A' }}</div>
                </div>
                <div class="col-md-6">
                    <strong>Payroll Number:</strong>
                    <div>{{ $user->payroll_number ?: 'N/A' }}</div>
                </div>
                <div class="col-md-6">
                    <strong>Roles:</strong>
                    <div>
                        @forelse($user->roles as $role)
                            <span class="badge bg-info text-dark">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted">None</span>
                        @endforelse
                    </div>
                </div>
                <div class="col-md-6">
                    <strong>Departments:</strong>
                    <div>
                        @forelse($user->departments as $department)
                            <span class="badge bg-secondary">{{ $department->name }}</span>
                        @empty
                            <span class="text-muted">None</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
