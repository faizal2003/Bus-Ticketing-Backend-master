<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class BookingsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return Booking::with(['user', 'schedule'])
            ->whereBetween('created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return ['Kode Booking', 'Nama Pelanggan', 'Rute', 'Tanggal Berangkat', 'Jumlah Penumpang', 'Total Harga', 'Status', 'Status Pembayaran', 'Tanggal Pemesanan'];
    }

    public function map($booking): array
    {
        return [
            $booking->booking_code,
            $booking->user->name ?? 'N/A',
            $booking->schedule->departure_city . ' → ' . $booking->schedule->arrival_city,
            $booking->schedule->departure_time->format('d/m/Y H:i'),
            $booking->total_passengers,
            $booking->total_price,
            $booking->booking_status,
            $booking->payment_status,
            $booking->created_at->format('d/m/Y H:i')
        ];
    }
}
