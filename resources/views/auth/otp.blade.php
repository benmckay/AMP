<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Access Management Portal</title>

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
        .otp-container {
            max-width: 450px;
            width: 100%;
            padding: 0 20px;
        }
        .otp-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .otp-header {
            background: linear-gradient(135deg, #008B8B 0%, #20B2AA 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .otp-header h1 {
            color: white;
        }
        .otp-body {
            padding: 2rem;
        }
        .otp-input {
            text-align: center;
            letter-spacing: 0.45rem;
            font-size: 1.3rem;
            font-weight: 700;
            width: 100%;
            padding: 0.75rem;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            box-sizing: border-box;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #008B8B 0%, #20B2AA 100%);
            border: none;
            color: white;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.75rem;
            width: 100%;
            cursor: pointer;
        }
        .btn-link-custom {
            color: #008B8B;
            font-weight: 600;
            border: none;
            background: none;
            padding: 0;
            cursor: pointer;
        }
        .hint {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-warning { background-color: #fff3cd; color: #856404; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="otp-card">
            <div class="otp-header">
                <i class="bi bi-shield-lock" style="font-size: 2.4rem;"></i>
                <h1 class="h4 mt-3 mb-2">OTP Verification</h1>
                <p class="mb-0">Complete your login with the one-time code</p>
            </div>

            <div class="otp-body">
                @if($errors->has('otp_send') || $errors->has('email'))
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ $errors->first('otp_send') ?: $errors->first('email') }}
                    </div>
                @endif

                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if(session('otp_fallback_code'))
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-info-circle"></i>
                        Use this OTP: <strong>{{ session('otp_fallback_code') }}</strong>
                    </div>
                @endif

                @if(session('otp_delivery_info'))
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i>
                        {{ session('otp_delivery_info') }}
                    </div>
                @endif

                @if(session('otp_delivery_error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-octagon"></i>
                        OTP delivery failed: {{ session('otp_delivery_error') }}
                    </div>
                @endif

                <p class="hint mb-3">
                    <i class="bi bi-person"></i> {{ $email }}<br>
                    <i class="bi {{ $destinationType === 'email' ? 'bi-envelope' : 'bi-phone' }}"></i>
                    Code sent to your {{ $destinationType }}: {{ $maskedDestination }}
                </p>

                <form method="POST" action="{{ route('login.otp.verify') }}" class="mb-3">
                    @csrf
                    <label for="otp" class="form-label fw-semibold">One-Time Password</label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        value="{{ old('otp') }}"
                        class="form-control otp-input @error('otp') is-invalid @enderror"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        required
                        autofocus
                    >
                    @error('otp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-primary-custom mt-3">
                        <i class="bi bi-check-circle"></i> Verify & Login
                    </button>
                </form>

                <div class="d-flex justify-content-between align-items-center">
                    <form method="POST" action="{{ route('login.otp.resend') }}">
                        @csrf
                        <button type="submit" class="btn-link-custom">
                            <i class="bi bi-arrow-repeat"></i> Resend OTP
                        </button>
                    </form>

                    <a href="{{ route('login') }}" class="text-decoration-none" style="color: #008B8B;">
                        <i class="bi bi-arrow-left"></i> Back to login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
