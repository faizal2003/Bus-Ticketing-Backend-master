<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'booking_id',
        'payment_method',
        'transaction_id',
        'amount',
        'status',
        'midtrans_response',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'midtrans_response' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }



    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
