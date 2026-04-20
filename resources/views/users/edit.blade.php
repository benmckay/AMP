@extends('layouts.app')

@section('title', 'Edit User')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="{{ route('users.index') }}" class="nav-link active"><i class="bi bi-people"></i> Manage Users</a>
@endsection

@section('content')
    @php
        $roles = $roles ?? collect();
        $departments = $departments ?? collect();
    @endphp

    <div class="mb-4">
        <h1 class="page-title">Edit User</h1>
        <p class="text-muted mb-0">Update user profile, roles, and department access.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $user->name) }}"
                        required
                    >
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}"
                        required
                    >
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone', $user->phone) }}"
                    >
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="payroll_number" class="form-label">Payroll Number</label>
                    <input
                        id="payroll_number"
                        name="payroll_number"
                        type="text"
                        class="form-control @error('payroll_number') is-invalid @enderror"
                        value="{{ old('payroll_number', $user->payroll_number) }}"
                    >
                    @error('payroll_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">New Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                    >
                    <div class="form-text">Leave blank to keep the current password.</div>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="form-control"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label d-block">Roles</label>
                    @php
                        $selectedRoles = old('roles', $user->roles->pluck('name')->all());
                    @endphp
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="roles[]"
                                    id="role_{{ $role->id }}"
                                    value="{{ $role->name }}"
                                    @checked(in_array($role->name, $selectedRoles, true))
                                >
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('roles.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label d-block">Department Access</label>
                    @php
                        $selectedDepartments = old('department_ids', $user->departments->pluck('id')->all());
                        $selectedDepartmentRoles = old(
                            'department_roles',
                            $user->departments
                                ->mapWithKeys(fn($department) => [$department->id => $department->pivot->role])
                                ->all()
                        );
                    @endphp
                    <div class="row g-3">
                        @foreach($departments as $department)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="form-check mb-2">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="department_ids[]"
                                            id="department_{{ $department->id }}"
                                            value="{{ $department->id }}"
                                            @checked(in_array($department->id, $selectedDepartments, true))
                                        >
                                        <label class="form-check-label" for="department_{{ $department->id }}">
                                            {{ $department->name }}
                                        </label>
                                    </div>

                                    <label for="department_role_{{ $department->id }}" class="form-label small text-muted mb-1">Access Role</label>
                                    <select
                                        class="form-select form-select-sm"
                                        id="department_role_{{ $department->id }}"
                                        name="department_roles[{{ $department->id }}]"
                                    >
                                        @php
                                            $departmentRole = $selectedDepartmentRoles[$department->id] ?? 'both';
                                        @endphp
                                        <option value="requester" @selected($departmentRole === 'requester')>Requester</option>
                                        <option value="approver" @selected($departmentRole === 'approver')>Approver</option>
                                        <option value="both" @selected($departmentRole === 'both')>Both</option>
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text">Checked departments will remain assigned using the selected access role.</div>
                    @error('department_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('department_ids.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('department_roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('department_roles.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2"></i> Save Changes
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
