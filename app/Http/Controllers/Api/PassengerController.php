<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\BusSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PassengerController extends Controller
{
    /**
     * Get passenger profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'avatar' => $user->avatar,
                        'is_active' => $user->is_active,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update passenger profile
     */
    public function update(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
                'avatar' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'avatar' => $request->avatar ?? $user->avatar,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'avatar' => $user->avatar,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking history
     */
    public function bookingHistory(Request $request)
    {
        try {
            $user = $request->user();

            $bookings = Booking::with(['schedule', 'schedule.bus', 'schedule.route', 'passengers', 'ticket'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'schedule' => [
                            'departure_city' => $booking->schedule->departure_city ?? 'Unknown',
                            'arrival_city' => $booking->schedule->arrival_city ?? 'Unknown',
                            'departure_time' => $booking->schedule->departure_time?->format('Y-m-d H:i:s'),
                            'arrival_time' => $booking->schedule->arrival_time?->format('Y-m-d H:i:s'),
                        ],
                        'bus' => [
                            'name' => $booking->schedule->bus->bus_name ?? 'Unknown',
                            'number' => $booking->schedule->bus->bus_number ?? 'Unknown',
                            'type' => $booking->schedule->bus->bus_type ?? 'Regular',
                        ],
                        'total_passengers' => $booking->total_passengers,
                        'total_price' => (float) $booking->total_price,
                        'formatted_total_price' => 'Rp ' . number_format($booking->total_price, 0, ',', '.'),
                        'booking_status' => $booking->booking_status,
                        'payment_status' => $booking->payment_status,
                        'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                        'has_ticket' => !is_null($booking->ticket),
                        'ticket_status' => $booking->ticket->status ?? null,
                        'passengers' => $booking->passengers->map(function ($passenger) {
                            return [
                                'full_name' => $passenger->full_name,
                                'seat_number' => $passenger->seat_number,
                            ];
                        }),
                    ];
                });

            // Separate into past and upcoming
            $pastBookings = $bookings->filter(function ($booking) {
                $departureTime = $booking['schedule']['departure_time'] ?? null;
                if (!$departureTime) return false;
                return strtotime($departureTime) < time();
            })->values();

            $upcomingBookings = $bookings->filter(function ($booking) {
                $departureTime = $booking['schedule']['departure_time'] ?? null;
                if (!$departureTime) return false;
                return strtotime($departureTime) >= time();
            })->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking history retrieved successfully',
                'data' => [
                    'past_bookings' => $pastBookings,
                    'upcoming_bookings' => $upcomingBookings,
                    'total_bookings' => $bookings->count(),
                    'total_past' => $pastBookings->count(),
                    'total_upcoming' => $upcomingBookings->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve booking history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming trips
     */
    public function upcomingTrips(Request $request)
    {
        try {
            $user = $request->user();

            $upcomingTrips = Booking::with(['schedule', 'schedule.bus', 'schedule.route', 'passengers', 'ticket'])
                ->where('user_id', $user->id)
                ->where('booking_status', 'confirmed')
                ->whereHas('schedule', function ($query) {
                    $query->where('departure_time', '>', now());
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'booking_code' => $booking->booking_code,
                        'schedule' => [
                            'departure_city' => $booking->schedule->departure_city,
                            'arrival_city' => $booking->schedule->arrival_city,
                            'departure_time' => $booking->schedule->departure_time->format('Y-m-d H:i:s'),
                            'arrival_time' => $booking->schedule->arrival_time->format('Y-m-d H:i:s'),
                            'departure_date' => $booking->schedule->departure_time->format('l, d F Y'),
                            'departure_time_formatted' => $booking->schedule->departure_time->format('H:i'),
                            'days_until_departure' => now()->diffInDays($booking->schedule->departure_time),
                        ],
                        'bus' => [
                            'name' => $booking->schedule->bus->bus_name,
                            'number' => $booking->schedule->bus->bus_number,
                            'type' => $booking->schedule->bus->bus_type,
                            'plate_number' => $booking->schedule->bus->plate_number,
                        ],
                        'total_passengers' => $booking->total_passengers,
                        'total_price' => (float) $booking->total_price,
                        'formatted_total_price' => 'Rp ' . number_format($booking->total_price, 0, ',', '.'),
                        'booking_status' => $booking->booking_status,
                        'payment_status' => $booking->payment_status,
                        'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                        'ticket' => $booking->ticket ? [
                            'ticket_code' => $booking->ticket->ticket_code,
                            'status' => $booking->ticket->status,
                            'boarding_status' => $booking->ticket->boarding_status,
                        ] : null,
                        'passengers' => $booking->passengers->map(function ($passenger) {
                            return [
                                'full_name' => $passenger->full_name,
                                'seat_number' => $passenger->seat_number,
                                'id_number' => $passenger->id_number,
                                'phone' => $passenger->phone,
                            ];
                        }),
                    ];
                });

            // Group by departure date
            $groupedTrips = $upcomingTrips->groupBy(function ($trip) {
                return \Carbon\Carbon::parse($trip['schedule']['departure_time'])->format('Y-m-d');
            })->map(function ($trips, $date) {
                return [
                    'date' => $date,
                    'date_formatted' => \Carbon\Carbon::parse($date)->format('l, d F Y'),
                    'trips' => $trips,
                    'total_trips' => $trips->count(),
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Upcoming trips retrieved successfully',
                'data' => [
                    'trips' => $upcomingTrips,
                    'grouped_trips' => $groupedTrips,
                    'total_trips' => $upcomingTrips->count(),
                    'nearest_trip' => $upcomingTrips->first(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve upcoming trips',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Delete old avatar if exists
            if ($user->avatar && Storage::exists('public/avatars/' . basename($user->avatar))) {
                Storage::delete('public/avatars/' . basename($user->avatar));
            }

            // Upload new avatar
            $path = $request->file('avatar')->store('public/avatars');
            $avatarUrl = Storage::url($path);

            $user->update(['avatar' => $avatarUrl]);

            return response()->json([
                'status' => 'success',
                'message' => 'Avatar uploaded successfully',
                'data' => [
                    'avatar_url' => $avatarUrl,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $avatarUrl,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for passenger
     */
    public function statistics(Request $request)
    {
        try {
            $user = $request->user();

            $totalBookings = Booking::where('user_id', $user->id)->count();
            $confirmedBookings = Booking::where('user_id', $user->id)
                ->where('booking_status', 'confirmed')
                ->count();
            $pendingBookings = Booking::where('user_id', $user->id)
                ->where('booking_status', 'pending')
                ->count();
            $totalSpent = Booking::where('user_id', $user->id)
                ->where('booking_status', 'confirmed')
                ->sum('total_price');

            $upcomingTripsCount = Booking::where('user_id', $user->id)
                ->where('booking_status', 'confirmed')
                ->whereHas('schedule', function ($query) {
                    $query->where('departure_time', '>', now());
                })
                ->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'total_bookings' => $totalBookings,
                    'confirmed_bookings' => $confirmedBookings,
                    'pending_bookings' => $pendingBookings,
                    'total_spent' => (float) $totalSpent,
                    'formatted_total_spent' => 'Rp ' . number_format($totalSpent, 0, ',', '.'),
                    'upcoming_trips' => $upcomingTripsCount,
                    'completion_rate' => $totalBookings > 0 ? round(($confirmedBookings / $totalBookings) * 100, 2) : 0,
                    'average_booking_value' => $totalBookings > 0 ? round($totalSpent / $totalBookings, 2) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get favorite routes
     */
    public function favoriteRoutes(Request $request)
    {
        try {
            $user = $request->user();

            $favoriteRoutes = Booking::with(['schedule.route'])
                ->where('user_id', $user->id)
                ->where('booking_status', 'confirmed')
                ->get()
                ->groupBy(function ($booking) {
                    return $booking->schedule->departure_city . '-' . $booking->schedule->arrival_city;
                })
                ->map(function ($bookings, $route) {
                    $firstBooking = $bookings->first();
                    return [
                        'route' => $route,
                        'origin' => $firstBooking->schedule->departure_city,
                        'destination' => $firstBooking->schedule->arrival_city,
                        'booking_count' => $bookings->count(),
                        'last_booking' => $bookings->max('created_at')->format('Y-m-d H:i:s'),
                        'average_price' => round($bookings->avg('total_price'), 2),
                    ];
                })
                ->sortByDesc('booking_count')
                ->values()
                ->take(5);

            return response()->json([
                'status' => 'success',
                'message' => 'Favorite routes retrieved successfully',
                'data' => $favoriteRoutes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve favorite routes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password is incorrect'
                ], 400);
            }

            // Check if user has active bookings
            $activeBookings = Booking::where('user_id', $user->id)
                ->where('booking_status', 'confirmed')
                ->whereHas('schedule', function ($query) {
                    $query->where('departure_time', '>', now());
                })
                ->exists();

            if ($activeBookings) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete account with active bookings'
                ], 400);
            }

            // Soft delete user (or mark as inactive)
            $user->update(['is_active' => false]);

            // Logout user
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Account deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete account',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
