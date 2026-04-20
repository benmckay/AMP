@extends('layouts.app')

@section('title', 'Browse Templates')

@section('sidebar')
    <a href="{{ route('dashboard.requester') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('templates.browse') }}" class="nav-link active">
        <i class="bi bi-folder"></i> Browse Templates
    </a>
@endsection

@section('content')
    <h1 class="page-title">Browse Templates</h1>
    <div class="card"><div class="card-body">
        @if($templates->count())
            <div class="row g-3">
                @foreach($templates as $template)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="mb-1">{{ $template->name }}</h6>
                                <small class="text-muted">{{ $template->department?->name ?? 'N/A' }}</small>
                                <div class="mt-2"><span class="badge bg-secondary">{{ $template->mnemonic }}</span></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">{{ $templates->links() }}</div>
        @else
            <p class="text-muted mb-0">No templates found.</p>
        @endif
    </div></div>
@endsection
