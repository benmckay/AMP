@extends('layouts.app')

@section('title', 'Template Details')

@section('sidebar')
    <a href="{{ route('dashboard.ict') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('templates.index') }}" class="nav-link active">
        <i class="bi bi-folder"></i> Manage Templates
    </a>
@endsection

@section('content')
    <h1 class="page-title">Template Details</h1>
    <div class="card"><div class="card-body">
        <p><strong>Mnemonic:</strong> {{ $template->mnemonic }}</p>
        <p><strong>Name:</strong> {{ $template->name }}</p>
        <p><strong>Department:</strong> {{ $template->department?->name ?? 'N/A' }}</p>
        <p><strong>Category:</strong> {{ $template->category ?? 'N/A' }}</p>
        <p><strong>EHR Access:</strong> {{ $template->ehr_access_level ?? 'N/A' }}</p>
        <a class="btn btn-primary" href="{{ route('templates.edit', $template->id) }}">Edit</a>
        <a class="btn btn-secondary" href="{{ route('templates.index') }}">Back</a>
    </div></div>
@endsection
