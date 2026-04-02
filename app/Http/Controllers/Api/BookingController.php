<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusSchedule;
use App\Models\BusSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Create a new booking
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'schedule_id' => 'required|exists:bus_schedules,id',
                'passengers' => 'required|array|min:1',
                'passengers.*.full_name' => 'required|string|max:255',
                'passengers.*.id_number' => 'nullable|string|max:50',
                'passengers.*.phone' => 'nullable|string|max:20',
                'passengers.*.seat_number' => 'required|string|max:10',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get schedule
            $schedule = BusSchedule::with('bus')->find($request->schedule_id);

            // Check if schedule is available
            if (!$schedule || $schedule->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Schedule not available'
                ], 400);
            }

            // Check if schedule has departed
            if ($schedule->departure_time < now()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Schedule has already departed'
                ], 400);
            }

            // Check available seats
            $passengerCount = count($request->passengers);
            if ($schedule->available_seats < $passengerCount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not enough available seats'
                ], 400);
            }

            // Check seat availability and duplicates
            $seatNumbers = [];
            foreach ($request->passengers as $passenger) {
                $seatNumber = $passenger['seat_number'];

                // Check for duplicate seat selection
                if (in_array($seatNumber, $seatNumbers)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Duplicate seat selection: ' . $seatNumber
                    ], 400);
                }
                $seatNumbers[] = $seatNumber;

                // Check if seat exists and is available
                $seat = BusSeat::where('bus_id', $schedule->bus_id)
                    ->where('seat_number', $seatNumber)
                    ->where('is_available', true)
                    ->first();

                if (!$seat) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Seat not available: ' . $seatNumber
                    ], 400);
                }
            }

            // Calculate total price
            $totalPrice = $schedule->price_per_seat * $passengerCount;

            // Create booking
            $booking = Booking::create([
                'booking_code' => Booking::generateBookingCode(),
                'user_id' => $request->user()->id,
                'schedule_id' => $schedule->id,
                'total_passengers' => $passengerCount,
                'total_price' => $totalPrice,
                'booking_status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Add passengers
            foreach ($request->passengers as $passengerData) {
                $booking->addPassenger($passengerData);

                // Mark seat as temporarily unavailable
                BusSeat::where('bus_id', $schedule->bus_id)
                    ->where('seat_number', $passengerData['seat_number'])
                    ->update(['is_available' => false]);
            }

            // Update schedule available seats
            $schedule->updateAvailableSeats();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'total_passengers' => $booking->total_passengers,
                    'total_price' => (float) $booking->total_price,
                    'formatted_total_price' => 'Rp ' . number_format($booking->total_price, 0, ',', '.'),
                    'booking_status' => $booking->booking_status,
                    'payment_status' => $booking->payment_status,
                    'schedule' => [
                        'departure_city' => $schedule->departure_city,
                        'arrival_city' => $schedule->arrival_city,
                        'departure_time' => $schedule->departure_time->format('Y-m-d H:i:s'),
                    ],
                    'passengers' => $booking->passengers->map(function ($passenger) {
                        return [
                            'full_name' => $passenger->full_name,
                            'seat_number' => $passenger->seat_number,
                        ];
                    }),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's bookings
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $bookings = Booking::with(['schedule', 'schedule.bus', 'passengers', 'payment', 'ticket'])
                ->where('user_id', $user->id)
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
                            'bus_name' => $booking->schedule->bus->bus_name,
                        ],
                        'total_passengers' => $booking->total_passengers,
                        'total_price' => (float) $booking->total_price,
                        'formatted_total_price' => 'Rp ' . number_format($booking->total_price, 0, ',', '.'),
                        'booking_status' => $booking->booking_status,
                        'payment_status' => $booking->payment_status,
                        'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                        'has_ticket' => !is_null($booking->ticket),
                        'ticket_status' => $booking->ticket->status ?? null,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking details
     */
    public function show($id, Request $request)
    {
        try {
            $booking = Booking::with(['schedule', 'schedule.bus', 'passengers', 'payment', 'ticket'])
                ->where('user_id', $request->user()->id)
                ->find($id);

            if (!$booking) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Booking details retrieved successfully',
                'data' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'schedule' => [
                        'id' => $booking->schedule->id,
                        'departure_city' => $booking->schedule->departure_city,
                        'arrival_city' => $booking->schedule->arrival_city,
                        'departure_time' => $booking->schedule->departure_time->format('Y-m-d H:i:s'),
                        'arrival_time' => $booking->schedule->arrival_time->format('Y-m-d H:i:s'),
                        'bus' => [
                            'name' => $booking->schedule->bus->bus_name,
                            'number' => $booking->schedule->bus->bus_number,
                            'type' => $booking->schedule->bus->bus_type,
                        ],
                    ],
                    'total_passengers' => $booking->total_passengers,
                    'total_price' => (float) $booking->total_price,
                    'formatted_total_price' => 'Rp ' . number_format($booking->total_price, 0, ',', '.'),
                    'booking_status' => $booking->booking_status,
                    'payment_status' => $booking->payment_status,
                    'notes' => $booking->notes,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                    'passengers' => $booking->passengers->map(function ($passenger) {
                        return [
                            'full_name' => $passenger->full_name,
                            'id_number' => $passenger->id_number,
                            'phone' => $passenger->phone,
                            'seat_number' => $passenger->seat_number,
                        ];
                    }),
                    'payment' => $booking->payment ? [
                        'status' => $booking->payment->status,
                        'amount' => (float) $booking->payment->amount,
                        'payment_method' => $booking->payment->payment_method,
                        'payment_date' => $booking->payment->payment_date?->format('Y-m-d H:i:s'),
                    ] : null,
                    'ticket' => $booking->ticket ? [
                        'ticket_code' => $booking->ticket->ticket_code,
                        'status' => $booking->ticket->status,
                        'boarding_status' => $booking->ticket->boarding_status,
                    ] : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve booking details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancel($id, Request $request)
    {
        try {
            $booking = Booking::with(['schedule', 'passengers'])
                ->where('user_id', $request->user()->id)
                ->where('booking_status', 'pending')
                ->find($id);

            if (!$booking) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking not found or cannot be cancelled'
                ], 404);
            }

            // Mark seats as available again
            foreach ($booking->passengers as $passenger) {
                BusSeat::where('bus_id', $booking->schedule->bus_id)
                    ->where('seat_number', $passenger->seat_number)
                    ->update(['is_available' => true]);
            }

            // Update booking status
            $booking->markAsCancelled();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking cancelled successfully',
                'data' => [
                    'booking_id' => $booking->id,
                    'booking_status' => $booking->booking_status,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
