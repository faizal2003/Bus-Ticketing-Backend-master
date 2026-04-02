<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleSocialiteController extends Controller
{
    /**
     * Redirect ke Google untuk login.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google.
     */
    public function handleCallback()
    {
        try {
            // For development, temporarily disable SSL verification
            // This is NOT recommended for production
            $guzzleClient = new \GuzzleHttp\Client([
                'verify' => false  // Disable SSL verification for development
            ]);
            
            // Get the Socialite driver and set custom HTTP client
            $driver = Socialite::driver('google');
            
            // Use reflection to set the HTTP client (hacky but works)
            $reflection = new \ReflectionClass($driver);
            $property = $reflection->getProperty('httpClient');
            $property->setAccessible(true);
            $property->setValue($driver, $guzzleClient);
            
            $googleUser = $driver->user();
        } catch (Exception $e) {
            // Log the actual error for debugging
            Log::error('Google OAuth Error: ' . $e->getMessage());
            
            return redirect('/login')->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }

        // Cari user berdasarkan email
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            // Buat user baru dengan role penumpang
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make(uniqid()),
                'phone' => '',
                'role' => 'penumpang',
                'is_active' => true,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
            ]);
        } else {
            // Cek apakah user aktif
            if (!$user->is_active) {
                return redirect('/login')->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi administrator.');
            }

            // Hanya izinkan penumpang untuk login dengan Google
            if ($user->role !== 'penumpang') {
                return redirect('/login')->with('error', 'Login dengan Google hanya untuk penumpang.');
            }

            // Update google_id jika belum ada
            if (empty($user->google_id)) {
                $user->update(['google_id' => $googleUser->id]);
            }

            // Update avatar jika belum ada
            if (empty($user->avatar) && $googleUser->avatar) {
                $user->update(['avatar' => $googleUser->avatar]);
            }
        }

        // Login user
        Auth::login($user, true);

        // Regenerate session untuk keamanan
        request()->session()->regenerate();

        // Set last activity for CheckSession middleware
        session()->put('last_activity', now()->timestamp);

        // Redirect berdasarkan role
        if ($user->role === 'super_admin') {
            return redirect(route('superadmin.dashboard'));
        } elseif ($user->role === 'admin') {
            return redirect(route('admin.dashboard'));
        } elseif ($user->role === 'kondektur') {
            return redirect(route('conductor.dashboard'));
        } else {
            return redirect(route('dashboard'));
        }
    }
}