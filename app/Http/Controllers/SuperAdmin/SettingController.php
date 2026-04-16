<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    /**
     * Display system configuration page.
     */
    public function index()
    {
        // Ambil semua data sekali saja
        $allSettings = Setting::all();

        // Group by group untuk keperluan lain (jika dibutuhkan di view)
        $settings = $allSettings->groupBy('group');

        // Buat array key-value untuk akses mudah di view
        $settingValues = [];
        foreach ($allSettings as $setting) {
            $settingValues[$setting->key] = $setting->value;
        }

        return view('superadmin.settings.index', compact('settings', 'settingValues'));
    }

    /**
     * Update system configuration.
     */
    public function update(Request $request)
    {
        $rules = [
            'company_name' => 'required|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'midtrans_server_key' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
            'midtrans_merchant_id' => 'nullable|string',
            'midtrans_environment' => 'required|in:sandbox,production',
            'payment_timeout' => 'required|integer|min:1|max:1440',
            'qr_size' => 'required|integer|min:100|max:800',
            'qr_margin' => 'required|integer|min:0|max:50',
            'qr_error_correction' => 'required|in:L,M,Q,H',
            'ticket_format' => 'required|string',
        ];

        $validated = $request->validate($rules);

        // Update atau create setiap setting
        foreach ($validated as $key => $value) {
            if ($key === 'company_logo') {
                if ($request->hasFile('company_logo')) {
                    // Hapus logo lama jika ada
                    $oldLogo = Setting::get('company_logo');
                    if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                        Storage::disk('public')->delete($oldLogo);
                    }
                    // Upload logo baru
                    $path = $request->file('company_logo')->store('logos', 'public');
                    $value = $path;
                } else {
                    continue; // tidak ada perubahan logo
                }
            }
            Setting::set($key, $value);
        }

        return redirect()->route('superadmin.settings.index')
            ->with('success', 'Konfigurasi sistem berhasil diperbarui.');
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
            $allowedCommands = ['optimize', 'migrate', 'db:seed', 'storage:link'];
            if (!in_array($command, $allowedCommands)) {
                return back()->with('error', 'Perintah tidak diizinkan.');
            }
            Artisan::call($command);
            return back()->with('success', "Perintah $command berhasil dijalankan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan perintah: ' . $e->getMessage());
        }
    }
}
