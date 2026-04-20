@extends('layouts.app')

@section('title', 'Create Termination Request')

@section('sidebar')
    <a href="{{ route('dashboard.hr') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.create-termination') }}" class="nav-link active">
        <i class="bi bi-plus-circle"></i> New Termination
    </a>
@endsection

@section('content')
    <h1 class="page-title">New Termination Request</h1>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('requests.store') }}">
            @csrf
            <input type="hidden" name="request_type" value="termination">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">First Name</label><input name="first_name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Last Name</label><input name="last_name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Template</label><select class="form-select" name="template_id" required>@foreach($templates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department_id"><option value="">None</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Priority</label><select class="form-select" name="priority"><option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                <div class="col-12"><label class="form-label">Justification</label><textarea class="form-control" name="justification" rows="4" required></textarea></div>
            </div>
            <div class="mt-3"><button class="btn btn-primary" type="submit">Submit Request</button></div>
        </form>
    </div></div>
@endsection
