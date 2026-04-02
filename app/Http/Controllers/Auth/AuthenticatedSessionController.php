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
        // Validasi tambahan untuk user aktif
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cek kredensial
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->withInput();
        }

        // Cek apakah user aktif
        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ])->withInput();
        }

        $request->session()->regenerate();

        // Set last activity for CheckSession middleware
        $request->session()->put('last_activity', now()->timestamp);

        // Redirect berdasarkan role
        if ($user->role === 'super_admin') {
            return redirect(route('superadmin.dashboard'));
        } elseif ($user->role === 'admin') {
            return redirect(route('admin.dashboard'));
        } elseif ($user->role === 'kondektur') {
            return redirect(route('conductor.dashboard'));
        }

        return redirect(route('dashboard'));
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
