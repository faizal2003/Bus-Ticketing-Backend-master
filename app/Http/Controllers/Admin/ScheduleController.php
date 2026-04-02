<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = BusSchedule::with('bus')
            ->latest()
            ->paginate(10);

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buses = Bus::where('status', 'active')->get();
        return view('admin.schedules.create', compact('buses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'bus_id' => 'required|exists:buses,id',
            'departure_location' => 'required|string|max:255', // PERBAIKAN: ganti departure_city jika field berbeda
            'arrival_location' => 'required|string|max:255',   // PERBAIKAN: ganti arrival_city jika field berbeda
            'departure_time' => 'required|date|after_or_equal:now',
            'arrival_time' => 'required|date|after:departure_time',
            'price' => 'required|numeric|min:1000|max:1000000', // PERBAIKAN: ganti price_per_seat jika field berbeda
            'available_seats' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
            // 'notes' => 'nullable|string', // HAPUS jika kolom tidak ada di database
        ], [
            'bus_id.required' => 'Bus wajib dipilih',
            'bus_id.exists' => 'Bus tidak valid',
            'departure_location.required' => 'Lokasi keberangkatan wajib diisi',
            'arrival_location.required' => 'Lokasi tujuan wajib diisi',
            'departure_time.required' => 'Waktu keberangkatan wajib diisi',
            'departure_time.after_or_equal' => 'Waktu keberangkatan harus sekarang atau masa depan',
            'arrival_time.required' => 'Waktu kedatangan wajib diisi',
            'arrival_time.after' => 'Waktu kedatangan harus setelah waktu keberangkatan',
            'price.required' => 'Harga wajib diisi',
            'price.min' => 'Harga minimal Rp 1,000',
            'price.max' => 'Harga maksimal Rp 1,000,000',
            'available_seats.required' => 'Jumlah kursi tersedia wajib diisi',
            'available_seats.min' => 'Jumlah kursi minimal 1',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Cek apakah bus tersedia pada waktu tersebut
            $conflictSchedule = BusSchedule::where('bus_id', $request->bus_id)
                ->where(function($query) use ($request) {
                    $query->whereBetween('departure_time', [$request->departure_time, $request->arrival_time])
                          ->orWhereBetween('arrival_time', [$request->departure_time, $request->arrival_time])
                          ->orWhere(function($q) use ($request) {
                              $q->where('departure_time', '<=', $request->departure_time)
                                ->where('arrival_time', '>=', $request->arrival_time);
                          });
                })
                ->where('status', 'active')
                ->first();

            if ($conflictSchedule) {
                return redirect()->back()
                    ->with('error', 'Bus sudah memiliki jadwal pada waktu tersebut.')
                    ->withInput();
            }

            // PERBAIKAN: Hapus 'notes' dari data yang disimpan jika kolom tidak ada
            $scheduleData = [
                'bus_id' => $request->bus_id,
                'departure_location' => $request->departure_location, // PERBAIKAN: sesuaikan dengan nama field di database
                'arrival_location' => $request->arrival_location,     // PERBAIKAN: sesuaikan dengan nama field di database
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'price' => $request->price,                           // PERBAIKAN: sesuaikan dengan nama field di database
                'available_seats' => $request->available_seats,
                'status' => $request->status,
                // 'notes' => $request->notes, // HAPUS JIKA KOLOM TIDAK ADA
            ];

            $schedule = BusSchedule::create($scheduleData);

            // Update available_seats di bus jika diperlukan
            $bus = Bus::find($request->bus_id);
            if ($bus) {
                $bus->update([
                    'available_seats' => $request->available_seats
                ]);
            }

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Jadwal bus berhasil ditambahkan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan jadwal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $schedule = BusSchedule::findOrFail($id);
        $buses = Bus::where('status', 'active')->get();

        return view('admin.schedules.edit', compact('schedule', 'buses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $schedule = BusSchedule::findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'bus_id' => 'required|exists:buses,id',
            'departure_location' => 'required|string|max:255',
            'arrival_location' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'price' => 'required|numeric|min:1000|max:1000000',
            'available_seats' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
            // 'notes' => 'nullable|string', // HAPUS jika kolom tidak ada
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Cek konflik jadwal (kecuali dengan jadwal itu sendiri)
            $conflictSchedule = BusSchedule::where('bus_id', $request->bus_id)
                ->where('id', '!=', $id)
                ->where(function($query) use ($request) {
                    $query->whereBetween('departure_time', [$request->departure_time, $request->arrival_time])
                          ->orWhereBetween('arrival_time', [$request->departure_time, $request->arrival_time])
                          ->orWhere(function($q) use ($request) {
                              $q->where('departure_time', '<=', $request->departure_time)
                                ->where('arrival_time', '>=', $request->arrival_time);
                          });
                })
                ->where('status', 'active')
                ->first();

            if ($conflictSchedule) {
                return redirect()->back()
                    ->with('error', 'Bus sudah memiliki jadwal pada waktu tersebut.')
                    ->withInput();
            }

            // PERBAIKAN: Hapus 'notes' dari data yang diupdate
            $scheduleData = [
                'bus_id' => $request->bus_id,
                'departure_location' => $request->departure_location,
                'arrival_location' => $request->arrival_location,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'price' => $request->price,
                'available_seats' => $request->available_seats,
                'status' => $request->status,
                // 'notes' => $request->notes, // HAPUS JIKA KOLOM TIDAK ADA
            ];

            $schedule->update($scheduleData);

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Jadwal bus berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $schedule = BusSchedule::findOrFail($id);

            // Cek apakah jadwal memiliki booking
            if ($schedule->bookings()->exists()) {
                return redirect()->route('admin.schedules.index')
                    ->with('error', 'Tidak dapat menghapus jadwal yang memiliki booking.');
            }

            $schedule->delete();

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Jadwal berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Toggle schedule status
     */
    public function toggleStatus($id)
    {
        try {
            $schedule = BusSchedule::findOrFail($id);

            $schedule->update([
                'status' => $schedule->status == 'active' ? 'inactive' : 'active'
            ]);

            $status = $schedule->status == 'active' ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->route('admin.schedules.index')
                ->with('success', "Jadwal berhasil $status.");

        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Gagal mengubah status jadwal: ' . $e->getMessage());
        }
    }
}
