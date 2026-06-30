<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Bus::query();

        // Filter pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('license_plate', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }

        // Filter status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Load dengan schedules count
        $buses = $query->with(['driver', 'conductor'])->withCount(['schedules', 'schedules as active_schedules_count' => function($q) {
            $q->where('status', 'active');
        }])->latest()->paginate(10);

        return view('admin.buses.index', compact('buses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = [
            'reguler' => 'Reguler',
            'premium' => 'Premium',
            'vip' => 'VIP',
            'ekonomi' => 'Ekonomi',
            'bisnis' => 'Bisnis',
            'executive' => 'Executive'
        ];

        $statuses = [
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'maintenance' => 'Maintenance'
        ];

        $drivers = Driver::where('status', 'active')->orderBy('name', 'asc')->get();
        $conductors = User::conductors()->active()->orderBy('name', 'asc')->get();

        return view('admin.buses.create', compact('types', 'statuses', 'drivers', 'conductors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Normalisasi input
        $request->merge([
            'plate_number' => strtoupper($request->plate_number),
        ]);

        $validator = Validator::make($request->all(), [
            'bus_name'       => 'required|string|max:255',
            'bus_number'     => 'required|integer|min:1|unique:buses,bus_number',   // ← tambahkan
            'plate_number'   => ['required', 'string', 'max:20', 'unique:buses,plate_number', 'regex:/^[A-Z]{1,2}\s\d{1,4}\s[A-Z]{0,3}$/'],
            'total_seats'    => 'required|integer|min:1|max:100',
            'bus_type'       => 'required|string|in:Regular,Executive,VIP,Super,reguler,premium,vip,ekonomi,bisnis,executive',
            'facilities'     => 'nullable|array',   // ← ubah jadi array
            'status'         => 'required|in:active,inactive,maintenance',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'driver_id'      => 'nullable|exists:drivers,id',
            'conductor_id'   => 'nullable|exists:users,id',
        ], [
            'bus_name.required'    => 'Nama bus wajib diisi',
            'bus_number.required'  => 'Nomor bus wajib diisi',
            'bus_number.unique'    => 'Nomor bus sudah terdaftar',
            'plate_number.required'=> 'Plat nomor wajib diisi',
            'plate_number.unique'  => 'Plat nomor sudah terdaftar',
            'plate_number.regex'   => 'Format plat nomor tidak valid (Contoh: B 1234 ABC)',
            'total_seats.required' => 'Kapasitas wajib diisi',
            'total_seats.min'      => 'Kapasitas minimal 1 kursi',
            'bus_type.required'    => 'Tipe bus wajib dipilih',
            'bus_type.in'          => 'Tipe bus tidak valid',
            'status.required'      => 'Status wajib dipilih',
            'facilities.array'     => 'Format fasilitas tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Normalisasi bus_type ke lowercase agar konsisten
            $busType = strtolower($request->bus_type);

            // Facilities sudah dalam bentuk array dari checkbox
            $facilities = $request->facilities ?? [];
            $facilities = array_filter($facilities); // hapus nilai kosong

            $imageName = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('buses', 'public');
                $imageName = basename($imagePath);
            }

            Bus::create([
                'bus_name'     => $request->bus_name,
                'bus_number'   => $request->bus_number,   // ← simpan bus_number
                'plate_number' => $request->plate_number,
                'total_seats'  => $request->total_seats,
                'bus_type'     => $busType,
                'facilities'   => $facilities,
                'status'       => $request->status,
                'image'        => $imageName,
                'driver_id'    => $request->driver_id,
                'conductor_id' => $request->conductor_id,
            ]);

            return redirect()->route('admin.buses.index')
                ->with('success', 'Bus berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan bus: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $bus = Bus::with(['schedules', 'driver', 'conductor'])->findOrFail($id);
            return view('admin.buses.show', compact('bus'));
        } catch (\Exception $e) {
            return redirect()->route('admin.buses.index')
                ->with('error', 'Bus tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $bus = Bus::findOrFail($id);

            $types = [
                'reguler' => 'Reguler',
                'premium' => 'Premium',
                'vip' => 'VIP',
                'ekonomi' => 'Ekonomi',
                'bisnis' => 'Bisnis',
                'executive' => 'Executive'
            ];

            $statuses = [
                'active' => 'Aktif',
                'inactive' => 'Nonaktif',
                'maintenance' => 'Maintenance'
            ];

            $drivers = Driver::where('status', 'active')->orderBy('name', 'asc')->get();
            $conductors = User::conductors()->active()->orderBy('name', 'asc')->get();

            return view('admin.buses.edit', compact('bus', 'types', 'statuses', 'drivers', 'conductors'));
        } catch (\Exception $e) {
            return redirect()->route('admin.buses.index')
                ->with('error', 'Bus tidak ditemukan.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $bus = Bus::findOrFail($id);

            // Normalisasi input
            $request->merge([
                'plate_number' => strtoupper($request->plate_number),
            ]);

            $validator = Validator::make($request->all(), [
                'bus_name'       => 'required|string|max:255',
                'bus_number'     => ['required', 'integer', 'min:1', Rule::unique('buses')->ignore($bus->id)],
                'plate_number'   => ['required', 'string', 'max:20', Rule::unique('buses')->ignore($bus->id), 'regex:/^[A-Z]{1,2}\s\d{1,4}\s[A-Z]{0,3}$/'],
                'total_seats'    => 'required|integer|min:1|max:100',
                'bus_type'       => 'required|string|in:Regular,Executive,VIP,Super,reguler,premium,vip,ekonomi,bisnis,executive',
                'facilities'     => 'nullable|array',
                'status'         => 'required|in:active,inactive,maintenance',
                'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'driver_id'      => 'nullable|exists:drivers,id',
                'conductor_id'   => 'nullable|exists:users,id',
            ], [
                'plate_number.regex' => 'Format plat nomor tidak valid (Contoh: B 1234 ABC)',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $busType = strtolower($request->bus_type);
            $facilities = $request->facilities ?? [];
            $facilities = array_filter($facilities);

            $imageName = $bus->image;
            if ($request->hasFile('image')) {
                if ($bus->image) {
                    Storage::disk('public')->delete('buses/' . $bus->image);
                }
                $imagePath = $request->file('image')->store('buses', 'public');
                $imageName = basename($imagePath);
            }

            $bus->update([
                'bus_name'     => $request->bus_name,
                'bus_number'   => $request->bus_number,
                'plate_number' => $request->plate_number,
                'total_seats'  => $request->total_seats,
                'bus_type'     => $busType,
                'facilities'   => $facilities,
                'status'       => $request->status,
                'image'        => $imageName,
                'driver_id'    => $request->driver_id,
                'conductor_id' => $request->conductor_id,
            ]);

            return redirect()->route('admin.buses.index')
                ->with('success', 'Bus berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui bus: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $bus = Bus::findOrFail($id);

            // Cek apakah bus memiliki jadwal
            if ($bus->schedules()->exists()) {
                return redirect()->route('admin.buses.index')
                    ->with('error', 'Tidak dapat menghapus bus yang memiliki jadwal. Hapus jadwal terlebih dahulu.');
            }

            if ($bus->image) {
                Storage::disk('public')->delete('buses/' . $bus->image);
            }

            $bus->delete();

            return redirect()->route('admin.buses.index')
                ->with('success', 'Bus berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->route('admin.buses.index')
                ->with('error', 'Gagal menghapus bus: ' . $e->getMessage());
        }
    }

    /**
     * Toggle bus status.
     */
    public function toggleStatus($id)
    {
        try {
            $bus = Bus::findOrFail($id);
            $newStatus = $bus->status == 'active' ? 'inactive' : 'active';
            $bus->update(['status' => $newStatus]);
            $statusText = $newStatus == 'active' ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->route('admin.buses.index')
                ->with('success', "Bus berhasil $statusText.");
        } catch (\Exception $e) {
            return redirect()->route('admin.buses.index')
                ->with('error', 'Gagal mengubah status bus: ' . $e->getMessage());
        }
    }
}
