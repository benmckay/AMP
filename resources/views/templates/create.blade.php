@extends('layouts.app')

@section('title', 'Create Template')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('templates.index') }}" class="nav-link">
        <i class="bi bi-folder"></i> Manage Templates
    </a>
    <a href="{{ route('templates.create') }}" class="nav-link active">
        <i class="bi bi-plus-circle"></i> New Template
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Create New Template</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Templates</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-file-earmark-plus"></i> Template Information
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('templates.store') }}">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="mnemonic" class="form-label">
                            Mnemonic <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('mnemonic') is-invalid @enderror" 
                               id="mnemonic" 
                               name="mnemonic"
                               value="{{ old('mnemonic') }}"
                               placeholder="e.g., TEMPPHYS00"
                               required>
                        @error('mnemonic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Short unique code for the template</small>
                    </div>

                    <div class="col-md-6">
                        <label for="department_id" class="form-label">
                            Department <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('department_id') is-invalid @enderror" 
                                id="department_id" 
                                name="department_id"
                                required>
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="name" class="form-label">
                            Template Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="e.g., Template, Physician"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" 
                               class="form-control @error('category') is-invalid @enderror" 
                               id="category" 
                               name="category"
                               value="{{ old('category') }}"
                               placeholder="e.g., Physician, Nursing, Administrative">
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="ehr_access_level" class="form-label">EHR Access Level</label>
                        <select class="form-select @error('ehr_access_level') is-invalid @enderror" 
                                id="ehr_access_level" 
                                name="ehr_access_level">
                            <option value="standard">Standard</option>
                            <option value="read">Read Only</option>
                            <option value="write">Read/Write</option>
                            <option value="full">Full Access</option>
                            <option value="admin">Admin Access</option>
                        </select>
                        @error('ehr_access_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description"
                              rows="3"
                              placeholder="Provide a detailed description of this template...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="requires_cos_approval" 
                               name="requires_cos_approval"
                               value="1"
                               {{ old('requires_cos_approval') ? 'checked' : '' }}>
                        <label class="form-check-label" for="requires_cos_approval">
                            Requires COS Approval
                        </label>
                    </div>
                    <small class="text-muted">Check this if the template requires Chief of Service approval</small>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active"
                               value="1"
                               checked>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                    <small class="text-muted">Active templates are available for selection in requests</small>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('templates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection