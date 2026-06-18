<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'schedule.bus']);

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

    public function create()
    {
        $schedules = \App\Models\BusSchedule::with('bus')
            ->where('status', 'active')
            ->where('departure_time', '>=', now())
            ->orderBy('departure_time', 'asc')
            ->get();

        return view('admin.bookings.create', compact('schedules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:bus_schedules,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'id_number' => 'nullable|string|max:50',
            'total_passengers' => 'required|integer|min:1|max:10',
            'seats' => 'required|array|min:1',
            'seats.*' => 'required|string|max:10',
            'notes' => 'nullable|string',
            'payment_method' => 'required|in:cash,bank_transfer',
            'auto_confirm' => 'nullable|boolean',
        ]);

        try {
            // Get schedule
            $schedule = \App\Models\BusSchedule::with('bus')->findOrFail($validated['schedule_id']);

            // Check if schedule is available
            if ($schedule->status !== 'active') {
                return redirect()->back()->withInput()->with('error', 'Jadwal tidak tersedia');
            }

            // Check available seats
            if ($schedule->available_seats < $validated['total_passengers']) {
                return redirect()->back()->withInput()->with('error', 'Kursi tidak cukup tersedia');
            }

            // Check seat availability
            $seatNumbers = $validated['seats'];
            $bookedSeats = \App\Models\BookingPassenger::whereHas('booking', function($query) use ($schedule) {
                $query->where('schedule_id', $schedule->id)
                      ->whereIn('booking_status', ['confirmed', 'pending']);
            })->pluck('seat_number')->toArray();

            foreach ($seatNumbers as $seatNumber) {
                if (in_array($seatNumber, $bookedSeats)) {
                    return redirect()->back()->withInput()->with('error', 'Kursi ' . $seatNumber . ' sudah dipesan');
                }
            }

            // Find or create user for this customer
            $user = \App\Models\User::where('email', $validated['customer_email'] ?? $validated['customer_phone'] . '@manual.booking')->first();
            
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'] ?? $validated['customer_phone'] . '@manual.booking',
                    'phone_number' => $validated['customer_phone'],
                    'role' => 'penumpang',
                    'password' => bcrypt(uniqid()), // Random password for manual bookings
                ]);
            }

            // Calculate total price
            $ticketTotal = $schedule->price_per_seat * $validated['total_passengers'];
            $tax = $ticketTotal * 0.1;
            $serviceFee = 5000;
            $totalPrice = $ticketTotal + $tax + $serviceFee;

            // Create booking
            $booking = Booking::create([
                'booking_code' => Booking::generateBookingCode(),
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'total_passengers' => $validated['total_passengers'],
                'total_price' => $totalPrice,
                'booking_status' => $request->auto_confirm ? 'confirmed' : 'pending',
                'payment_status' => $request->auto_confirm ? 'paid' : 'pending',
                'notes' => $validated['notes'],
            ]);

            // Add passengers
            foreach ($seatNumbers as $index => $seatNumber) {
                $booking->addPassenger([
                    'full_name' => $validated['customer_name'] . ($index > 0 ? ' (Penumpang ' . ($index + 1) . ')' : ''),
                    'id_number' => $validated['id_number'] ?? null,
                    'phone' => $validated['customer_phone'],
                    'seat_number' => $seatNumber,
                ]);
            }

            // Create payment record
            Payment::create([
                'booking_id' => $booking->id,
                'transaction_id' => 'MANUAL-' . strtoupper(uniqid()),
                'amount' => $totalPrice,
                'payment_method' => $validated['payment_method'],
                'status' => $request->auto_confirm ? 'success' : 'pending',
                'payment_date' => $request->auto_confirm ? now() : null,
            ]);

            // Generate ticket if auto-confirm
            if ($request->auto_confirm) {
                $this->generateTicket($booking);
            }

            // Update schedule available seats
            $schedule->updateAvailableSeats();

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Pemesanan manual berhasil dibuat');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat pemesanan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $booking = Booking::with([
            'user',
            'schedule.bus',
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
            'departure_city' => $booking->schedule->departure_city,
            'arrival_city' => $booking->schedule->arrival_city,
            'departure_time' => Carbon::parse($booking->schedule->departure_time)->format('d M Y, H:i'),
            'arrival_time' => Carbon::parse($booking->schedule->arrival_time)->format('d M Y, H:i'),
            'bus_name' => $booking->schedule->bus->bus_name ?? 'Bus tidak ditemukan',
            'bus_number' => $booking->schedule->bus->bus_number ?? '-',
            'seat_count' => $booking->total_passengers,
            'seats' => $booking->passengers->pluck('seat_number')->toArray(),
            'total_price' => $booking->total_price,
            'status' => $booking->booking_status,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment?->payment_method,
            'payment_date' => $booking->payment?->payment_date
                ? Carbon::parse($booking->payment->payment_date)->format('d M Y, H:i')
                : null,
            'ticket_code' => $booking->ticket?->ticket_code,
            'ticket_status' => $booking->ticket?->status,
            'boarding_status' => $booking->ticket?->boarding_status,
            'created_at' => Carbon::parse($booking->created_at)->format('d M Y, H:i'),
            'notes' => $booking->notes,
            'original' => $booking // Keep original object for actions
        ];

        return view('admin.bookings.show', compact('formattedBooking'));
    }

    public function print($id)
    {
        $booking = Booking::with([
            'user',
            'schedule.bus',
            'passengers',
            'payment',
            'ticket'
        ])->findOrFail($id);

        if (!$booking->ticket) {
            return redirect()->back()->with('error', 'Tiket belum di-generate untuk pemesanan ini.');
        }

        $pdf = Pdf::loadView('admin.bookings.ticket-pdf', compact('booking'));
        return $pdf->stream('ticket-' . $booking->ticket->ticket_code . '.pdf');
    }

    public function confirm($id)
    {
        $booking = Booking::with(['payment', 'ticket'])->findOrFail($id);

        try {
            // Idempotency: don't re-confirm an already paid/confirmed booking.
            if ($booking->payment_status === 'paid' && $booking->booking_status === 'confirmed') {
                return redirect()->route('admin.bookings.show', $id)
                    ->with('success', 'Pemesanan sudah dikonfirmasi sebelumnya');
            }

            $booking->update([
                'booking_status' => 'confirmed',
                'payment_status' => 'paid'
            ]);

            // Keep the payment record consistent with the booking so the panel
            // never shows two conflicting statuses (paid booking / pending payment).
            $payment = Payment::where('booking_id', $booking->id)->latest('id')->first();
            if ($payment) {
                $payment->update([
                    'status' => 'success',
                    'payment_date' => $payment->payment_date ?? now(),
                ]);
            }

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
            $booking->schedule->updateAvailableSeats();

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
            'schedule_id' => $booking->schedule_id,
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
