@extends('layouts.app')

@section('title', 'Create Department')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="{{ route('departments.index') }}" class="nav-link active"><i class="bi bi-building"></i> Departments</a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Create Department</h1>
        <p class="text-muted mb-0">Add a new department for templates and user assignment.</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('departments.store') }}" class="row g-3">
                @csrf

                <div class="col-md-4">
                    <label for="code" class="form-label">Code</label>
                    <input id="code" name="code" type="text" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-8">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2"></i> Create Department
                    </button>
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
