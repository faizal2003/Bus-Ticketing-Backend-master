<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'seat_number',
        'seat_class',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeBooked($query)
    {
        return $query->where('is_available', false);
    }

    public function scopeByClass($query, $class)
    {
        return $query->where('seat_class', $class);
    }

    public function scopeWindowSeats($query)
    {
        return $query->whereIn('seat_number', function($query) {
            $query->select('seat_number')
                  ->from('bus_seats')
                  ->whereRaw("LEFT(seat_number, 1) IN ('A', 'D')");
        });
    }

    public function scopeAisleSeats($query)
    {
        return $query->whereIn('seat_number', function($query) {
            $query->select('seat_number')
                  ->from('bus_seats')
                  ->whereRaw("LEFT(seat_number, 1) IN ('B', 'C')");
        });
    }

    public function getSeatTypeAttribute()
    {
        $firstLetter = substr($this->seat_number, 0, 1);

        if (in_array($firstLetter, ['A', 'D'])) {
            return 'window';
        } elseif (in_array($firstLetter, ['B', 'C'])) {
            return 'aisle';
        }

        return 'unknown';
    }

    public function getRowNumberAttribute()
    {
        return intval(substr($this->seat_number, 1));
    }

    public function getSeatLetterAttribute()
    {
        return substr($this->seat_number, 0, 1);
    }

    public function markAsAvailable()
    {
        $this->update(['is_available' => true]);
        return $this;
    }

    public function markAsBooked()
    {
        $this->update(['is_available' => false]);
        return $this;
    }

    public function isWindowSeat()
    {
        return in_array($this->seat_letter, ['A', 'D']);
    }

    public function isAisleSeat()
    {
        return in_array($this->seat_letter, ['B', 'C']);
    }
}
