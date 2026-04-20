@extends('layouts.app')

@section('title', 'Create User')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="{{ route('users.index') }}" class="nav-link active"><i class="bi bi-people"></i> Manage Users</a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Create User</h1>
        <p class="text-muted mb-0">Add a new user account and assign roles.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="payroll_number" class="form-label">Payroll Number</label>
                    <input id="payroll_number" name="payroll_number" type="text" class="form-control @error('payroll_number') is-invalid @enderror" value="{{ old('payroll_number') }}">
                    @error('payroll_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label d-block">Roles</label>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($roles as $role)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="roles[]"
                                    id="role_{{ $role->id }}"
                                    value="{{ $role->name }}"
                                    @checked(in_array($role->name, old('roles', []), true))
                                >
                                <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('roles.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2"></i> Create User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
