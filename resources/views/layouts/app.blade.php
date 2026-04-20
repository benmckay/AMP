<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'AMP') }} - @yield('title')</title>
    
    <script>
        (function() {
            const theme = "{{ Auth::user()?->theme ?? 'system' }}";
            const getStoredTheme = () => theme;
            const setTargetTheme = (theme) => {
                if (theme === 'system') {
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-bs-theme', systemTheme);
                } else {
                    document.documentElement.setAttribute('data-bs-theme', theme);
                }
            };
            setTargetTheme(getStoredTheme());

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                const storedTheme = getStoredTheme();
                if (storedTheme === 'system') {
                    setTargetTheme(storedTheme);
                }
            });
        })();
    </script>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    @stack('styles')
    <style>
        :root {
            --primary-color: #008B8B;
            --secondary-color: #20B2AA;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 0.75rem 1rem;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background: white;
            border-right: 1px solid #dee2e6;
            padding: 1.5rem 0;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #f1f3f5;
            color: var(--primary-color);
            border-left: 3px solid var(--primary-color);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        .content-wrapper {
            padding: 2rem;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            border: 1px solid #dee2e6;
        }
        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
            font-weight: 600;
        }
        .card-body {
            padding: 1.5rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            text-transform: uppercase;
        }
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .badge {
            padding: 0.4em 0.6em;
            border-radius: 6px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-shield-check"></i> AMP
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <nav class="nav flex-column">
                    @yield('sidebar')
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="content-wrapper">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('submit', function(e) {
                if (e.target.classList.contains('confirm-delete')) {
                    e.preventDefault();
                    const form = e.target;
                    const message = form.getAttribute('data-confirm') || 'Are you sure you want to delete this item? Note this action cannot be undone!';
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
