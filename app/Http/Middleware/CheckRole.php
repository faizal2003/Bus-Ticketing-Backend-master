<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Cek jika user tidak terautentikasi
        if (!Auth::check()) {
            // Untuk API, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated. Please login first.',
                    'code' => 401
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Periksa apakah user memiliki salah satu role yang diizinkan
        if (!in_array($user->role, $roles)) {
            // Untuk API, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Required role: ' . implode(', ', $roles),
                    'code' => 403
                ], 403);
            }

            // Untuk web, redirect berdasarkan role
            switch ($user->role) {
                case 'super_admin':
                    return redirect()->route('superadmin.dashboard');
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'kondektur':
                    return redirect()->route('conductor.dashboard');
                default:
                    return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
