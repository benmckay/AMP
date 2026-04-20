@extends('layouts.app')

@section('title', 'Edit Request')

@section('sidebar')
    <a href="{{ route('dashboard.requester') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.index') }}" class="nav-link active">
        <i class="bi bi-list-ul"></i> My Requests
    </a>
@endsection

@section('content')
    <h1 class="page-title">Edit Request</h1>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('requests.update', $request->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input class="form-control" name="first_name" value="{{ old('first_name', $request->first_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input class="form-control" name="last_name" value="{{ old('last_name', $request->last_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email', $request->email) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Template</label>
                        <select class="form-select" name="template_id" required>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" @selected(old('template_id', $request->template_id) == $template->id)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department_id">
                            <option value="">None</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id', $request->department_id) === (string) $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Priority</label>
                        <select class="form-select" name="priority">
                            @foreach(['low','normal','high','urgent'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', $request->priority) === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Justification</label>
                        <textarea class="form-control" name="justification" rows="4" required>{{ old('justification', $request->justification) }}</textarea>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a href="{{ route('requests.show', $request->id) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
