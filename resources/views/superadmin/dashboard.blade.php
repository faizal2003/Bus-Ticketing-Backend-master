@extends('layouts.superadmin')

@section('title', 'Dashboard Super Admin')

@section('content')
    <div class="space-y-6">
        <!-- 5 Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- Total Penumpang -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Penumpang</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($totalPassengers) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Transaksi -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center">
                            <i class="fas fa-receipt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Transaksi</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($totalTransactions) }}</p>
                    </div>
                </div>
            </div>

            <!-- Pendapatan -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-yellow-500 to-yellow-600 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Pendapatan</h3>
                        <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Jadwal Aktif -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Jadwal Aktif</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($activeSchedules) }}</p>
                    </div>
                </div>
            </div>

            <!-- Kursi Terisi -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-red-500 to-red-600 flex items-center justify-center">
                            <i class="fas fa-chair text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Kursi Terisi</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($occupiedSeats) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dua Grafik -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Grafik Penjualan -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik Penjualan (7 Hari Terakhir)</h3>
                <canvas id="salesChart" height="200"></canvas>
            </div>

            <!-- Grafik Rute Terlaris -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Rute Terlaris</h3>
                <canvas id="routesChart" height="200"></canvas>
            </div>
        </div>

        <!-- Ringkasan Sistem & Pemesanan Terbaru -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Ringkasan Sistem -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Sistem</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-500 text-sm">Total Bus</p>
                        <p class="text-2xl font-bold">{{ number_format($totalBuses) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total User</p>
                        <p class="text-2xl font-bold">{{ number_format($totalUsers) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Booking Hari Ini</p>
                        <p class="text-2xl font-bold">{{ number_format($todayBookings) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Pendapatan Hari Ini</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Pemesanan Terbaru -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Pemesanan Terbaru</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penumpang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recentBookings as $booking)
                                <tr>
                                    <td class="px-6 py-4 text-sm">{{ $booking['booking_code'] }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $booking['user_name'] }}</td>
                                    <td class="px-6 py-4 text-sm">Rp
                                        {{ number_format($booking['total_price'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full {{ $booking['status'] == 'confirmed' ? 'bg-green-100 text-green-800' : ($booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($booking['status']) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-gray-500">Belum ada pemesanan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Grafik Penjualan (Line Chart)
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: @json($salesChart['labels']),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: @json($salesChart['data']),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `Rp ${ctx.raw.toLocaleString('id-ID')}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (val) => `Rp ${val.toLocaleString('id-ID')}`
                        }
                    }
                }
            }
        });

        // Grafik Rute Terlaris (Bar Chart)
        const routesCtx = document.getElementById('routesChart').getContext('2d');
        const routesData = @json($topRoutes);
        new Chart(routesCtx, {
            type: 'bar',
            data: {
                labels: routesData.map(item => item.route),
                datasets: [{
                    label: 'Jumlah Pemesanan',
                    data: routesData.map(item => item.total_bookings),
                    backgroundColor: 'rgba(168, 85, 247, 0.6)',
                    borderColor: 'rgb(168, 85, 247)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
@endpush
