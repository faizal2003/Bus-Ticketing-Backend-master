<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConductorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'conductor_id',
        'ticket_id',
        'action',
        'details',
        'location',
        'ip_address',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function conductor()
    {
        return $this->belongsTo(User::class, 'conductor_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public static function logScan($conductorId, $ticketId, $result)
    {
        return self::create([
            'conductor_id' => $conductorId,
            'ticket_id' => $ticketId,
            'action' => 'scan_ticket',
            'details' => [
                'result' => $result,
                'timestamp' => now()->toDateTimeString(),
            ],
            'location' => request()->ip(),
            'ip_address' => request()->ip(),
        ]);
    }

    public static function logBoarding($conductorId, $ticketId, $seatNumber)
    {
        return self::create([
            'conductor_id' => $conductorId,
            'ticket_id' => $ticketId,
            'action' => 'boarding',
            'details' => [
                'seat_number' => $seatNumber,
                'timestamp' => now()->toDateTimeString(),
            ],
        ]);
    }

    public static function logDeparture($conductorId, $scheduleId, $actualTime = null)
    {
        return self::create([
            'conductor_id' => $conductorId,
            'action' => 'report_departure',
            'details' => [
                'schedule_id' => $scheduleId,
                'actual_time' => $actualTime ?? now()->toDateTimeString(),
                'scheduled_time' => now()->toDateTimeString(),
            ],
        ]);
    }

    public static function logArrival($conductorId, $scheduleId, $actualTime = null)
    {
        return self::create([
            'conductor_id' => $conductorId,
            'action' => 'report_arrival',
            'details' => [
                'schedule_id' => $scheduleId,
                'actual_time' => $actualTime ?? now()->toDateTimeString(),
                'scheduled_time' => now()->toDateTimeString(),
            ],
        ]);
    }

    public function getFormattedDetailsAttribute()
    {
        return json_encode($this->details, JSON_PRETTY_PRINT);
    }

    public function getActionNameAttribute()
    {
        $actions = [
            'scan_ticket' => 'Scan Tiket',
            'boarding' => 'Penumpang Naik',
            'report_departure' => 'Laporan Keberangkatan',
            'report_arrival' => 'Laporan Kedatangan',
            'update_status' => 'Update Status',
        ];

        return $actions[$this->action] ?? $this->action;
    }
}
