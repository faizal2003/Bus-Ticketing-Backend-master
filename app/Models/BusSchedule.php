<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'departure_city',
        'arrival_city',
        'departure_time',
        'arrival_time',
        'price_per_seat',
        'available_seats',
        'status',
        'notes'
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'price_per_seat' => 'decimal:2',
        'available_seats' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Add this method to fix the relationship issue
    public function bus()
    {
        return $this->belongsTo(Bus::class)->withDefault([
            'bus_name' => 'Bus tidak ditemukan',
            'bus_number' => '-',
            'total_seats' => 0
        ]);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'schedule_id');
    }

    public function confirmedBookings()
    {
        return $this->bookings()->where('booking_status', 'confirmed');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('departure_time', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('departure_time', '<', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('departure_time', today());
    }

    public function scopeSearch($query, $departure = null, $arrival = null, $date = null)
    {
        if ($departure) {
            $query->where('departure_city', 'like', "%{$departure}%");
        }

        if ($arrival) {
            $query->where('arrival_city', 'like', "%{$arrival}%");
        }

        if ($date) {
            $query->whereDate('departure_time', $date);
        }

        return $query;
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_seats', '>', 0);
    }

    public function getFormattedDepartureTimeAttribute()
    {
        return $this->departure_time->format('d M Y, H:i');
    }

    public function getFormattedArrivalTimeAttribute()
    {
        return $this->arrival_time->format('d M Y, H:i');
    }

    public function getDurationAttribute()
    {
        $diff = $this->departure_time->diff($this->arrival_time);

        if ($diff->days > 0) {
            return $diff->days . ' hari ' . $diff->h . ' jam';
        }

        return $diff->h . ' jam ' . $diff->i . ' menit';
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price_per_seat, 0, ',', '.');
    }

    public function isFull()
    {
        return $this->available_seats <= 0;
    }

    public function hasDeparted()
    {
        return $this->departure_time < now();
    }

    public function isPast()
    {
        return $this->departure_time < now();
    }

    public function updateAvailableSeats()
    {
        $confirmedBookings = $this->confirmedBookings()->sum('total_passengers');
        $this->available_seats = max(0, $this->bus->total_seats - $confirmedBookings);
        $this->save();

        return $this;
    }

    public function bookSeats($quantity)
    {
        if ($this->available_seats < $quantity) {
            throw new \Exception('Not enough available seats');
        }

        $this->available_seats -= $quantity;
        $this->save();

        return $this;
    }

    public function cancelSeats($quantity)
    {
        $this->available_seats += $quantity;
        $this->save();

        return $this;
    }

    public function getRouteAttribute()
    {
        return $this->departure_city . ' → ' . $this->arrival_city;
    }

    public function getAvailableSeats()
    {
        $bookedSeats = $this->bookings()
            ->whereIn('booking_status', ['confirmed', 'pending'])
            ->with('passengers')
            ->get()
            ->pluck('passengers.*.seat_number')
            ->flatten()
            ->toArray();

        return $this->bus->seats()
            ->whereNotIn('seat_number', $bookedSeats)
            ->where('is_available', true)
            ->get();
    }
}
