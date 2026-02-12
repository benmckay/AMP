<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
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
        
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');
        
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Log the login
            $this->logAuthEvent('User logged in');
            
            // Redirect to MFA if enabled
            if (Auth::user()->two_factor_secret) {
                return redirect()->route('2fa.verify');
            }
            
            return redirect()->intended(route('dashboard'));
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
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
}
