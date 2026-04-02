<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Get available schedules
     */
    public function index(Request $request)
    {
        try {
            $query = BusSchedule::with(['bus', 'route'])
                ->where('status', 'active')
                ->where('departure_time', '>', now());

            // Filter by date
            if ($request->has('date')) {
                $query->whereDate('departure_time', $request->date);
            }

            // Filter by origin
            if ($request->has('origin')) {
                $query->where('departure_city', 'like', '%' . $request->origin . '%');
            }

            // Filter by destination
            if ($request->has('destination')) {
                $query->where('arrival_city', 'like', '%' . $request->destination . '%');
            }

            $schedules = $query->orderBy('departure_time', 'asc')
                ->paginate($request->per_page ?? 10);

            return response()->json([
                'status' => 'success',
                'message' => 'Schedules retrieved successfully',
                'data' => $schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'bus' => [
                            'name' => $schedule->bus->bus_name ?? 'Unknown',
                            'number' => $schedule->bus->bus_number ?? 'Unknown',
                            'type' => $schedule->bus->bus_type ?? 'Regular',
                        ],
                        'route' => [
                            'origin' => $schedule->departure_city,
                            'destination' => $schedule->arrival_city,
                        ],
                        'departure_time' => $schedule->departure_time,
                        'arrival_time' => $schedule->arrival_time,
                        'price' => (float) $schedule->price,
                        'available_seats' => $schedule->available_seats,
                        'status' => $schedule->status,
                    ];
                }),
                'meta' => [
                    'current_page' => $schedules->currentPage(),
                    'total_pages' => $schedules->lastPage(),
                    'total_items' => $schedules->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get schedule details
     */
    public function show($id)
    {
        try {
            $schedule = BusSchedule::with(['bus', 'route', 'bookings'])->find($id);

            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Schedule details retrieved successfully',
                'data' => [
                    'id' => $schedule->id,
                    'bus' => [
                        'id' => $schedule->bus->id,
                        'name' => $schedule->bus->bus_name,
                        'number' => $schedule->bus->bus_number,
                        'type' => $schedule->bus->bus_type,
                        'facilities' => $schedule->bus->facilities ?? [],
                    ],
                    'route' => [
                        'origin' => $schedule->departure_city,
                        'destination' => $schedule->arrival_city,
                        'distance' => $schedule->route->distance ?? 0,
                        'estimated_duration' => $schedule->route->duration ?? '0 hours',
                    ],
                    'departure_time' => $schedule->departure_time,
                    'arrival_time' => $schedule->arrival_time,
                    'price' => (float) $schedule->price,
                    'available_seats' => $schedule->available_seats,
                    'total_seats' => $schedule->bus->total_seats ?? 40,
                    'booked_seats' => $schedule->bookings->where('booking_status', 'confirmed')->count(),
                    'status' => $schedule->status,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve schedule details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check seat availability
     */
    public function checkAvailability($id, Request $request)
    {
        try {
            $request->validate([
                'seat_numbers' => 'required|array',
                'seat_numbers.*' => 'integer|min:1|max:40',
            ]);

            $schedule = BusSchedule::with(['bookings.passengers'])->find($id);

            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ], 404);
            }

            // Get booked seats
            $bookedSeats = [];
            foreach ($schedule->bookings->where('booking_status', 'confirmed') as $booking) {
                foreach ($booking->passengers as $passenger) {
                    $bookedSeats[] = (int) $passenger->seat_number;
                }
            }

            $requestedSeats = $request->seat_numbers;
            $availableSeats = [];
            $unavailableSeats = [];

            foreach ($requestedSeats as $seat) {
                if (in_array($seat, $bookedSeats)) {
                    $unavailableSeats[] = $seat;
                } else {
                    $availableSeats[] = $seat;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Availability checked successfully',
                'data' => [
                    'schedule_id' => $schedule->id,
                    'available_seats' => $availableSeats,
                    'unavailable_seats' => $unavailableSeats,
                    'total_available' => count($availableSeats),
                    'total_requested' => count($requestedSeats),
                    'is_available' => empty($unavailableSeats),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
