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
        $validator = Validator::make($request->all(), [
            'bus_id' => 'required|exists:buses,id',
            'departure_city' => 'required|string|max:255',      // ← ganti
            'arrival_city' => 'required|string|max:255',        // ← ganti
            'departure_time' => 'required|date|after_or_equal:now',
            'arrival_time' => 'required|date|after:departure_time',
            'price_per_seat' => 'required|numeric|min:1000|max:1000000', // ← ganti
            'available_seats' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $bus = Bus::findOrFail($request->bus_id);

            // Jika available_seats tidak diisi, gunakan total_seats bus
            $availableSeats = $request->filled('available_seats') ? $request->available_seats : $bus->total_seats;

            // Cek konflik jadwal
            $conflict = BusSchedule::where('bus_id', $request->bus_id)
                ->where(function($q) use ($request) {
                    $q->whereBetween('departure_time', [$request->departure_time, $request->arrival_time])
                    ->orWhereBetween('arrival_time', [$request->departure_time, $request->arrival_time]);
                })
                ->where('status', 'active')
                ->exists();

            if ($conflict) {
                return redirect()->back()->with('error', 'Bus sudah memiliki jadwal pada waktu tersebut.')->withInput();
            }

            BusSchedule::create([
                'bus_id' => $request->bus_id,
                'departure_city' => $request->departure_city,
                'arrival_city' => $request->arrival_city,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'price_per_seat' => $request->price_per_seat,
                'available_seats' => $availableSeats,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.schedules.index')->with('success', 'Jadwal bus berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $schedule = BusSchedule::with(['bus', 'bookings.user'])->findOrFail($id);
            return view('admin.schedules.show', compact('schedule'));
        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Jadwal tidak ditemukan.');
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

        $validator = Validator::make($request->all(), [
            'bus_id' => 'required|exists:buses,id',
            'departure_city' => 'required|string|max:255',
            'arrival_city' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'price_per_seat' => 'required|numeric|min:1000|max:1000000',
            'available_seats' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Cek konflik kecuali dirinya sendiri
            $conflict = BusSchedule::where('bus_id', $request->bus_id)
                ->where('id', '!=', $id)
                ->where(function($q) use ($request) {
                    $q->whereBetween('departure_time', [$request->departure_time, $request->arrival_time])
                    ->orWhereBetween('arrival_time', [$request->departure_time, $request->arrival_time]);
                })
                ->where('status', 'active')
                ->exists();

            if ($conflict) {
                return redirect()->back()->with('error', 'Bus sudah memiliki jadwal pada waktu tersebut.')->withInput();
            }

            $schedule->update([
                'bus_id' => $request->bus_id,
                'departure_city' => $request->departure_city,
                'arrival_city' => $request->arrival_city,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'price_per_seat' => $request->price_per_seat,
                'available_seats' => $request->available_seats,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.schedules.index')->with('success', 'Jadwal bus berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage())->withInput();
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
