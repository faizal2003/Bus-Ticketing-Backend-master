<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
use App\Exports\RevenueExport;
use App\Exports\BusesExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $bookings = Booking::with(['user', 'schedule'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $revenueData = Booking::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $busesReport = Bus::withCount(['schedules as total_schedules' => function($q) use ($start, $end) {
                $q->whereBetween('departure_time', [$start, $end]);
            }])
            ->get()
            ->map(function($bus) use ($start, $end) {
                $totalPassengers = Booking::whereHas('schedule', function($q) use ($bus, $start, $end) {
                        $q->where('bus_id', $bus->id)
                          ->whereBetween('departure_time', [$start, $end]);
                    })
                    ->where('booking_status', 'confirmed')
                    ->sum('total_passengers');
                $bus->total_passengers = $totalPassengers;
                return $bus;
            });

        return view('superadmin.reports.index', compact('bookings', 'revenueData', 'busesReport', 'startDate', 'endDate'));
    }

    public function exportBookingsPDF(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $bookings = Booking::with(['user', 'schedule'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('superadmin.reports.pdf_bookings', compact('bookings', 'startDate', 'endDate'));
        return $pdf->download('laporan_pemesanan_' . date('Ymd') . '.pdf');
    }

    public function exportBookingsExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        return Excel::download(new BookingsExport($startDate, $endDate), 'laporan_pemesanan_' . date('Ymd') . '.xlsx');
    }

    public function exportRevenuePDF(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $revenueData = Booking::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $totalRevenue = $revenueData->sum('total');

        $pdf = Pdf::loadView('superadmin.reports.pdf_revenue', compact('revenueData', 'totalRevenue', 'startDate', 'endDate'));
        return $pdf->download('laporan_pendapatan_' . date('Ymd') . '.pdf');
    }

    public function exportRevenueExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        return Excel::download(new RevenueExport($startDate, $endDate), 'laporan_pendapatan_' . date('Ymd') . '.xlsx');
    }

    public function exportBusesPDF(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $buses = Bus::withCount(['schedules as total_schedules' => function($q) use ($start, $end) {
                $q->whereBetween('departure_time', [$start, $end]);
            }])
            ->get()
            ->map(function($bus) use ($start, $end) {
                $totalPassengers = Booking::whereHas('schedule', function($q) use ($bus, $start, $end) {
                        $q->where('bus_id', $bus->id)
                          ->whereBetween('departure_time', [$start, $end]);
                    })
                    ->where('booking_status', 'confirmed')
                    ->sum('total_passengers');
                $bus->total_passengers = $totalPassengers;
                return $bus;
            });

        $pdf = Pdf::loadView('superadmin.reports.pdf_buses', compact('buses', 'startDate', 'endDate'));
        return $pdf->download('laporan_bus_' . date('Ymd') . '.pdf');
    }

    public function exportBusesExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        return Excel::download(new BusesExport($startDate, $endDate), 'laporan_bus_' . date('Ymd') . '.xlsx');
    }
}
