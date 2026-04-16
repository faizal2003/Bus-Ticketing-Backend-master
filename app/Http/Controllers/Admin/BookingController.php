<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'busSchedule.bus']);

        // Apply filters
        if ($request->filled('booking_code')) {
            $query->where('booking_code', 'like', '%' . $request->booking_code . '%');
        }

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate stats
        $totalBookings = Booking::count();
        $confirmedBookings = Booking::where('booking_status', 'confirmed')->count();
        $pendingBookings = Booking::where('booking_status', 'pending')->count();
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_price');

        return view('admin.bookings.index', compact(
            'bookings',
            'totalBookings',
            'confirmedBookings',
            'pendingBookings',
            'totalRevenue'
        ));
    }

    public function show($id)
    {
        $booking = Booking::with([
            'user',
            'busSchedule.bus',
            'passengers',
            'payment',
            'ticket'
        ])->findOrFail($id);

        // Format data for view
        $formattedBooking = [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'passenger_name' => $booking->user->name,
            'passenger_email' => $booking->user->email,
            'passenger_phone' => $booking->user->phone,
            'departure_city' => $booking->busSchedule->departure_city,
            'arrival_city' => $booking->busSchedule->arrival_city,
            'departure_time' => Carbon::parse($booking->busSchedule->departure_time)->format('d M Y, H:i'),
            'arrival_time' => Carbon::parse($booking->busSchedule->arrival_time)->format('d M Y, H:i'),
            'bus_name' => $booking->busSchedule->bus->bus_name ?? 'Bus tidak ditemukan',
            'bus_number' => $booking->busSchedule->bus->bus_number ?? '-',
            'seat_count' => $booking->total_passengers,
            'seats' => $booking->passengers->pluck('seat_number')->toArray(),
            'total_price' => $booking->total_price,
            'status' => $booking->booking_status,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment->payment_method ?? null,
            'payment_date' => $booking->payment->payment_date
                ? Carbon::parse($booking->payment->payment_date)->format('d M Y, H:i')
                : null,
            'ticket_code' => $booking->ticket->ticket_code ?? null,
            'ticket_status' => $booking->ticket->status ?? null,
            'boarding_status' => $booking->ticket->boarding_status ?? null,
            'created_at' => Carbon::parse($booking->created_at)->format('d M Y, H:i'),
            'notes' => $booking->notes,
            'original' => $booking // Keep original object for actions
        ];

        return view('admin.bookings.show', compact('formattedBooking'));
    }

    public function confirm($id)
    {
        $booking = Booking::findOrFail($id);

        try {
            $booking->update([
                'booking_status' => 'confirmed',
                'payment_status' => 'paid'
            ]);

            // Generate ticket if not exists
            if (!$booking->ticket) {
                $this->generateTicket($booking);
            }

            return redirect()->route('admin.bookings.show', $id)
                ->with('success', 'Pemesanan berhasil dikonfirmasi');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengonfirmasi pemesanan: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);

        try {
            $booking->update([
                'booking_status' => 'cancelled',
                'payment_status' => 'refunded'
            ]);

            // Update available seats
            $booking->busSchedule->updateAvailableSeats();

            return redirect()->route('admin.bookings.show', $id)
                ->with('success', 'Pemesanan berhasil dibatalkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan pemesanan: ' . $e->getMessage());
        }
    }

    private function generateTicket($booking)
    {
        // Generate ticket code
        $ticketCode = 'TKT-' . date('Ymd') . '-' . strtoupper(uniqid());

        // Generate QR Code data
        $qrData = json_encode([
            'booking_code' => $booking->booking_code,
            'ticket_code' => $ticketCode,
            'schedule_id' => $booking->busSchedule_id,
            'timestamp' => now()->timestamp
        ]);

        // Create ticket
        $ticket = $booking->ticket()->create([
            'ticket_code' => $ticketCode,
            'qr_code' => $qrData,
            'status' => 'active',
            'boarding_status' => 'pending'
        ]);

        return $ticket;
    }
}
