<?php

namespace App\Exports;

use App\Models\Bus;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class BusesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $buses = Bus::all();
        foreach ($buses as $bus) {
            $bus->total_schedules = $bus->schedules()->whereBetween('departure_time', [$start, $end])->count();
            $bus->total_passengers = $bus->schedules()->whereBetween('departure_time', [$start, $end])
                ->with('bookings')
                ->get()
                ->sum(function($schedule) {
                    return $schedule->bookings()->where('booking_status', 'confirmed')->sum('total_passengers');
                });
        }
        return $buses;
    }

    public function headings(): array
    {
        return ['ID', 'Nama Bus', 'Plat Nomor', 'Tipe', 'Total Kursi', 'Jumlah Jadwal', 'Total Penumpang'];
    }

    public function map($bus): array
    {
        return [
            $bus->id,
            $bus->bus_name,
            $bus->plate_number,
            $bus->bus_type,
            $bus->total_seats,
            $bus->total_schedules ?? 0,
            $bus->total_passengers ?? 0,
        ];
    }
}
