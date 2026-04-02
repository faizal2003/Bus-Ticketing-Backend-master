<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle dashboard redirection based on user role
     */
    public function index(Request $request)
    {
        // Gunakan user dari request (lebih reliable)
        $user = $request->user();

        // Jika user tidak login, redirect ke login
        if (!$user) {
            return redirect()->route('login');
        }

        // Redirect berdasarkan role
        switch ($user->role) {
            case 'super_admin':
                return redirect()->route('superadmin.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'kondektur':
                return redirect()->route('conductor.dashboard');
            case 'penumpang':
                // Jika ada view khusus penumpang
                if (view()->exists('passenger.dashboard')) {
                    return view('passenger.dashboard');
                }
                return view('dashboard');
            default:
                // Untuk role lainnya atau fallback
                return view('dashboard');
        }
    }
}
