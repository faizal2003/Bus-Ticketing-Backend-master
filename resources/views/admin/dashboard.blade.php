@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Buses -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-bus text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Bus</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $totalBuses ?? 0 }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-green-600">
                        <i class="fas fa-arrow-up"></i> {{ $activeBuses ?? 0 }} aktif
                    </span>
                </div>
            </div>

            <!-- Today's Bookings -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Pemesanan Hari Ini</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $todayBookings ?? 0 }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-blue-600">
                        Rp {{ number_format($todayRevenue ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Total Passengers -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Penumpang</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $totalPassengers ?? 0 }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-purple-600">
                        Bulan ini: {{ $monthPassengers ?? 0 }}
                    </span>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Pendapatan</h3>
                        <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-green-600">
                        <i class="fas fa-arrow-up"></i> 12% dari bulan lalu
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Bookings -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Pemesanan Terbaru</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode Booking
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Penumpang
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentBookings ?? [] as $booking)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $booking['booking_code'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking['created_at'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $booking['passenger_name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking['seat_count'] }} kursi</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($booking['status'] === 'confirmed')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Dikonfirmasi
                                            </span>
                                        @elseif($booking['status'] === 'pending')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Menunggu
                                            </span>
                                        @elseif($booking['status'] === 'cancelled')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Dibatalkan
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $booking['status'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.bookings.show', $booking['id']) }}"
                                            class="text-blue-600 hover:text-blue-900">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data pemesanan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upcoming Schedules -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Jadwal Mendatang</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rute
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kursi Tersedia
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($upcomingSchedules ?? [] as $schedule)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $schedule['departure'] }} → {{ $schedule['arrival'] }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $schedule['bus_name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $schedule['departure_time'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $schedule['available_seats'] }} kursi</div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                                            <div class="bg-blue-600 h-2.5 rounded-full"
                                                style="width: {{ $schedule['seat_percentage'] }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada jadwal mendatang
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.buses.create') }}"
                    class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-blue-300 rounded-lg hover:bg-blue-50 hover:border-blue-400 transition-colors">
                    <i class="fas fa-plus-circle text-blue-600 text-3xl mb-3"></i>
                    <p class="text-sm font-medium text-gray-900">Tambah Bus Baru</p>
                    <p class="text-xs text-gray-500 mt-1">Tambah bus ke armada</p>
                </a>

                <a href="{{ route('admin.schedules.create') }}"
                    class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-green-300 rounded-lg hover:bg-green-50 hover:border-green-400 transition-colors">
                    <i class="fas fa-calendar-plus text-green-600 text-3xl mb-3"></i>
                    <p class="text-sm font-medium text-gray-900">Buat Jadwal Baru</p>
                    <p class="text-xs text-gray-500 mt-1">Buat jadwal perjalanan</p>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                    class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-purple-300 rounded-lg hover:bg-purple-50 hover:border-purple-400 transition-colors">
                    <i class="fas fa-file-export text-purple-600 text-3xl mb-3"></i>
                    <p class="text-sm font-medium text-gray-900">Generate Laporan</p>
                    <p class="text-xs text-gray-500 mt-1">Buat laporan kinerja</p>
                </a>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Sistem</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-900 mt-2">Sistem Aktif</p>
                    <p class="text-xs text-gray-500">Semua sistem berjalan normal</p>
                </div>

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">
                        <i class="fas fa-database"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-900 mt-2">Database</p>
                    <p class="text-xs text-gray-500">Koneksi stabil</p>
                </div>

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">
                        <i class="fas fa-server"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-900 mt-2">Server</p>
                    <p class="text-xs text-gray-500">Perform optimal</p>
                </div>

                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-900 mt-2">Waktu</p>
                    <p class="text-xs text-gray-500">{{ now()->format('H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .transition-colors {
            transition: all 0.3s ease;
        }

        .border-2 {
            border-width: 2px;
        }

        .border-dashed {
            border-style: dashed;
        }
    </style>
@endpush
