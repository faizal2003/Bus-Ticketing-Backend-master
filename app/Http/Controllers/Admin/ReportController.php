<?php
// app/Http\Controllers/Admin\ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // 1. Hitung statistik dasar
            $totalBookings = Booking::whereBetween('created_at', [$start, $end])->count();
            $totalRevenue = Booking::whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'paid')
                ->sum('total_price');
            $totalPassengers = Booking::whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'paid')
                ->where('booking_status', 'confirmed')
                ->sum('total_passengers');

            // 2. Periode sebelumnya untuk perbandingan
            $previousStart = $start->copy()->subDays($start->diffInDays($end));
            $previousEnd = $start->copy()->subDay()->endOfDay();

            $previousBookings = Booking::whereBetween('created_at', [$previousStart, $previousEnd])->count();
            $previousRevenue = Booking::whereBetween('created_at', [$previousStart, $previousEnd])
                ->where('payment_status', 'paid')
                ->sum('total_price');
            $previousPassengers = Booking::whereBetween('created_at', [$previousStart, $previousEnd])
                ->where('payment_status', 'paid')
                ->where('booking_status', 'confirmed')
                ->sum('total_passengers');

            // 3. Hitung persentase perubahan
            $bookingChange = $previousBookings > 0
                ? (($totalBookings - $previousBookings) / $previousBookings) * 100
                : ($totalBookings > 0 ? 100 : 0);

            $revenueChange = $previousRevenue > 0
                ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100
                : ($totalRevenue > 0 ? 100 : 0);

            $passengerChange = $previousPassengers > 0
                ? (($totalPassengers - $previousPassengers) / $previousPassengers) * 100
                : ($totalPassengers > 0 ? 100 : 0);

            // 4. Distribusi status
            $statusDistribution = Booking::select('booking_status')
                ->selectRaw('COUNT(*) as count')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('booking_status')
                ->get()
                ->map(function ($item) use ($totalBookings) {
                    $percentage = $totalBookings > 0 ? ($item->count / $totalBookings) * 100 : 0;
                    $colors = [
                        'pending' => 'yellow',
                        'confirmed' => 'green',
                        'cancelled' => 'red',
                    ];

                    return [
                        'label' => ucfirst($item->booking_status),
                        'count' => $item->count,
                        'percentage' => round($percentage, 1),
                        'color' => $colors[$item->booking_status] ?? 'gray'
                    ];
                });

            // 5. Rute terpopuler - PERBAIKAN DI SINI: gunakan schedule_id, bukan bus_schedule_id
            $topRoutes = BusSchedule::with('bus')
                ->whereBetween('departure_time', [$start, $end])
                ->get()
                ->map(function ($schedule) use ($start, $end) {
                    $bookings = $schedule->bookings()
                        ->whereBetween('created_at', [$start, $end])
                        ->where('booking_status', 'confirmed')
                        ->get();

                    $bookingCount = $bookings->count();
                    $passengerCount = $bookings->sum('total_passengers');
                    $revenue = $bookings->where('payment_status', 'paid')->sum('total_price');

                    $totalSeats = $schedule->bus->total_seats ?? 1;
                    $occupancyRate = $totalSeats > 0 ? ($passengerCount / $totalSeats) * 100 : 0;

                    return [
                        'departure' => $schedule->departure_city,
                        'arrival' => $schedule->arrival_city,
                        'schedule_count' => 1,
                        'booking_count' => $bookingCount,
                        'passenger_count' => $passengerCount,
                        'revenue' => $revenue,
                        'occupancy_rate' => round($occupancyRate, 1),
                        'occupancy_color' => $occupancyRate >= 80 ? 'green' : ($occupancyRate >= 50 ? 'yellow' : 'red')
                    ];
                })
                ->filter(fn($route) => $route['booking_count'] > 0)
                ->sortByDesc('booking_count')
                ->take(5)
                ->values();

            // 6. Data untuk chart (contoh sederhana)
            $chartData = $this->generateChartData($start, $end);

            return view('admin.reports.index', [
                'reportData' => [
                    'total_bookings' => $totalBookings,
                    'total_revenue' => $totalRevenue,
                    'total_passengers' => $totalPassengers,
                    'avg_booking' => $totalBookings > 0 ? round($totalRevenue / $totalBookings) : 0,

                    'booking_change' => round($bookingChange, 1),
                    'revenue_change' => round($revenueChange, 1),
                    'passenger_change' => round($passengerChange, 1),

                    'status_distribution' => $statusDistribution,
                    'top_routes' => $topRoutes,
                    'chart_data' => $chartData,
                ]
            ]);

        } catch (\Exception $e) {
            // Log error dan tampilkan halaman error yang lebih baik
            Log::error('Report Error: ' . $e->getMessage());

            return view('admin.reports.index', [
                'reportData' => [
                    'total_bookings' => 0,
                    'total_revenue' => 0,
                    'total_passengers' => 0,
                    'avg_booking' => 0,
                    'booking_change' => 0,
                    'revenue_change' => 0,
                    'passenger_change' => 0,
                    'status_distribution' => [],
                    'top_routes' => [],
                    'chart_data' => [],
                ],
                'error' => 'Terjadi kesalahan saat memuat laporan: ' . $e->getMessage()
            ]);
        }
    }

    private function generateChartData($start, $end)
    {
        // Generate data chart sederhana
        $days = $start->diffInDays($end);
        $chartData = [];

        for ($i = 0; $i <= $days; $i++) {
            $date = $start->copy()->addDays($i);
            $revenue = Booking::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('total_price');

            $chartData[] = [
                'date' => $date->format('d M'),
                'revenue' => $revenue,
                'bookings' => Booking::whereDate('created_at', $date)->count(),
            ];
        }

        return $chartData;
    }
}
