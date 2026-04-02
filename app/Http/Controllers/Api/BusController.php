<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BusController extends Controller
{
    /**
     * Search buses with filters
     */
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'origin' => 'required|string',
                'destination' => 'required|string',
                'date' => 'required|date',
                'passengers' => 'nullable|integer|min:1|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Query untuk mencari schedule
            $query = BusSchedule::with(['bus', 'route'])
                ->whereHas('route', function ($q) use ($request) {
                    $q->where('origin_city', 'like', '%' . $request->origin . '%')
                      ->where('destination_city', 'like', '%' . $request->destination . '%');
                })
                ->whereDate('departure_time', $request->date)
                ->where('status', 'active')
                ->where('available_seats', '>=', $request->passengers ?? 1)
                ->where('departure_time', '>', now());

            // Apply sorting
            if ($request->has('sort_by')) {
                switch ($request->sort_by) {
                    case 'price_low':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price_high':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'departure_early':
                        $query->orderBy('departure_time', 'asc');
                        break;
                    case 'departure_late':
                        $query->orderBy('departure_time', 'desc');
                        break;
                    default:
                        $query->orderBy('departure_time', 'asc');
                }
            } else {
                $query->orderBy('departure_time', 'asc');
            }

            $schedules = $query->paginate($request->per_page ?? 10);

            return response()->json([
                'status' => 'success',
                'message' => 'Buses found successfully',
                'data' => $schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'bus_id' => $schedule->bus_id,
                        'bus' => [
                            'id' => $schedule->bus->id,
                            'name' => $schedule->bus->bus_name ?? 'Unknown',
                            'bus_number' => $schedule->bus->bus_number ?? 'Unknown',
                            'type' => $schedule->bus->bus_type ?? 'Regular',
                            'total_seats' => $schedule->bus->total_seats ?? 40,
                            'facilities' => $schedule->bus->facilities ?? [],
                        ],
                        'route' => [
                            'origin' => $schedule->route->origin_city ?? 'Unknown',
                            'destination' => $schedule->route->destination_city ?? 'Unknown',
                            'distance' => $schedule->route->distance ?? 0,
                            'duration' => $schedule->route->duration ?? '0 hours',
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
                    'per_page' => $schedules->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search buses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bus details
     */
    public function show(Bus $bus)
    {
        try {
            $bus->load(['schedules' => function ($query) {
                $query->where('departure_time', '>', now())
                      ->where('status', 'active')
                      ->orderBy('departure_time', 'asc');
            }]);

            return response()->json([
                'status' => 'success',
                'message' => 'Bus details retrieved successfully',
                'data' => [
                    'bus' => [
                        'id' => $bus->id,
                        'name' => $bus->bus_name,
                        'bus_number' => $bus->bus_number,
                        'type' => $bus->bus_type,
                        'total_seats' => $bus->total_seats,
                        'facilities' => $bus->facilities ?? [],
                        'driver_name' => $bus->driver_name,
                        'driver_phone' => $bus->driver_phone,
                        'plate_number' => $bus->plate_number,
                        'schedules' => $bus->schedules->map(function ($schedule) {
                            return [
                                'id' => $schedule->id,
                                'departure_time' => $schedule->departure_time,
                                'arrival_time' => $schedule->arrival_time,
                                'price' => (float) $schedule->price,
                                'available_seats' => $schedule->available_seats,
                                'route' => [
                                    'origin' => $schedule->route->origin_city ?? 'Unknown',
                                    'destination' => $schedule->route->destination_city ?? 'Unknown',
                                ],
                            ];
                        }),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve bus details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get seat layout for a specific bus and schedule
     */
    public function getSeatLayout(Bus $bus, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'schedule_id' => 'required|exists:bus_schedules,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $schedule = BusSchedule::with(['bookings.passengers'])->find($request->schedule_id);

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

            // Generate seat layout
            $totalSeats = $bus->total_seats ?? 40;
            $seatsPerRow = 4; // 2-2 configuration
            $totalRows = ceil($totalSeats / $seatsPerRow);

            $seatLayout = [];
            $seatNumber = 1;

            for ($row = 1; $row <= $totalRows; $row++) {
                $rowSeats = [];
                for ($col = 1; $col <= $seatsPerRow; $col++) {
                    if ($seatNumber <= $totalSeats) {
                        $rowSeats[] = [
                            'number' => $seatNumber,
                            'status' => in_array($seatNumber, $bookedSeats) ? 'booked' : 'available',
                            'type' => 'regular',
                        ];
                        $seatNumber++;
                    }
                }
                $seatLayout[] = $rowSeats;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Seat layout retrieved successfully',
                'data' => [
                    'bus' => [
                        'id' => $bus->id,
                        'name' => $bus->bus_name,
                        'total_seats' => $bus->total_seats,
                    ],
                    'schedule' => [
                        'id' => $schedule->id,
                        'departure_time' => $schedule->departure_time,
                        'price' => (float) $schedule->price,
                    ],
                    'seat_layout' => $seatLayout,
                    'booked_seats' => $bookedSeats,
                    'available_seats' => $totalSeats - count($bookedSeats),
                    'legend' => [
                        'available' => 'Available for booking',
                        'booked' => 'Already booked',
                        'selected' => 'Selected by you',
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load seat layout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular routes
     */
    public function popularRoutes(Request $request)
    {
        try {
            $popularRoutes = DB::table('routes')
                ->join('bus_schedules', 'routes.id', '=', 'bus_schedules.route_id')
                ->join('bookings', 'bus_schedules.id', '=', 'bookings.schedule_id')
                ->select(
                    'routes.origin_city',
                    'routes.destination_city',
                    DB::raw('COUNT(bookings.id) as booking_count'),
                    DB::raw('AVG(bus_schedules.price) as avg_price')
                )
                ->where('bookings.booking_status', 'confirmed')
                ->where('bookings.created_at', '>=', now()->subDays(30))
                ->groupBy('routes.origin_city', 'routes.destination_city')
                ->orderBy('booking_count', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Popular routes retrieved successfully',
                'data' => $popularRoutes->map(function ($route) {
                    return [
                        'origin' => $route->origin_city,
                        'destination' => $route->destination_city,
                        'booking_count' => $route->booking_count,
                        'avg_price' => (int) $route->avg_price,
                        'formatted_price' => 'Rp ' . number_format($route->avg_price, 0, ',', '.'),
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve popular routes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
