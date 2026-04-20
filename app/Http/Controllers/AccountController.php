<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function profile(): View
    {
        $user = auth()->user()->load(['roles', 'departmentAssignments.department']);

        $requestStats = [
            'total_requests' => AccessRequest::where('requester_id', $user->id)->count(),
            'pending_requests' => AccessRequest::where('requester_id', $user->id)->where('status', 'pending')->count(),
            'fulfilled_requests' => AccessRequest::where('requester_id', $user->id)->where('status', 'fulfilled')->count(),
            'audit_events' => AuditLog::where('user_id', $user->id)->count(),
        ];

        return view('profile.show', compact('user', 'requestStats'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'payroll_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'payroll_number')->ignore($user->id)],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile details updated successfully.');
    }

    public function settings(): View
    {
        $user = auth()->user()->load('roles');

        return view('settings.index', [
            'user' => $user,
            'otpChannel' => strtolower((string) config('otp.delivery_channel', 'internal')),
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_timezone' => ['nullable', 'string', 'max:64'],
            'preferred_language' => ['required', Rule::in(['en', 'sw'])],
            'theme' => ['required', Rule::in(['system', 'light', 'dark'])],
        ]);

        $validated['notify_security_alerts'] = $request->boolean('notify_security_alerts');
        $validated['notify_request_updates'] = $request->boolean('notify_request_updates');
        $validated['notify_weekly_summary'] = $request->boolean('notify_weekly_summary');

        auth()->user()->update($validated);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}

