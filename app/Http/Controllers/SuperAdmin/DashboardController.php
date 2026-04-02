<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Bus;
use App\Models\BusSchedule;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display super admin dashboard.
     */
    public function index()
    {
        try {
            // 1. Total Users (menggunakan kolom role, bukan relasi)
            $totalUsers = User::count();
            $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();

            // 2. Today's bookings
            $today = Carbon::today();
            $todayBookings = Booking::whereDate('created_at', $today)->count();

            // 3. Today's revenue
            $todayRevenue = Booking::whereDate('created_at', $today)
                ->where('status', 'confirmed')
                ->sum('total_price');

            // 4. Recent users (last 5)
            $recentUsers = User::latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'role', 'created_at'])
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'role_name' => $user->role_name,
                        'created_at' => $user->created_at->format('Y-m-d H:i'),
                    ];
                });

            // 5. Recent bookings (last 5)
            $recentBookings = Booking::with('user:id,name')
                ->latest()
                ->take(5)
                ->get(['id', 'booking_code', 'user_id', 'total_price', 'status', 'payment_status', 'created_at'])
                ->map(function($booking) {
                    return [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'user_name' => $booking->user->name ?? 'N/A',
                        'total_price' => $booking->total_price,
                        'status' => $booking->status,
                        'payment_status' => $booking->payment_status,
                        'created_at' => $booking->created_at->format('Y-m-d H:i'),
                    ];
                });

            // 6. System health statistics
            $systemHealth = [
                'total_users' => $totalUsers,
                'total_admins' => $totalAdmins,
                'total_buses' => Bus::count(),
                'total_bookings' => Booking::count(),
                'active_schedules' => BusSchedule::where('departure_time', '>=', now())->count(),
                'month_revenue' => Booking::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'confirmed')
                    ->sum('total_price'),
                'month_bookings' => Booking::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];

            return view('superadmin.dashboard', compact(
                'totalUsers',
                'totalAdmins',
                'todayBookings',
                'todayRevenue',
                'recentUsers',
                'recentBookings',
                'systemHealth'
            ));

        } catch (\Exception $e) {
            // Log error
            Log::error('SuperAdmin Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback data jika terjadi error
            return view('superadmin.dashboard', [
                'totalUsers' => 0,
                'totalAdmins' => 0,
                'todayBookings' => 0,
                'todayRevenue' => 0,
                'recentUsers' => collect(),
                'recentBookings' => collect(),
                'systemHealth' => [
                    'total_users' => 0,
                    'total_admins' => 0,
                    'total_buses' => 0,
                    'total_bookings' => 0,
                    'active_schedules' => 0,
                    'month_revenue' => 0,
                    'month_bookings' => 0,
                ]
            ]);
        }
    }
}
