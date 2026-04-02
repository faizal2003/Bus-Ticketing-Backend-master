<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Hanya format response untuk API (route yang dimulai dengan /api/)
        if ($request->is('api/*')) {
            $original = $response->original;

            // Jika response sudah berupa JSON dengan format standar, biarkan
            if (is_array($original) && isset($original['status'])) {
                return $response;
            }

            // Format response untuk data
            if (is_array($original) || is_object($original)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Request successful',
                    'data' => $original,
                    'timestamp' => now()->toDateTimeString()
                ]);
            }
        }

        return $response;
    }
}
