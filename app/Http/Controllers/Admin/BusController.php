<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $buses = $query->withCount(['schedules', 'schedules as active_schedules_count' => function($q) {
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

        return view('admin.buses.create', compact('types', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:buses,license_plate',
            'capacity' => 'required|integer|min:1|max:100',
            'type' => 'required|string|in:reguler,premium,vip,ekonomi,bisnis,executive',
            'facilities' => 'nullable|string',
            'status' => 'required|in:active,inactive,maintenance',
        ], [
            'name.required' => 'Nama bus wajib diisi',
            'license_plate.required' => 'Plat nomor wajib diisi',
            'license_plate.unique' => 'Plat nomor sudah terdaftar',
            'capacity.required' => 'Kapasitas wajib diisi',
            'capacity.min' => 'Kapasitas minimal 1 kursi',
            'capacity.max' => 'Kapasitas maksimal 100 kursi',
            'type.required' => 'Tipe bus wajib dipilih',
            'type.in' => 'Tipe bus tidak valid',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Bus::create([
                'name' => $request->name,
                'license_plate' => $request->license_plate,
                'capacity' => $request->capacity,
                'type' => $request->type,
                'facilities' => $request->facilities,
                'status' => $request->status,
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

            return view('admin.buses.edit', compact('bus', 'types', 'statuses'));
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

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'license_plate' => ['required', 'string', 'max:20', Rule::unique('buses')->ignore($bus->id)],
                'capacity' => 'required|integer|min:1|max:100',
                'type' => 'required|string|in:reguler,premium,vip,ekonomi,bisnis,executive',
                'facilities' => 'nullable|string',
                'status' => 'required|in:active,inactive,maintenance',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $bus->update([
                'name' => $request->name,
                'license_plate' => $request->license_plate,
                'capacity' => $request->capacity,
                'type' => $request->type,
                'facilities' => $request->facilities,
                'status' => $request->status,
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
