<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketController extends Controller
{
    /**
     * Get user's tickets
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $tickets = Ticket::with(['booking.schedule', 'booking.schedule.bus'])
                ->whereHas('booking', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'ticket_code' => $ticket->ticket_code,
                        'booking_code' => $ticket->booking->booking_code,
                        'schedule' => [
                            'departure_city' => $ticket->booking->schedule->departure_city ?? 'Unknown',
                            'arrival_city' => $ticket->booking->schedule->arrival_city ?? 'Unknown',
                            'departure_time' => $ticket->booking->schedule->departure_time ?? now(),
                        ],
                        'bus' => [
                            'name' => $ticket->booking->schedule->bus->bus_name ?? 'Unknown',
                            'number' => $ticket->booking->schedule->bus->bus_number ?? 'Unknown',
                        ],
                        'status' => $ticket->status,
                        'boarding_status' => $ticket->boarding_status,
                        'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Tickets retrieved successfully',
                'data' => $tickets
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ticket details
     */
    public function show($id, Request $request)
    {
        try {
            $user = $request->user();

            $ticket = Ticket::with([
                'booking',
                'booking.user',
                'booking.schedule',
                'booking.schedule.bus',
                'booking.passengers'
            ])->find($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket not found'
                ], 404);
            }

            // Check if user owns this ticket
            if ($ticket->booking->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to view this ticket'
                ], 403);
            }

            $passenger = $ticket->booking->passengers->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket details retrieved successfully',
                'data' => [
                    'id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'booking_code' => $ticket->booking->booking_code,
                    'passenger' => [
                        'name' => $passenger->full_name ?? 'Unknown',
                        'seat_number' => $passenger->seat_number ?? 'Unknown',
                    ],
                    'schedule' => [
                        'origin' => $ticket->booking->schedule->departure_city,
                        'destination' => $ticket->booking->schedule->arrival_city,
                        'departure_time' => $ticket->booking->schedule->departure_time->format('Y-m-d H:i:s'),
                        'arrival_time' => $ticket->booking->schedule->arrival_time->format('Y-m-d H:i:s'),
                    ],
                    'bus' => [
                        'name' => $ticket->booking->schedule->bus->bus_name,
                        'number' => $ticket->booking->schedule->bus->bus_number,
                        'type' => $ticket->booking->schedule->bus->bus_type,
                    ],
                    'status' => $ticket->status,
                    'boarding_status' => $ticket->boarding_status,
                    'scanned_at' => $ticket->scanned_at?->format('Y-m-d H:i:s'),
                    'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                    'qr_data' => base64_encode(json_encode([
                        'ticket_code' => $ticket->ticket_code,
                        'booking_id' => $ticket->booking_id,
                        'timestamp' => now()->timestamp,
                    ])),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve ticket details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate QR code for ticket
     */
    public function getQR($id, Request $request)
    {
        try {
            $user = $request->user();
            $ticket = Ticket::with('booking')->find($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket not found'
                ], 404);
            }

            if ($ticket->booking->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Generate QR data
            $qrData = [
                'ticket_code' => $ticket->ticket_code,
                'booking_id' => $ticket->booking_id,
                'passenger_name' => $ticket->booking->passengers->first()->full_name ?? 'Unknown',
                'timestamp' => now()->timestamp,
            ];

            // Generate QR code
            $qrCode = QrCode::size(300)->generate(json_encode($qrData));

            return response()->json([
                'status' => 'success',
                'message' => 'QR code generated successfully',
                'data' => [
                    'ticket_code' => $ticket->ticket_code,
                    'qr_code' => 'data:image/svg+xml;base64,' . base64_encode($qrCode),
                    'qr_data' => base64_encode(json_encode($qrData)),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate QR code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate ticket (for conductor)
     */
    public function validateTicket($id, Request $request)
    {
        try {
            $ticket = Ticket::with(['booking.schedule', 'booking.passengers'])->find($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket not found'
                ], 404);
            }

            // Check if ticket is valid
            if ($ticket->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket is ' . $ticket->status,
                    'data' => [
                        'valid' => false,
                        'reason' => 'Ticket is ' . $ticket->status,
                    ]
                ]);
            }

            // Check if already scanned
            if ($ticket->scanned_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket already scanned at ' . $ticket->scanned_at->format('H:i:s'),
                    'data' => [
                        'valid' => false,
                        'reason' => 'Already scanned',
                        'scanned_at' => $ticket->scanned_at->format('Y-m-d H:i:s'),
                    ]
                ]);
            }

            // Check if schedule has departed
            if ($ticket->booking->schedule->departure_time < now()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bus has already departed',
                    'data' => [
                        'valid' => false,
                        'reason' => 'Schedule departed',
                    ]
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket is valid',
                'data' => [
                    'valid' => true,
                    'ticket' => [
                        'id' => $ticket->id,
                        'ticket_code' => $ticket->ticket_code,
                        'passenger_name' => $ticket->booking->passengers->first()->full_name ?? 'Unknown',
                        'seat_number' => $ticket->booking->passengers->first()->seat_number ?? 'Unknown',
                        'schedule' => [
                            'origin' => $ticket->booking->schedule->departure_city,
                            'destination' => $ticket->booking->schedule->arrival_city,
                            'departure_time' => $ticket->booking->schedule->departure_time->format('Y-m-d H:i:s'),
                        ],
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to validate ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
