<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Access Management Portal</title>
    
    <!-- Scripts & Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background: linear-gradient(135deg, #008B8B 0%, #20B2AA 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 0 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #008B8B 0%, #20B2AA 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
        }
        .login-header p {
            margin: 0;
            opacity: 0.9;
        }
        .login-body {
            padding: 2.5rem 2rem;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            width: 100%;
            box-sizing: border-box;
        }
        .btn-login {
            background: linear-gradient(135deg, #008B8B 0%, #20B2AA 100%);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            margin-top: 1rem;
        }
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            z-index: 1;
        }
        .divider span {
            background: white;
            padding: 0 10px;
            color: #6c757d;
            font-size: 0.875rem;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
            z-index: -1;
        }
        .hospital-info {
            text-align: center;
            color: white;
            margin-top: 2rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="mb-3">
                    <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                </div>
                <h1>Access Management Portal</h1>
                <p>Aga Khan University Hospital</p>
            </div>
            
            <div class="login-body">
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif
                
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle"></i>
                        {{ session('status') }}
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Email Address
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus
                               placeholder="Enter your email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Password
                        </label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required
                               placeholder="Enter your password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>
                
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <div class="text-center">
                    <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: #008B8B;">
                        <i class="bi bi-key"></i> Forgot Password?
                    </a>
                </div>
            </div>
        </div>
        
        <div class="hospital-info">
            <p>&copy; {{ date('Y') }} Aga Khan University Hospital. All rights reserved.</p>
            <p class="mb-0"><small>Secure Access Management System</small></p>
        </div>
    </div>
</body>
</html>