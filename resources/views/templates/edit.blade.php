@extends('layouts.app')

@section('title', 'Edit Template')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('templates.index') }}" class="nav-link active">
        <i class="bi bi-folder"></i> Manage Templates
    </a>
@endsection

@section('content')
    <h1 class="page-title">Edit Template</h1>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('templates.update', $template->id) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Mnemonic</label><input class="form-control" name="mnemonic" value="{{ old('mnemonic', $template->mnemonic) }}" required></div>
                <div class="col-md-8"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $template->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department_id" required>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id', $template->department_id) == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Category</label><input class="form-control" name="category" value="{{ old('category', $template->category) }}"></div>
                <div class="col-md-6"><label class="form-label">EHR Access Level</label><input class="form-control" name="ehr_access_level" value="{{ old('ehr_access_level', $template->ehr_access_level) }}"></div>
                <div class="col-md-6 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active))><label class="form-check-label">Active</label></div></div>
                <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="4" name="description">{{ old('description', $template->description) }}</textarea></div>
            </div>
            <div class="mt-3"><button class="btn btn-primary" type="submit">Save</button><a class="btn btn-secondary" href="{{ route('templates.show', $template->id) }}">Cancel</a></div>
        </form>
    </div></div>
@endsection
