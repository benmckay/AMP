<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const OTP_SESSION_KEY = 'auth.pending_login';
    private const OTP_PURPOSE_LOGIN = 'login';

    public function __construct(private readonly OtpService $otpService)
    {
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->input('email'))->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('email'));
        }

        if ($this->otpDeliveryChannel() === 'email' && empty($user->email)) {
            throw ValidationException::withMessages([
                'email' => 'Your account does not have an email configured for OTP login. Please contact support.',
            ]);
        }

        if ($this->otpDeliveryChannel() !== 'email' && empty($user->phone)) {
            throw ValidationException::withMessages([
                'email' => 'Your account does not have a phone number configured for OTP login. Please contact support.',
            ]);
        }

        $request->session()->put(self::OTP_SESSION_KEY, [
            'user_id' => $user->id,
            'remember' => $request->boolean('remember'),
        ]);

        if (!$this->generateAndSendOtp($user, $request)) {
            return redirect()
                ->route('login.otp.form')
                ->withErrors([
                    'otp_send' => 'Unable to send OTP at the moment. Please try again shortly.',
                ]);
        }

        return redirect()
            ->route('login.otp.form')
            ->with('status', 'OTP sent successfully.');
    }

    /**
     * Show OTP verification form.
     */
    public function showOtpForm(Request $request)
    {
        $pendingLogin = $request->session()->get(self::OTP_SESSION_KEY);
        if (!$pendingLogin || empty($pendingLogin['user_id'])) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your login session expired. Please sign in again.']);
        }

        $user = User::find($pendingLogin['user_id']);
        if (!$user) {
            $request->session()->forget(self::OTP_SESSION_KEY);

            return redirect()->route('login')
                ->withErrors(['email' => 'Unable to continue login. Please sign in again.']);
        }

        return view('auth.otp', [
            'maskedDestination' => $this->otpDeliveryChannel() === 'email'
                ? $this->maskEmail((string) $user->email)
                : $this->maskPhone((string) $user->phone),
            'destinationType' => $this->otpDeliveryChannel() === 'email' ? 'email' : 'phone',
            'email' => $user->email,
        ]);
    }

    /**
     * Verify submitted OTP and complete login.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $pendingLogin = $request->session()->get(self::OTP_SESSION_KEY);
        if (!$pendingLogin || empty($pendingLogin['user_id'])) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your login session expired. Please sign in again.']);
        }

        $user = User::find($pendingLogin['user_id']);
        if (!$user) {
            $this->clearPendingOtpState($request);

            return redirect()->route('login')
                ->withErrors(['email' => 'Unable to continue login. Please sign in again.']);
        }

        $result = $this->otpService->verify(
            $user,
            $request->input('otp'),
            self::OTP_PURPOSE_LOGIN,
            $request
        );

        if (!$result['success']) {
            if ($result['status'] === 'expired') {
                $this->clearPendingOtpState($request);

                return redirect()->route('login')
                    ->withErrors(['email' => 'OTP expired. Please sign in again to request a new code.']);
            }

            if ($result['status'] === 'locked') {
                $this->clearPendingOtpState($request);

                return redirect()->route('login')
                    ->withErrors(['email' => 'Too many invalid OTP attempts. Please sign in again.']);
            }

            return back()->withErrors([
                'otp' => 'Invalid OTP.',
            ]);
        }

        $request->session()->forget(self::OTP_SESSION_KEY);

        Auth::login($user, (bool) ($pendingLogin['remember'] ?? false));
        $request->session()->regenerate();

        $this->logAuthEvent('User logged in with OTP');

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Resend OTP for pending login.
     */
    public function resendOtp(Request $request)
    {
        $pendingLogin = $request->session()->get(self::OTP_SESSION_KEY);
        if (!$pendingLogin || empty($pendingLogin['user_id'])) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your login session expired. Please sign in again.']);
        }

        $user = User::find($pendingLogin['user_id']);
        if (!$user) {
            $this->clearPendingOtpState($request);

            return redirect()->route('login')
                ->withErrors(['email' => 'Unable to continue login. Please sign in again.']);
        }

        $result = $this->otpService->send($user, self::OTP_PURPOSE_LOGIN, $request, true);
        if (!$result['success']) {
            if ($result['status'] === 'cooldown') {
                $seconds = (int) ($result['retry_after'] ?? config('otp.resend_cooldown', 60));

                return back()->withErrors([
                    'otp' => "Too many resend requests. Try again in {$seconds} seconds.",
                ]);
            }

            return back()->withErrors([
                'otp_send' => 'Unable to resend OTP at the moment. Please try again shortly.',
            ]);
        }

        if (!empty($result['provider_error'])) {
            session()->flash('otp_delivery_error', $result['provider_error']);
        } else {
            session()->forget('otp_delivery_error');
        }

        if (!empty($result['fallback_otp'])) {
            session()->flash('otp_fallback_code', (string) $result['fallback_otp']);
        }

        if (!empty($result['delivery_info'])) {
            session()->flash('otp_delivery_info', (string) $result['delivery_info']);
        } elseif (!empty($result['is_sandbox'])) {
            session()->flash(
                'otp_delivery_info',
                "Africa's Talking sandbox does not deliver real SMS. Use this OTP for testing."
            );
        }

        return back()->with('status', 'OTP sent successfully.');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $this->logAuthEvent('User logged out');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Log auth events to Spatie activity log when available, otherwise Laravel log.
     */
    private function logAuthEvent(string $message): void
    {
        $user = Auth::user();

        if (function_exists('activity')) {
            activity()
                ->causedBy($user)
                ->log($message);

            return;
        }

        Log::info($message, [
            'user_id' => $user?->id,
            'email' => $user?->email,
        ]);
    }

    private function generateAndSendOtp(User $user, Request $request): bool
    {
        $result = $this->otpService->send($user, self::OTP_PURPOSE_LOGIN, $request, false);
        if (!$result['success']) {
            if (!empty($result['provider_error'])) {
                session()->flash('otp_delivery_error', $result['provider_error']);
            }

            return false;
        }

        if (!empty($result['provider_error'])) {
            session()->flash('otp_delivery_error', $result['provider_error']);
        } else {
            session()->forget('otp_delivery_error');
        }

        if (!empty($result['fallback_otp'])) {
            session()->flash('otp_fallback_code', (string) $result['fallback_otp']);
        }

        if (!empty($result['delivery_info'])) {
            session()->flash('otp_delivery_info', (string) $result['delivery_info']);
        } elseif (!empty($result['is_sandbox'])) {
            session()->flash(
                'otp_delivery_info',
                "Africa's Talking sandbox does not deliver real SMS. Use this OTP for testing."
            );
        }

        return true;
    }

    private function maskPhone(string $phone): string
    {
        $trimmed = preg_replace('/\s+/', '', $phone) ?? $phone;
        $length = strlen($trimmed);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4).substr($trimmed, -4);
    }

    private function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return '***';
        }

        [$name, $domain] = explode('@', $email, 2);
        $maskedName = strlen($name) <= 2
            ? substr($name, 0, 1).'*'
            : substr($name, 0, 2).str_repeat('*', max(strlen($name) - 2, 1));

        return $maskedName.'@'.$domain;
    }

    private function otpDeliveryChannel(): string
    {
        return strtolower((string) config('otp.delivery_channel', 'internal'));
    }

    private function clearPendingOtpState(Request $request): void
    {
        $request->session()->forget(self::OTP_SESSION_KEY);
    }
}
