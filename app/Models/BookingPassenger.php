<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'full_name',
        'id_number',
        'phone',
        'seat_number',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeByBooking($query, $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    public function scopeBySeat($query, $seatNumber)
    {
        return $query->where('seat_number', $seatNumber);
    }

    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->full_name);
        $initials = '';

        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }

        return substr($initials, 0, 2);
    }

    public function getFormattedIdNumber()
    {
        if (empty($this->id_number)) {
            return '-';
        }

        return wordwrap($this->id_number, 4, ' ', true);
    }
}
