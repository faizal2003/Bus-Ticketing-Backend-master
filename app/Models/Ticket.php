<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'booking_id',
        'qr_code',
        'status',
        'boarding_status',
        'scanned_at',
        'scanned_by',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function conductorLogs()
    {
        return $this->hasMany(ConductorLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeExpired($query)
    {
        return $this->where('status', 'expired');
    }

    public function scopePendingBoarding($query)
    {
        return $query->where('boarding_status', 'pending');
    }

    public function scopeBoarded($query)
    {
        return $query->where('boarding_status', 'boarded');
    }

    public function scopeScanned($query)
    {
        return $query->whereNotNull('scanned_at');
    }

    public function scopeUnscanned($query)
    {
        return $query->whereNull('scanned_at');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'active' => ['label' => 'Aktif', 'color' => 'success'],
            'used' => ['label' => 'Digunakan', 'color' => 'info'],
            'expired' => ['label' => 'Kadaluarsa', 'color' => 'danger'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => 'secondary'],
        ];

        return $statuses[$this->status] ?? ['label' => $this->status, 'color' => 'warning'];
    }

    public function getBoardingStatusLabelAttribute()
    {
        $statuses = [
            'pending' => ['label' => 'Belum Naik', 'color' => 'warning'],
            'boarded' => ['label' => 'Sudah Naik', 'color' => 'success'],
            'missed' => ['label' => 'Terlewat', 'color' => 'danger'],
        ];

        return $statuses[$this->boarding_status] ?? ['label' => $this->boarding_status, 'color' => 'info'];
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getIsUsedAttribute()
    {
        return $this->status === 'used';
    }

    public function getIsScannedAttribute()
    {
        return !is_null($this->scanned_at);
    }

    public function getCanBeBoardedAttribute()
    {
        return $this->is_active &&
               $this->boarding_status === 'pending' &&
               !$this->booking->has_departed;
    }

    public static function generateTicketCode()
    {
        $prefix = 'TKT';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), 7, 8));

        return $prefix . '-' . $date . '-' . $random;
    }

    public function markAsScanned($conductorId)
    {
        $this->update([
            'scanned_at' => now(),
            'scanned_by' => $conductorId,
            'boarding_status' => 'boarded',
        ]);

        return $this;
    }

    public function markAsUsed()
    {
        $this->update(['status' => 'used']);
        return $this;
    }

    public function markAsExpired()
    {
        $this->update(['status' => 'expired']);
        return $this;
    }

    public function generateQrData()
    {
        $data = [
            'ticket_code' => $this->ticket_code,
            'booking_id' => $this->booking_id,
            'passenger_name' => $this->booking->passengers->first()->full_name ?? 'Unknown',
            'schedule_id' => $this->booking->schedule_id,
            'timestamp' => now()->timestamp,
        ];

        return json_encode($data);
    }

    public function getPassengerInfo()
    {
        $passenger = $this->booking->passengers->first();

        return [
            'name' => $passenger->full_name ?? 'Unknown',
            'seat' => $passenger->seat_number ?? 'Unknown',
            'id_number' => $passenger->id_number ?? '-',
        ];
    }

    public function getScheduleInfo()
    {
        $schedule = $this->booking->schedule;

        return [
            'route' => $schedule->departure_city . ' → ' . $schedule->arrival_city,
            'departure_time' => $schedule->formatted_departure_time,
            'arrival_time' => $schedule->formatted_arrival_time,
            'bus' => $schedule->bus->bus_name,
            'bus_number' => $schedule->bus->bus_number,
        ];
    }
}
