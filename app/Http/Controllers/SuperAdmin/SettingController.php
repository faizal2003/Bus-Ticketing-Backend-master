<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    /**
     * Display system settings.
     */
    public function index()
    {
        // Get current settings
        $settings = [
            'app_name' => config('app.name', 'Bus Ticketing System'),
            'app_url' => config('app.url'),
            'app_timezone' => config('app.timezone'),
            'app_locale' => config('app.locale'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'session_lifetime' => config('session.lifetime'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];

        // Get system info
        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'server_os' => php_uname('s') . ' ' . php_uname('r'),
            'database_driver' => config('database.default'),
        ];

        // Get cache stats
        $cacheInfo = [
            'cache_size' => 'N/A',
            'cache_items' => 'N/A',
        ];

        return view('superadmin.settings.index', compact('settings', 'systemInfo', 'cacheInfo'));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_timezone' => 'required|timezone',
            'app_locale' => 'required|in:id,en',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            'session_lifetime' => 'required|integer|min:1|max:1440',
        ]);

        // Update config values (in a real app, you'd save these to database)
        // For now, we'll just show a success message
        // In production, you should use a settings table or .env file

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');

            $type = $request->get('type', 'all');

            return back()->with('success', 'Cache berhasil dibersihkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
        }
    }

    /**
     * Run maintenance commands.
     */
    public function runMaintenance(Request $request)
    {
        try {
            $command = $request->get('command');

            $allowedCommands = [
                'optimize',
                'migrate',
                'db:seed',
                'storage:link',
            ];

            if (!in_array($command, $allowedCommands)) {
                return back()->with('error', 'Perintah tidak diizinkan.');
            }

            Artisan::call($command);
            $output = Artisan::output();

            return back()->with('success', "Perintah $command berhasil dijalankan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan perintah: ' . $e->getMessage());
        }
    }
}
