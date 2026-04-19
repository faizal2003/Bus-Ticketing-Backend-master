<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Booking;
use App\Models\BusSchedule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $totalBuses = Bus::count();
            $activeBuses = Bus::where('status', 'active')->count();

            $today = Carbon::today();
            $todayBookings = Booking::whereDate('created_at', $today)->count();
            $todayRevenue = Booking::whereDate('created_at', $today)
                ->where('payment_status', 'paid')
                ->sum('total_price');

            $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_price');

            $totalPassengers = Booking::where('booking_status', 'confirmed')->sum('total_passengers');
            $monthPassengers = Booking::whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->where('booking_status', 'confirmed')
                ->sum('total_passengers');

            $recentBookings = Booking::with(['user:id,name', 'schedule.bus'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function($booking) {
                    return [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'passenger_name' => $booking->user->name ?? 'N/A',
                        'seat_count' => $booking->total_passengers,
                        'status' => $booking->booking_status,
                        'created_at' => $booking->created_at->format('d/m/Y H:i'),
                    ];
                });

            $upcomingSchedules = BusSchedule::with('bus')
                ->where('departure_time', '>=', now())
                ->where('departure_time', '<=', now()->addDay())
                ->where('status', 'active')
                ->orderBy('departure_time', 'asc')
                ->take(5)
                ->get()
                ->map(function($schedule) {
                    // PERBAIKAN: gunakan total_seats, bukan capacity
                    $totalSeats = $schedule->bus->total_seats ?? 1; // default 1 agar tidak division by zero
                    $availableSeats = $schedule->available_seats ?? 0;
                    $seatPercentage = $totalSeats > 0 ? round(($availableSeats / $totalSeats) * 100) : 0;

                    return [
                        'id' => $schedule->id,
                        'departure' => $schedule->departure_city,
                        'arrival' => $schedule->arrival_city,
                        'bus_name' => $schedule->bus->bus_name ?? 'N/A',
                        'departure_time' => $schedule->departure_time->format('d/m/Y H:i'),
                        'available_seats' => $availableSeats,
                        'seat_percentage' => $seatPercentage,
                    ];
                });

            return view('admin.dashboard', compact(
                'totalBuses',
                'activeBuses',
                'todayBookings',
                'todayRevenue',
                'totalRevenue',
                'totalPassengers',
                'monthPassengers',
                'recentBookings',
                'upcomingSchedules'
            ));

        } catch (\Exception $e) {
            Log::error('Admin Dashboard Error: ' . $e->getMessage());

            return view('admin.dashboard', [
                'totalBuses' => 0,
                'activeBuses' => 0,
                'todayBookings' => 0,
                'todayRevenue' => 0,
                'totalRevenue' => 0,
                'totalPassengers' => 0,
                'monthPassengers' => 0,
                'recentBookings' => collect(),
                'upcomingSchedules' => collect(),
            ]);
        }
    }
}
