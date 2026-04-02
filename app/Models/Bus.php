<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_number',
        'bus_name',
        'plate_number',
        'bus_type',
        'total_seats',
        'facilities',
        'status',
    ];

    protected $casts = [
        'facilities' => 'array',
        'total_seats' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function seats()
    {
        return $this->hasMany(BusSeat::class);
    }

    public function schedules()
    {
        return $this->hasMany(BusSchedule::class, 'bus_id');
    }

    public function getSchedulesCountAttribute()
    {
        return $this->schedules()->count();
    }

    public function getActiveSchedulesCountAttribute()
    {
        return $this->schedules()->where('status', 'active')->count();
    }

    public function activeSchedules()
    {
        return $this->schedules()->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('bus_type', $type);
    }

    public function getAvailableSeatsCountAttribute()
    {
        return $this->seats()->where('is_available', true)->count();
    }

    public function getBookedSeatsCountAttribute()
    {
        return $this->total_seats - $this->available_seats_count;
    }

    public function getFacilitiesListAttribute()
    {
        if (is_array($this->facilities)) {
            return implode(', ', $this->facilities);
        }

        return 'Tidak ada fasilitas';
    }

    public function getHasAvailableSeatsAttribute()
    {
        return $this->available_seats_count > 0;
    }

    public function getBusTypeNameAttribute()
    {
        $types = [
            'Regular' => 'Reguler',
            'Executive' => 'Eksekutif',
            'VIP' => 'VIP',
            'Super' => 'Super',
        ];

        return $types[$this->bus_type] ?? $this->bus_type;
    }

    public function generateSeats()
    {
        $seats = [];
        $rows = ceil($this->total_seats / 4);
        $seatLetters = ['A', 'B', 'C', 'D'];

        for ($row = 1; $row <= $rows; $row++) {
            foreach ($seatLetters as $letter) {
                $seatNumber = $letter . $row;

                BusSeat::create([
                    'bus_id' => $this->id,
                    'seat_number' => $seatNumber,
                    'seat_class' => 'regular',
                    'is_available' => true,
                ]);

                if (count($seats) >= $this->total_seats) {
                    break 2;
                }
            }
        }

        return $this->seats()->count();
    }

    public function getSeat($seatNumber)
    {
        return $this->seats()->where('seat_number', $seatNumber)->first();
    }

    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
        return $this;
    }
}
