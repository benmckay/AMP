@extends('layouts.app')

@section('title', 'Settings')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('profile') }}" class="nav-link">
        <i class="bi bi-person"></i> Profile
    </a>
    <a href="{{ route('settings') }}" class="nav-link active">
        <i class="bi bi-gear"></i> Settings
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Settings</h1>
        <p class="text-muted mb-0">Customize how your account works, including preferences, notifications, and security.</p>
    </div>

    <div class="card mb-4">
        <div class="card-header">Preferences</div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.preferences.update') }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label for="preferred_timezone" class="form-label">Timezone</label>
                    <input type="text" id="preferred_timezone" name="preferred_timezone"
                           class="form-control @error('preferred_timezone') is-invalid @enderror"
                           value="{{ old('preferred_timezone', $user->preferred_timezone) }}"
                           placeholder="e.g. Africa/Nairobi">
                    @error('preferred_timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="preferred_language" class="form-label">Language</label>
                    <select id="preferred_language" name="preferred_language"
                            class="form-select @error('preferred_language') is-invalid @enderror">
                        <option value="en" @selected(old('preferred_language', $user->preferred_language ?? 'en') === 'en')>English</option>
                        <option value="sw" @selected(old('preferred_language', $user->preferred_language ?? 'en') === 'sw')>Swahili</option>
                    </select>
                    @error('preferred_language') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="theme" class="form-label">Theme</label>
                    <select id="theme" name="theme" class="form-select @error('theme') is-invalid @enderror">
                        <option value="system" @selected(old('theme', $user->theme ?? 'system') === 'system')>System</option>
                        <option value="light" @selected(old('theme', $user->theme ?? 'system') === 'light')>Light</option>
                        <option value="dark" @selected(old('theme', $user->theme ?? 'system') === 'dark')>Dark</option>
                    </select>
                    @error('theme') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <h6 class="mb-2">Notifications</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="notify_security_alerts" name="notify_security_alerts" value="1"
                               @checked(old('notify_security_alerts', $user->notify_security_alerts ?? true))>
                        <label class="form-check-label" for="notify_security_alerts">
                            Security alerts (recommended)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="notify_request_updates" name="notify_request_updates" value="1"
                               @checked(old('notify_request_updates', $user->notify_request_updates ?? true))>
                        <label class="form-check-label" for="notify_request_updates">
                            Access request updates
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_weekly_summary" name="notify_weekly_summary" value="1"
                               @checked(old('notify_weekly_summary', $user->notify_weekly_summary ?? false))>
                        <label class="form-check-label" for="notify_weekly_summary">
                            Weekly summary email
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Security</div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>OTP Delivery:</strong> {{ strtoupper($otpChannel) }} (configured by system administrator)
            </div>
            <form method="POST" action="{{ route('settings.password.update') }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                           class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-lock"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Account Actions</div>
        <div class="card-body d-flex gap-2 flex-wrap">
            <a href="{{ route('profile') }}" class="btn btn-outline-secondary">
                <i class="bi bi-person-lines-fill"></i> Manage Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>
@endsection

