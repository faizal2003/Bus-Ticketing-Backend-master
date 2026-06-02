<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private function setupMidtrans()
    {
        \Midtrans\Config::$serverKey = Setting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));
        \Midtrans\Config::$isProduction = Setting::get('midtrans_environment', env('MIDTRANS_ENVIRONMENT', 'sandbox')) === 'production';
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    /**
     * Initiate payment
     */
    public function initiate(Request $request)
    {
        try {
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'payment_method' => 'required|in:bank_transfer,cash,qris',
                'bank' => 'required_if:payment_method,bank_transfer|in:bca,bni,bri,mandiri,permata,cimb',
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

            $amount = $booking->total_price;
            $transaction_id = 'TRX-' . $booking->id . '-' . time();

            // Setup Midtrans
            $this->setupMidtrans();
            
            $midtransResponse = null;

            if ($request->payment_method === 'bank_transfer') {
                $params = [
                    'payment_type' => 'bank_transfer',
                    'transaction_details' => [
                        'order_id' => $transaction_id,
                        'gross_amount' => (int) $amount,
                    ],
                    'bank_transfer' => [
                        'bank' => $request->bank
                    ],
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone_number ?? ''
                    ]
                ];

                try {
                    $midtransResponse = \Midtrans\CoreApi::charge($params);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to connect to payment gateway: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Create payment
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $request->payment_method,
                'transaction_id' => $transaction_id,
                'amount' => $amount,
                'status' => 'pending',
                'midtrans_response' => $midtransResponse ? (array) $midtransResponse : null,
                'payment_date' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'midtrans' => $midtransResponse,
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
            $this->setupMidtrans();
            $notification = new \Midtrans\Notification();

            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $order_id = $notification->order_id;
            $fraud = $notification->fraud_status;

            $payment = Payment::where('transaction_id', $order_id)->first();
            if (!$payment) {
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $payment->update(['status' => 'pending']);
                    } else {
                        $payment->update(['status' => 'success', 'payment_date' => now()]);
                    }
                }
            } else if ($transaction == 'settlement') {
                $payment->update(['status' => 'success', 'payment_date' => now()]);
            } else if ($transaction == 'pending') {
                $payment->update(['status' => 'pending']);
            } else if ($transaction == 'deny') {
                $payment->update(['status' => 'failed']);
            } else if ($transaction == 'expire') {
                $payment->update(['status' => 'expired']);
            } else if ($transaction == 'cancel') {
                $payment->update(['status' => 'failed']);
            }

            // Update Booking status
            if ($payment->status === 'success') {
                $payment->booking->update([
                    'payment_status' => 'paid',
                    'booking_status' => 'confirmed'
                ]);
                $payment->booking->generateTicket();
            } else if (in_array($payment->status, ['failed', 'expired'])) {
                $payment->booking->update([
                    'payment_status' => 'failed',
                    'booking_status' => 'cancelled'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment callback processed'
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
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
            if ($payment->booking->user_id !== $user->id) {
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
                    'transaction_id' => $payment->transaction_id,
                    'amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date?->format('Y-m-d H:i:s'),
                    'midtrans' => $payment->midtrans_response,
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
            if ($payment->booking->user_id !== $user->id && $user->role !== 'admin' && $user->role !== 'super_admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Sync with Midtrans
            $this->setupMidtrans();
            
            try {
                $status = \Midtrans\Transaction::status($payment->transaction_id);
                $transactionStatus = $status->transaction_status ?? 'pending';

                if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                    $payment->update(['status' => 'success']);
                    $payment->booking->update(['payment_status' => 'paid', 'booking_status' => 'confirmed']);
                    $payment->booking->generateTicket();
                } else if ($transactionStatus == 'expire') {
                    $payment->update(['status' => 'expired']);
                    $payment->booking->update(['payment_status' => 'expired', 'booking_status' => 'cancelled']);
                } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny') {
                    $payment->update(['status' => 'failed']);
                    $payment->booking->update(['payment_status' => 'failed', 'booking_status' => 'cancelled']);
                }
            } catch (\Exception $e) {
                // If Midtrans cannot find the transaction, we just fall back
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
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
