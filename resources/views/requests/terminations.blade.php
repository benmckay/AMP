@extends('layouts.app')

@section('title', 'Termination Requests')

@section('sidebar')
    <a href="{{ route('dashboard.hr') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.reactivations') }}" class="nav-link">
        <i class="bi bi-arrow-clockwise"></i> Reactivations
    </a>
    <a href="{{ route('requests.terminations') }}" class="nav-link active">
        <i class="bi bi-x-circle"></i> Terminations
    </a>
@endsection

@section('content')
    <h1 class="page-title">Termination Requests</h1>
    <div class="card"><div class="card-body">
        @if($requests->count())
            <ul class="list-group list-group-flush">
                @foreach($requests as $request)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $request->request_number }} - {{ $request->full_name }}</span>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('requests.show', $request->id) }}">View</a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-3">{{ $requests->links() }}</div>
        @else
            <p class="text-muted mb-0">No pending termination requests.</p>
        @endif
    </div></div>
@endsection
