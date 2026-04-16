<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Bus;
use App\Models\BusSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // 1. Total penumpang (dari booking confirmed)
            $totalPassengers = Booking::where('booking_status', 'confirmed')->sum('total_passengers');

            // 2. Total transaksi (jumlah booking confirmed)
            $totalTransactions = Booking::where('booking_status', 'confirmed')->count();

            // 3. Total pendapatan (dari booking yang sudah dibayar)
            $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_price');

            // 4. Jadwal aktif (status active dan departure_time >= sekarang)
            $activeSchedules = BusSchedule::where('status', 'active')
                ->where('departure_time', '>=', now())
                ->count();

            // 5. Kursi terisi (total penumpang dari booking confirmed)
            $occupiedSeats = Booking::where('booking_status', 'confirmed')->sum('total_passengers');

            // 6. Data untuk grafik penjualan (7 hari terakhir)
            $salesChart = $this->getSalesChartData();

            // 7. Data untuk grafik rute terlaris (top 5)
            $topRoutes = $this->getTopRoutes();

            // 8. Data tambahan untuk ringkasan sistem
            $totalBuses = Bus::count();
            $totalUsers = User::count();
            $todayBookings = Booking::whereDate('created_at', Carbon::today())->count();
            $todayRevenue = Booking::whereDate('created_at', Carbon::today())
                ->where('payment_status', 'paid')
                ->sum('total_price');

            // 9. Pemesanan terbaru (5 data)
            $recentBookings = Booking::with('user')->latest()->take(5)->get()->map(function($booking) {
                return [
                    'booking_code' => $booking->booking_code,
                    'user_name' => $booking->user->name ?? 'N/A',
                    'total_price' => $booking->total_price,
                    'status' => $booking->booking_status,
                    'created_at' => $booking->created_at->format('Y-m-d H:i'),
                ];
            });

            return view('superadmin.dashboard', compact(
                'totalPassengers',
                'totalTransactions',
                'totalRevenue',
                'activeSchedules',
                'occupiedSeats',
                'salesChart',
                'topRoutes',
                'totalBuses',
                'totalUsers',
                'todayBookings',
                'todayRevenue',
                'recentBookings'
            ));

        } catch (\Exception $e) {
            Log::error('SuperAdmin Dashboard Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Fallback data
            return view('superadmin.dashboard', [
                'totalPassengers' => 0,
                'totalTransactions' => 0,
                'totalRevenue' => 0,
                'activeSchedules' => 0,
                'occupiedSeats' => 0,
                'salesChart' => ['labels' => [], 'data' => []],
                'topRoutes' => [],
                'totalBuses' => 0,
                'totalUsers' => 0,
                'todayBookings' => 0,
                'todayRevenue' => 0,
                'recentBookings' => collect()
            ]);
        }
    }

    private function getSalesChartData($days = 7)
    {
        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            $revenue = Booking::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('total_price');
            $data[] = $revenue;
        }
        return ['labels' => $labels, 'data' => $data];
    }

    private function getTopRoutes($limit = 5)
    {
        $routes = BusSchedule::select(
                'departure_city',
                'arrival_city',
                DB::raw('count(bookings.id) as total_bookings')
            )
            ->leftJoin('bookings', 'bus_schedules.id', '=', 'bookings.schedule_id')
            ->where('bookings.booking_status', 'confirmed')
            ->groupBy('bus_schedules.id', 'departure_city', 'arrival_city')
            ->orderByDesc('total_bookings')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'route' => $item->departure_city . ' → ' . $item->arrival_city,
                    'total_bookings' => $item->total_bookings
                ];
            });
        return $routes;
    }
}
