@extends('layouts.app')

@section('title', 'Manage Users')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('users.index') }}" class="nav-link active">
        <i class="bi bi-people"></i> Manage Users
    </a>
    <a href="{{ route('departments.index') }}" class="nav-link">
        <i class="bi bi-building"></i> Departments
    </a>
    <a href="{{ route('templates.index') }}" class="nav-link">
        <i class="bi bi-folder"></i> Manage Templates
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title mb-0">User Management</h1>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> New User
            </a>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dashboard.ict') }}">ICT Dashboard</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="search">Search</label>
                    <input id="search" type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name or email">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="role">Role</label>
                    <select id="role" name="role" class="form-select">
                        <option value="">All roles</option>
                        <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                        <option value="ict_admin" @selected(request('role') === 'ict_admin')>ICT Admin</option>
                        <option value="hr" @selected(request('role') === 'hr')>HR</option>
                        <option value="approver" @selected(request('role') === 'approver')>Approver</option>
                        <option value="requester" @selected(request('role') === 'requester')>Requester</option>
                        <option value="auditor" @selected(request('role') === 'auditor')>Auditor</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="department_id">Department</label>
                    <select id="department_id" name="department_id" class="form-select">
                        <option value="">All departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($users->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th>Departments</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @forelse($user->roles as $role)
                                            <span class="badge bg-info text-dark">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-muted">None</span>
                                        @endforelse
                                    </td>
                                    <td>{{ $user->departments_count }}</td>
                                    <td>{{ $user->created_at?->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="confirm-delete" data-confirm="Delete user '{{ $user->name }}'? This action cannot be undone.">
                                                @csrf
                                                @method('DELETE')
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
                <div class="mt-3">{{ $users->links() }}</div>
            @else
                <p class="text-muted mb-0">No users found for the current filters.</p>
            @endif
        </div>
    </div>
@endsection
