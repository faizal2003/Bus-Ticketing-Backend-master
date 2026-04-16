@extends('layouts.superadmin')

@section('title', 'Laporan & Statistik')

@section('content')
    <div class="space-y-6">
        <!-- Filter Tanggal -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('superadmin.reports.index') }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded-lg px-3 py-2">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Filter</button>
            </form>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showTab('bookings')" id="tab-bookings"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ request('tab', 'bookings') == 'bookings' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500' }}">
                    Laporan Pemesanan
                </button>
                <button onclick="showTab('revenue')" id="tab-revenue"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ request('tab') == 'revenue' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500' }}">
                    Laporan Pendapatan
                </button>
                <button onclick="showTab('buses')" id="tab-buses"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ request('tab') == 'buses' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500' }}">
                    Laporan Bus
                </button>
            </nav>
        </div>

        <!-- Tab Pemesanan -->
        <div id="tab-content-bookings" class="{{ request('tab', 'bookings') == 'bookings' ? '' : 'hidden' }}">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Data Pemesanan</h3>
                    <div class="space-x-2">
                        <a href="{{ route('superadmin.reports.export.bookings.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="bg-red-600 text-white px-3 py-1 rounded text-sm">PDF</a>
                        <a href="{{ route('superadmin.reports.export.bookings.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="bg-green-600 text-white px-3 py-1 rounded text-sm">Excel</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Kode</th>
                                <th class="px-4 py-2 text-left">Pelanggan</th>
                                <th class="px-4 py-2 text-left">Rute</th>
                                <th class="px-4 py-2 text-left">Berangkat</th>
                                <th class="px-4 py-2 text-left">Jumlah</th>
                                <th class="px-4 py-2 text-left">Total</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                <tr>
                                    <td class="px-4 py-2">{{ $booking->booking_code }}</td>
                                    <td class="px-4 py-2">{{ $booking->user->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ $booking->schedule->departure_city ?? '' }} →
                                        {{ $booking->schedule->arrival_city ?? '' }}</td>
                                    <td class="px-4 py-2">
                                        {{ optional($booking->schedule)->departure_time ? $booking->schedule->departure_time->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-2">{{ $booking->total_passengers }}</td>
                                    <td class="px-4 py-2">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($booking->booking_status) }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($booking->payment_status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $bookings->appends(['tab' => 'bookings'])->links() }}</div>
            </div>
        </div>

        <!-- Tab Pendapatan -->
        <div id="tab-content-revenue" class="{{ request('tab') == 'revenue' ? '' : 'hidden' }}">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Pendapatan per Hari</h3>
                    <div class="space-x-2">
                        <a href="{{ route('superadmin.reports.export.revenue.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="bg-red-600 text-white px-3 py-1 rounded text-sm">PDF</a>
                        <a href="{{ route('superadmin.reports.export.revenue.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="bg-green-600 text-white px-3 py-1 rounded text-sm">Excel</a>
                    </div>
                </div>
                <div class="overflow-x-auto p-4">
                    <table class="min-w-full divide-y">
                        <thead>
                            <tr>
                                <th class="text-left">Tanggal</th>
                                <th class="text-left">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($revenueData as $item)
                                <tr>
                                    <td>{{ $item->date }}</td>
                                    <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Bus -->
        <div id="tab-content-buses" class="{{ request('tab') == 'buses' ? '' : 'hidden' }}">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Laporan Bus</h3>
                    <div class="space-x-2">
                        <a href="{{ route('superadmin.reports.export.buses.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="bg-red-600 text-white px-3 py-1 rounded text-sm">PDF</a>
                        <a href="{{ route('superadmin.reports.export.buses.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="bg-green-600 text-white px-3 py-1 rounded text-sm">Excel</a>
                    </div>
                </div>
                <div class="overflow-x-auto p-4">
                    <table class="min-w-full divide-y">
                        <thead>
                            <tr>
                                <th>Nama Bus</th>
                                <th>Plat</th>
                                <th>Tipe</th>
                                <th>Kursi</th>
                                <th>Jadwal</th>
                                <th>Penumpang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($busesReport as $bus)
                                <tr>
                                    <td>{{ $bus->bus_name }}</td>
                                    <td>{{ $bus->plate_number }}</td>
                                    <td>{{ $bus->bus_type }}</td>
                                    <td>{{ $bus->total_seats }}</td>
                                    <td>{{ $bus->total_schedules ?? 0 }}</td>
                                    <td>{{ $bus->total_passengers ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showTab(tabName) {
            document.querySelectorAll('[id^="tab-content-"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.remove('border-purple-500', 'text-purple-600');
                el.classList.add('border-transparent', 'text-gray-500');
            });
            document.getElementById('tab-content-' + tabName).classList.remove('hidden');
            document.getElementById('tab-' + tabName).classList.add('border-purple-500', 'text-purple-600');
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }
    </script>
@endpush
