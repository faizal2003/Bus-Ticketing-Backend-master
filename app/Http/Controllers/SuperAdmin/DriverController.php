<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('license_number', 'LIKE', "%{$search}%");
            });
        }

        $drivers = $query->latest()->paginate(10);
        return view('superadmin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('superadmin.drivers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'license_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required' => 'Nama driver wajib diisi.',
            'phone.required' => 'Nomor HP driver wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'picture.image' => 'File harus berupa gambar.',
            'picture.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'picture.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $data = $request->except('picture');

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('drivers', 'public');
            $data['picture'] = $path;
        }

        Driver::create($data);

        return redirect()->route('superadmin.drivers.index')
            ->with('success', 'Driver berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $driver = Driver::findOrFail($id);
        return view('superadmin.drivers.edit', compact('driver'));
    }

    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'license_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required' => 'Nama driver wajib diisi.',
            'phone.required' => 'Nomor HP driver wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'picture.image' => 'File harus berupa gambar.',
            'picture.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'picture.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $data = $request->except('picture');

        if ($request->hasFile('picture')) {
            if ($driver->picture) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($driver->picture);
            }
            $path = $request->file('picture')->store('drivers', 'public');
            $data['picture'] = $path;
        }

        $driver->update($data);

        return redirect()->route('superadmin.drivers.index')
            ->with('success', 'Driver berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);

        // Check if driver is currently assigned to any bus
        if ($driver->buses()->exists()) {
            return redirect()->route('superadmin.drivers.index')
                ->with('error', 'Gagal menghapus driver: Driver ini masih ditugaskan ke armada bus.');
        }

        if ($driver->picture) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($driver->picture);
        }

        $driver->delete();

        return redirect()->route('superadmin.drivers.index')
            ->with('success', 'Driver berhasil dihapus.');
    }
}
