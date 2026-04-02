<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Initiate payment
     */
    public function initiate(Request $request)
    {
        try {
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'payment_method' => 'required|in:cash,transfer,qris',
                'amount' => 'required|numeric|min:0',
            ]);

            $user = $request->user();
            $booking = Booking::find($request->booking_id);

            // Check if user owns this booking
            if ($booking->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to pay for this booking'
                ], 403);
            }

            // Check if booking is pending
            if ($booking->booking_status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking cannot be paid'
                ], 400);
            }

            // Check if amount matches
            if ($request->amount < $booking->total_price) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Amount is less than required'
                ], 400);
            }

            // Create payment
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'payment_code' => 'PAY' . strtoupper(uniqid()),
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'payment_date' => now(),
            ]);

            // For demo purposes, we'll auto-confirm payment
            // In real scenario, you'd integrate with payment gateway
            $payment->update(['status' => 'paid']);
            $booking->update([
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
                'payment_date' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'payment_code' => $payment->payment_code,
                    'amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'booking_status' => $booking->booking_status,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment callback from gateway
     */
    public function callback(Request $request)
    {
        try {
            // This would handle callback from payment gateway
            // For now, we'll just return success
            return response()->json([
                'status' => 'success',
                'message' => 'Payment callback received'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process callback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function status($id, Request $request)
    {
        try {
            $payment = Payment::with('booking')->find($id);

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found'
                ], 404);
            }

            $user = $request->user();
            if ($payment->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to view this payment'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status retrieved',
                'data' => [
                    'payment_id' => $payment->id,
                    'payment_code' => $payment->payment_code,
                    'amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date?->format('Y-m-d H:i:s'),
                    'booking' => [
                        'id' => $payment->booking->id,
                        'booking_code' => $payment->booking->booking_code,
                        'status' => $payment->booking->booking_status,
                        'payment_status' => $payment->booking->payment_status,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment
     */
    public function verify($id, Request $request)
    {
        try {
            $payment = Payment::with('booking')->find($id);

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment not found'
                ], 404);
            }

            $user = $request->user();
            if ($payment->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Simulate verification
            $payment->update(['status' => 'verified']);
            $payment->booking->update(['payment_status' => 'verified']);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                    'verified_at' => now()->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
