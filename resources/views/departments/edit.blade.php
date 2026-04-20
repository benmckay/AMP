@extends('layouts.app')

@section('title', 'Edit Department')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="{{ route('departments.index') }}" class="nav-link active"><i class="bi bi-building"></i> Departments</a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Edit Department</h1>
        <p class="text-muted mb-0">Update department details and status.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('departments.update', $department) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-4">
                    <label for="code" class="form-label">Code</label>
                    <input id="code" name="code" type="text" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $department->code) }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-8">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $department->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $department->is_active))>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2"></i> Save Changes
                    </button>
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
