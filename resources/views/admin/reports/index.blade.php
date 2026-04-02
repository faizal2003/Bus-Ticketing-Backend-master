@extends('layouts.admin')

@section('title', 'Laporan Sistem')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Laporan Sistem</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Analisis dan statistik sistem pemesanan tiket bus
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button type="button" onclick="printReport()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-print mr-2"></i>
                    Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Date Range Selector -->
        <div class="bg-white shadow rounded-lg p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">
                            Tanggal Mulai
                        </label>
                        <input type="date" name="start_date" id="start_date"
                            value="{{ request('start_date', date('Y-m-01')) }}"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">
                            Tanggal Akhir
                        </label>
                        <input type="date" name="end_date" id="end_date"
                            value="{{ request('end_date', date('Y-m-d')) }}"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filter Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Pemesanan</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $reportData['total_bookings'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm {{ $reportData['booking_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        <i class="fas fa-arrow-{{ $reportData['booking_change'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($reportData['booking_change'] ?? 0) }}% dari periode sebelumnya
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Pendapatan</h3>
                        <p class="text-3xl font-bold text-gray-900">Rp
                            {{ number_format($reportData['total_revenue'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm {{ $reportData['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        <i class="fas fa-arrow-{{ $reportData['revenue_change'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($reportData['revenue_change'] ?? 0) }}% dari periode sebelumnya
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Penumpang</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $reportData['total_passengers'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm {{ $reportData['passenger_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        <i class="fas fa-arrow-{{ $reportData['passenger_change'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($reportData['passenger_change'] ?? 0) }}% dari periode sebelumnya
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                            <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Rata-rata Pemesanan</h3>
                        <p class="text-3xl font-bold text-gray-900">Rp
                            {{ number_format($reportData['avg_booking'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-500">per transaksi</span>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Chart -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Pendapatan per Bulan</h3>
                <div class="h-64 flex items-center justify-center">
                    <!-- Chart placeholder -->
                    <div class="text-center">
                        <i class="fas fa-chart-bar text-gray-400 text-4xl mb-3"></i>
                        <p class="text-gray-500">Chart akan ditampilkan di sini</p>
                    </div>
                </div>
            </div>

            <!-- Booking Status Distribution -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Status Pemesanan</h3>
                <div class="space-y-4">
                    @foreach ($reportData['status_distribution'] ?? [] as $status)
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>{{ $status['label'] }}</span>
                                <span>{{ $status['count'] }} ({{ $status['percentage'] }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-{{ $status['color'] }}-600 h-2 rounded-full"
                                    style="width: {{ $status['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Routes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Rute Terpopuler</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rute
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah Pemesanan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Penumpang
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Pendapatan
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rata-rata Okupansi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData['top_routes'] ?? [] as $route)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $route['departure'] }} → {{ $route['arrival'] }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $route['schedule_count'] }} jadwal</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $route['booking_count'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $route['passenger_count'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-green-600">
                                        Rp {{ number_format($route['revenue'], 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-900 mr-2">{{ $route['occupancy_rate'] }}%</span>
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-{{ $route['occupancy_color'] }}-600 h-2 rounded-full"
                                                style="width: {{ min($route['occupancy_rate'], 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Tidak ada data rute
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bus Performance -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Performa Bus</h3>
                <div class="space-y-4">
                    @forelse($reportData['bus_performance'] ?? [] as $bus)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-bus text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $bus['name'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $bus['number'] }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">{{ $bus['occupancy'] }}% okupansi</div>
                                <div class="text-xs text-green-600">Rp {{ number_format($bus['revenue'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">Tidak ada data performa bus</p>
                    @endforelse
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Metode Pembayaran</h3>
                <div class="space-y-4">
                    @forelse($reportData['payment_methods'] ?? [] as $method)
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>{{ $method['name'] }}</span>
                                <span>{{ $method['count'] }} ({{ $method['percentage'] }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $method['percentage'] }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">Tidak ada data metode pembayaran</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ekspor Laporan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button type="button"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-file-excel text-green-600 mr-2"></i>
                    Export ke Excel
                </button>
                <button type="button"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-file-pdf text-red-600 mr-2"></i>
                    Export ke PDF
                </button>
                <button type="button"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-file-csv text-blue-600 mr-2"></i>
                    Export ke CSV
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function printReport() {
            window.print();
        }

        // Set default date range to current month
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            if (!startDate.value) {
                const firstDay = new Date();
                firstDay.setDate(1);
                startDate.value = firstDay.toISOString().split('T')[0];
            }

            if (!endDate.value) {
                endDate.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
@endpush
