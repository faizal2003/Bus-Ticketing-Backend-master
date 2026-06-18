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
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-gray-900">Aksi Cepat</h3>
                <p class="text-gray-500 text-sm mt-1">Akses fitur utama dengan cepat</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
                @if (auth()->user()->is_super_admin)
                    <a href="{{ route('admin.buses.create') }}"
                        class="action-card bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200 rounded-xl p-8 hover:shadow-2xl hover:border-blue-400 transition-all duration-300 group">
                        <div class="flex flex-col items-center justify-center h-full">
                            <div
                                class="icon-container bg-blue-500 text-white rounded-full p-5 mb-4 group-hover:scale-110 group-hover:shadow-lg transition-all duration-300">
                                <i class="fas fa-plus-circle text-2xl"></i>
                            </div>
                            <h4 class="text-base font-bold text-gray-900 text-center">Tambah Bus Baru</h4>
                            <p class="text-sm text-gray-600 mt-2 text-center">Tambahkan armada bus terbaru</p>
                        </div>
                    </a>
                @endif

                <a href="{{ route('admin.schedules.create') }}"
                    class="action-card bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-200 rounded-xl p-8 hover:shadow-2xl hover:border-green-400 transition-all duration-300 group">
                    <div class="flex flex-col items-center justify-center h-full">
                        <div
                            class="icon-container bg-green-500 text-white rounded-full p-5 mb-4 group-hover:scale-110 group-hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-calendar-plus text-2xl"></i>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 text-center">Buat Jadwal Baru</h4>
                        <p class="text-sm text-gray-600 mt-2 text-center">Buat jadwal perjalanan baru</p>
                    </div>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                    class="action-card bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-200 rounded-xl p-8 hover:shadow-2xl hover:border-purple-400 transition-all duration-300 group">
                    <div class="flex flex-col items-center justify-center h-full">
                        <div
                            class="icon-container bg-purple-500 text-white rounded-full p-5 mb-4 group-hover:scale-110 group-hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-file-export text-2xl"></i>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 text-center">Generate Laporan</h4>
                        <p class="text-sm text-gray-600 mt-2 text-center">Buat laporan kinerja sistem</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Action Cards */
        .action-card {
            min-height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-card:hover::before {
            left: 100%;
        }

        .icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            min-height: 80px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .action-card:hover .icon-container {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .action-card {
                min-height: 220px;
            }

            .icon-container {
                min-width: 70px;
                min-height: 70px;
            }
        }

        @media (max-width: 768px) {
            .action-card {
                min-height: 200px;
            }

            .icon-container {
                min-width: 60px;
                min-height: 60px;
            }
        }

        /* Smooth transitions */
        * {
            transition-property: background-color, border-color, color, fill, stroke, box-shadow, transform;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }

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
