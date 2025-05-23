<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Get the authenticated user
        $user = Auth::user();
        $role = $user->role; // Get the role

        // Log the role value for debugging
        \Log::info('User role detected: ' . $role);
        \Log::info('User data:', ['user' => $user->toArray()]);

        // Make case-insensitive comparison and trim any whitespace
        $role = strtolower(trim($role));

        // Redirect based on user role with improved comparison
        if ($role == 'student') {
            return redirect()->intended('/student');
        } elseif ($role == 'admin') {
            return redirect()->intended('/admin');
        } elseif ($role == 'instructor') {
            return redirect()->intended('/instructor');
        }

        // Log if no matching role was found
        \Log::warning('No matching role found for: ' . $role);

        // Default fallback redirect
        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
