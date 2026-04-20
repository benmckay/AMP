@extends('layouts.app')

@section('title', 'Department Details')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="{{ route('departments.index') }}" class="nav-link active"><i class="bi bi-building"></i> Departments</a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Department Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('departments.edit', $department) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('departments.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Code:</strong>
                    <div><code>{{ $department->code }}</code></div>
                </div>
                <div class="col-md-4">
                    <strong>Name:</strong>
                    <div>{{ $department->name }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Status:</strong>
                    <div>
                        @if($department->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <strong>Description:</strong>
                    <div>{{ $department->description ?: 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Assigned Users</div>
        <div class="card-body">
            @if($department->departmentUsers->count())
                <div class="d-flex flex-wrap gap-2">
                    @foreach($department->departmentUsers as $user)
                        <span class="badge bg-info text-dark">{{ $user->name }}</span>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">No users assigned.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">Templates</div>
        <div class="card-body">
            @if($department->templates->count())
                <ul class="mb-0">
                    @foreach($department->templates as $template)
                        <li>{{ $template->mnemonic }} - {{ $template->name }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted mb-0">No templates in this department.</p>
            @endif
        </div>
    </div>
@endsection
