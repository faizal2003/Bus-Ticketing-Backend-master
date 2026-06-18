<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    /**
     * Get user's refund requests
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $refunds = Refund::with(['booking.schedule'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($refund) {
                    return [
                        'id' => $refund->id,
                        'booking_code' => $refund->booking->booking_code ?? '-',
                        'amount' => (float) $refund->amount,
                        'formatted_amount' => 'Rp ' . number_format($refund->amount, 0, ',', '.'),
                        'reason' => $refund->reason,
                        'status' => $refund->status,
                        'status_label' => $refund->status_label,
                        'admin_notes' => $refund->admin_notes,
                        'processed_at' => $refund->processed_at?->format('Y-m-d H:i:s'),
                        'created_at' => $refund->created_at->format('Y-m-d H:i:s'),
                        'schedule' => [
                            'departure_city' => $refund->booking->schedule->departure_city ?? '-',
                            'arrival_city' => $refund->booking->schedule->arrival_city ?? '-',
                            'departure_time' => optional($refund->booking->schedule)->departure_time?->format('Y-m-d H:i:s'),
                        ],
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $refunds
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve refunds',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific refund details
     */
    public function show($id, Request $request)
    {
        try {
            $refund = Refund::with(['booking.schedule.bus', 'booking.passengers'])
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$refund) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refund not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $refund->id,
                    'booking_code' => $refund->booking->booking_code ?? '-',
                    'amount' => (float) $refund->amount,
                    'formatted_amount' => 'Rp ' . number_format($refund->amount, 0, ',', '.'),
                    'reason' => $refund->reason,
                    'status' => $refund->status,
                    'status_label' => $refund->status_label,
                    'admin_notes' => $refund->admin_notes,
                    'processed_at' => $refund->processed_at?->format('Y-m-d H:i:s'),
                    'created_at' => $refund->created_at->format('Y-m-d H:i:s'),
                    'booking' => [
                        'booking_code' => $refund->booking->booking_code,
                        'total_passengers' => $refund->booking->total_passengers,
                        'schedule' => [
                            'departure_city' => $refund->booking->schedule->departure_city ?? '-',
                            'arrival_city' => $refund->booking->schedule->arrival_city ?? '-',
                            'departure_time' => optional($refund->booking->schedule)->departure_time?->format('Y-m-d H:i:s'),
                            'bus_name' => $refund->booking->schedule->bus->bus_name ?? '-',
                        ],
                        'passengers' => $refund->booking->passengers->map(function($p) {
                            return [
                                'name' => $p->full_name,
                                'seat_number' => $p->seat_number,
                            ];
                        }),
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve refund details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
