<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\ConductorLog;
use App\Models\BusSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConductorController extends Controller
{
    /**
     * Scan and validate ticket
     */
    public function scanTicket(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'qr_data' => 'nullable|string',
                'ticket_code' => 'nullable|string',
                'action' => 'nullable|in:scan,board',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ticketCode = null;

            if ($request->has('qr_data')) {
                // Decode QR data
                $qrData = json_decode(base64_decode($request->qr_data), true);
                if (!$qrData || !isset($qrData['ticket_code'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid QR code data'
                    ], 400);
                }
                $ticketCode = $qrData['ticket_code'];
            } elseif ($request->has('ticket_code')) {
                $ticketCode = $request->ticket_code;
            }

            if (!$ticketCode) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket code or QR data is required'
                ], 400);
            }

            $ticket = Ticket::with(['booking.user', 'booking.schedule', 'booking.schedule.bus', 'booking.passengers'])
                ->where('ticket_code', $ticketCode)
                ->first();

            if (!$ticket) {
                // Log failed scan
                ConductorLog::create([
                    'conductor_id' => $request->user()->id,
                    'action' => 'scan_ticket',
                    'details' => [
                        'ticket_code' => $ticketCode,
                        'result' => 'not_found',
                        'scan_time' => now()->toDateTimeString(),
                    ],
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket not found'
                ], 404);
            }

            // Check if ticket is valid
            if ($ticket->status !== 'active') {
                // Log invalid ticket scan
                ConductorLog::create([
                    'conductor_id' => $request->user()->id,
                    'ticket_id' => $ticket->id,
                    'action' => 'scan_ticket',
                    'details' => [
                        'result' => 'invalid',
                        'current_status' => $ticket->status,
                        'scan_time' => now()->toDateTimeString(),
                    ],
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket is ' . $ticket->status,
                    'data' => [
                        'ticket_code' => $ticket->ticket_code,
                        'status' => $ticket->status,
                        'boarding_status' => $ticket->boarding_status,
                    ]
                ], 400);
            }

            // Check if ticket is already scanned
            if ($ticket->scanned_at && !$request->action === 'board') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket already scanned at ' . $ticket->scanned_at->format('H:i:s'),
                    'data' => [
                        'ticket_code' => $ticket->ticket_code,
                        'scanned_at' => $ticket->scanned_at->format('Y-m-d H:i:s'),
                        'scanned_by' => $ticket->scannedBy->name ?? 'Unknown',
                    ]
                ], 400);
            }

            // Check if bus has departed
            if ($ticket->booking->schedule->departure_time < now()->subMinutes(30)) { // Allow 30 mins after departure
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bus has already departed',
                    'data' => [
                        'ticket_code' => $ticket->ticket_code,
                        'departure_time' => $ticket->booking->schedule->departure_time->format('Y-m-d H:i:s'),
                    ]
                ], 400);
            }

            // Process based on action
            $action = $request->action ?? 'scan';

            if ($action === 'board') {
                // Mark as boarded
                $ticket->update([
                    'scanned_at' => now(),
                    'scanned_by' => $request->user()->id,
                    'boarding_status' => 'boarded'
                ]);

                // Log boarding
                ConductorLog::create([
                    'conductor_id' => $request->user()->id,
                    'ticket_id' => $ticket->id,
                    'action' => 'board_passenger',
                    'details' => [
                        'seat_number' => $ticket->booking->passengers->first()->seat_number ?? 'Unknown',
                        'board_time' => now()->toDateTimeString(),
                    ],
                ]);

                $message = 'Passenger boarded successfully';
            } else {
                // Just scan (validate)
                $ticket->update(['scanned_at' => now()]);

                // Log scan
                ConductorLog::create([
                    'conductor_id' => $request->user()->id,
                    'ticket_id' => $ticket->id,
                    'action' => 'scan_ticket',
                    'details' => [
                        'result' => 'valid',
                        'scan_time' => now()->toDateTimeString(),
                    ],
                ]);

                $message = 'Ticket validated successfully';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'ticket_id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'status' => $ticket->status,
                    'boarding_status' => $ticket->boarding_status,
                    'scanned_at' => $ticket->scanned_at?->format('Y-m-d H:i:s'),
                    'passenger' => [
                        'name' => $ticket->booking->passengers->first()->full_name ?? 'Unknown',
                        'seat_number' => $ticket->booking->passengers->first()->seat_number ?? 'Unknown',
                    ],
                    'schedule' => [
                        'departure_city' => $ticket->booking->schedule->departure_city,
                        'arrival_city' => $ticket->booking->schedule->arrival_city,
                        'departure_time' => $ticket->booking->schedule->departure_time->format('Y-m-d H:i:s'),
                        'bus_name' => $ticket->booking->schedule->bus->bus_name,
                        'bus_number' => $ticket->booking->schedule->bus->bus_number,
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to scan ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get passenger list for a schedule
     */
    public function passengerList($scheduleId)
    {
        try {
            $schedule = BusSchedule::with(['bus', 'bookings' => function($query) {
                $query->where('booking_status', 'confirmed');
            }, 'bookings.passengers', 'bookings.ticket'])->find($scheduleId);

            if (!$schedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ], 404);
            }

            $passengers = [];
            foreach ($schedule->bookings as $booking) {
                foreach ($booking->passengers as $passenger) {
                    $passengers[] = [
                        'id' => $passenger->id,
                        'ticket_id' => $booking->ticket->id ?? null,
                        'ticket_code' => $booking->ticket->ticket_code ?? null,
                        'name' => $passenger->full_name,
                        'seat_number' => $passenger->seat_number,
                        'boarding_status' => $booking->ticket->boarding_status ?? 'pending',
                        'phone' => $passenger->phone,
                    ];
                }
            }

            // Sort by seat number
            usort($passengers, function($a, $b) {
                return (int)$a['seat_number'] <=> (int)$b['seat_number'];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Passenger list retrieved successfully',
                'data' => [
                    'schedule_id' => $schedule->id,
                    'bus_name' => $schedule->bus->bus_name,
                    'departure_time' => $schedule->departure_time->format('Y-m-d H:i:s'),
                    'total_passengers' => count($passengers),
                    'passengers' => $passengers
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve passenger list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's schedule for conductor
     */
    public function todaySchedule(Request $request)
    {
        try {
            $user = $request->user();

            // Get schedules for today
            $schedules = BusSchedule::with(['bus', 'bookings.ticket'])
                ->whereDate('departure_time', today())
                ->orderBy('departure_time', 'asc')
                ->get()
                ->map(function ($schedule) {
                    $bookedCount = $schedule->bookings()
                        ->where('booking_status', 'confirmed')
                        ->count();

                    $boardedCount = $schedule->bookings()
                        ->where('booking_status', 'confirmed')
                        ->whereHas('ticket', function($query) {
                            $query->where('boarding_status', 'boarded');
                        })
                        ->count();

                    return [
                        'id' => $schedule->id,
                        'bus' => [
                            'name' => $schedule->bus->bus_name,
                            'number' => $schedule->bus->bus_number,
                            'plate_number' => $schedule->bus->plate_number,
                        ],
                        'departure_city' => $schedule->departure_city,
                        'arrival_city' => $schedule->arrival_city,
                        'departure_time' => $schedule->departure_time->format('Y-m-d H:i:s'),
                        'arrival_time' => $schedule->arrival_time->format('Y-m-d H:i:s'),
                        'total_passengers' => $bookedCount,
                        'boarded_passengers' => $boardedCount,
                        'status' => $schedule->status,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Today\'s schedule retrieved successfully',
                'data' => $schedules
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update ticket boarding status
     */
    public function updateTicketStatus($ticketId, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,boarded,missed',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ticket = Ticket::with('booking.passengers')->find($ticketId);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket not found'
                ], 404);
            }

            $oldStatus = $ticket->boarding_status;
            $ticket->update(['boarding_status' => $request->status]);

            // Log status change
            ConductorLog::create([
                'conductor_id' => $request->user()->id,
                'ticket_id' => $ticket->id,
                'action' => 'update_status',
                'details' => [
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'notes' => $request->notes,
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket status updated successfully',
                'data' => [
                    'ticket_id' => $ticket->id,
                    'ticket_code' => $ticket->ticket_code,
                    'boarding_status' => $ticket->boarding_status,
                    'passenger_name' => $ticket->booking->passengers->first()->full_name ?? 'Unknown',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update ticket status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report departure
     */
    public function reportDeparture(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'schedule_id' => 'required|exists:bus_schedules,id',
                'actual_time' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Log departure
            ConductorLog::logDeparture(
                $request->user()->id,
                $request->schedule_id,
                $request->actual_time
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Departure reported successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to report departure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report arrival
     */
    public function reportArrival(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'schedule_id' => 'required|exists:bus_schedules,id',
                'actual_time' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Log arrival
            ConductorLog::logArrival(
                $request->user()->id,
                $request->schedule_id,
                $request->actual_time
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Arrival reported successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to report arrival',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conductor logs
     */
    public function getLogs(Request $request)
    {
        try {
            $user = $request->user();

            $logs = ConductorLog::with(['ticket', 'ticket.booking'])
                ->where('conductor_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'action_name' => $log->action_name,
                        'ticket_code' => $log->ticket->ticket_code ?? null,
                        'passenger_name' => $log->ticket?->booking?->passengers?->first()?->full_name ?? null,
                        'details' => $log->formatted_details,
                        'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Logs retrieved successfully',
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
