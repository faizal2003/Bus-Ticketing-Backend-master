<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'schedule_id',
        'total_passengers',
        'total_price',
        'booking_status',
        'payment_status',
        'payment_method',
        'payment_date',
        'ticket_code',
        'ticket_status',
        'boarding_status',
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'total_passengers' => 'integer',
        'payment_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(BusSchedule::class);
    }

    public function passengers()
    {
        return $this->hasMany(BookingPassenger::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopePendingPayment($query)
    {
        return $query->where('booking_status', 'confirmed')
                    ->where('payment_status', 'pending');
    }

    public function scopeBoarded($query)
    {
        return $query->where('boarding_status', 'boarded');
    }

    public function scopePendingBoarding($query)
    {
        return $query->where('boarding_status', 'pending')
                    ->where('booking_status', 'confirmed');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('booking_status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('booking_status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => ['label' => 'Menunggu', 'color' => 'warning'],
            'confirmed' => ['label' => 'Dikonfirmasi', 'color' => 'success'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => 'danger'],
            'expired' => ['label' => 'Kadaluarsa', 'color' => 'secondary'],
        ];

        return $statuses[$this->booking_status] ?? ['label' => $this->booking_status, 'color' => 'info'];
    }

    public function getPaymentStatusLabelAttribute()
    {
        $statuses = [
            'pending' => ['label' => 'Menunggu', 'color' => 'warning'],
            'paid' => ['label' => 'Dibayar', 'color' => 'success'],
            'failed' => ['label' => 'Gagal', 'color' => 'danger'],
            'expired' => ['label' => 'Kadaluarsa', 'color' => 'secondary'],
        ];

        return $statuses[$this->payment_status] ?? ['label' => $this->payment_status, 'color' => 'info'];
    }

    public function getIsConfirmedAttribute()
    {
        return $this->booking_status === 'confirmed';
    }

    public function getIsPaidAttribute()
    {
        return $this->payment_status === 'paid';
    }

    public function getCanBeCancelledAttribute()
    {
        return $this->booking_status === 'pending' && !$this->has_departed;
    }

    public function getHasDepartedAttribute()
    {
        return $this->schedule->departure_time < now();
    }

    public static function generateBookingCode()
    {
        $prefix = 'BK';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), 7, 6));

        return $prefix . '-' . $date . '-' . $random;
    }

    public function markAsConfirmed()
    {
        $this->update([
            'booking_status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        $this->schedule->updateAvailableSeats();

        return $this;
    }

    public function markAsCancelled()
    {
        $this->update([
            'booking_status' => 'cancelled',
            'payment_status' => 'refunded'
        ]);

        $this->schedule->updateAvailableSeats();

        return $this;
    }

    public function markAsPaid($paymentMethod, $paymentDate = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'payment_date' => $paymentDate ?? now(),
        ]);

        return $this;
    }

    public function generateTicket()
    {
        if (!$this->ticket_code) {
            $this->ticket_code = 'TICKET-' . strtoupper(uniqid());
            $this->ticket_status = 'active';
            $this->save();
        }

        return $this;
    }

    public function markAsBoarded()
    {
        $this->update([
            'boarding_status' => 'boarded',
        ]);

        return $this;
    }

    public function canBoard()
    {
        return $this->booking_status === 'confirmed'
            && $this->payment_status === 'paid'
            && $this->boarding_status === 'pending'
            && $this->schedule->departure_time->isFuture();
    }

    public function addPassenger($data)
    {
        return $this->passengers()->create([
            'full_name' => $data['full_name'],
            'id_number' => $data['id_number'] ?? null,
            'phone' => $data['phone'] ?? null,
            'seat_number' => $data['seat_number'],
        ]);
    }

    public function calculateTotalPrice()
    {
        $pricePerSeat = $this->schedule->price_per_seat;
        $totalPassengers = $this->total_passengers;

        return $pricePerSeat * $totalPassengers;
    }

    public function getSeatNumbers()
    {
        return $this->passengers->pluck('seat_number')->toArray();
    }
}
