<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip untuk beberapa route
        if ($request->is('login') ||
            $request->is('logout') ||
            $request->is('admin/session/*') ||
            $request->is('api/*')) {
            return $next($request);
        }

        // Cek apakah user sudah login (pakai Auth facade)
        if (Auth::check()) {
            // Simpan waktu aktivitas terakhir
            Session::put('last_activity', now()->timestamp);

            $lastActivity = Session::get('last_activity');
            $sessionLifetime = config('session.lifetime', 120); // menit

            // Jika melebihi batas waktu tidak aktif → logout
            if ($lastActivity && (now()->timestamp - $lastActivity) > ($sessionLifetime * 60)) {
                Auth::logout();
                Session::flush();
                Session::regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Session expired'], 401);
                }

                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.');
            }
        }

        return $next($request);
    }
}
