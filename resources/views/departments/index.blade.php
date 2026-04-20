@extends('layouts.app')

@section('title', 'Departments')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('departments.index') }}" class="nav-link active">
        <i class="bi bi-building"></i> Departments
    </a>
    <a href="{{ route('users.index') }}" class="nav-link">
        <i class="bi bi-people"></i> Manage Users
    </a>
    <a href="{{ route('templates.index') }}" class="nav-link">
        <i class="bi bi-folder"></i> Manage Templates
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title mb-0">Departments</h1>
            <a href="{{ route('departments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Department
            </a>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dashboard.ict') }}">ICT Dashboard</a></li>
                <li class="breadcrumb-item active">Departments</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            @if($departments->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Templates</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $department)
                                <tr>
                                    <td><code>{{ $department->code }}</code></td>
                                    <td>{{ $department->name }}</td>
                                    <td>{{ $department->description ?: 'N/A' }}</td>
                                    <td>{{ $department->templates_count }}</td>
                                    <td>
                                        @if($department->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('departments.show', $department) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form method="POST" action="{{ route('departments.update', $department) }}" class="confirm-delete" data-confirm="Delete department '{{ $department->name }}'?">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="_delete" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $departments->links() }}</div>
            @else
                <p class="text-muted mb-0">No departments found.</p>
            @endif
        </div>
    </div>
@endsection
