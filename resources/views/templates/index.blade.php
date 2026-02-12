@extends('layouts.app')

@section('title', 'Manage Templates')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('templates.index') }}" class="nav-link active">
        <i class="bi bi-folder"></i> Manage Templates
    </a>
    <a href="{{ route('templates.create') }}" class="nav-link">
        <i class="bi bi-plus-circle"></i> New Template
    </a>
    <a href="{{ route('departments.index') }}" class="nav-link">
        <i class="bi bi-building"></i> Departments
    </a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Template Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Templates</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('templates.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Template
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('templates.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="department" class="form-label">Department</label>
                    <select class="form-select" id="department" name="department_id">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by mnemonic or name...">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('templates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Templates by Department -->
    @foreach($templatesByDepartment as $department)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-building"></i> {{ $department->name }}
                    <span class="badge bg-secondary ms-2">{{ $department->templates->count() }} templates</span>
                </span>
                <a href="{{ route('templates.create', ['department' => $department->id]) }}" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus"></i> Add Template
                </a>
            </div>
            <div class="card-body">
                @if($department->templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mnemonic</th>
                                    <th>Template Name</th>
                                    <th>Category</th>
                                    <th>EHR Access</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($department->templates as $template)
                                    <tr>
                                        <td>
                                            <code>{{ $template->mnemonic }}</code>
                                        </td>
                                        <td>{{ $template->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $template->category }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $template->ehr_access_level }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $template->access_requests_count ?? 0 }} requests</span>
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('templates.show', $template->id) }}" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('templates.edit', $template->id) }}" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($template->access_requests_count == 0)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteTemplate({{ $template->id }})"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No templates in this department yet.</p>
                @endif
            </div>
        </div>
    @endforeach

    @if($templatesByDepartment->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-folder-x" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No templates found.</p>
            <a href="{{ route('templates.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create First Template
            </a>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-trash"></i> Delete Template
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>Are you sure you want to delete this template?</p>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            This action cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function deleteTemplate(templateId) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const form = document.getElementById('deleteForm');
        form.action = `/templates/${templateId}`;
        modal.show();
    }
</script>
@endpush