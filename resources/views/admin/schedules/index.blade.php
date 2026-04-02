@extends('layouts.admin')

@section('title', 'Kelola Jadwal')

@section('content')
    <div class="space-y-6">
        <!-- Header with Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Daftar Jadwal Bus</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Kelola jadwal keberangkatan bus
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('admin.schedules.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Tambah Jadwal Baru
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="departure_city" class="block text-sm font-medium text-gray-700">Kota Keberangkatan</label>
                    <input type="text" name="departure_city" id="departure_city" value="{{ request('departure_city') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Kota asal...">
                </div>
                <div>
                    <label for="arrival_city" class="block text-sm font-medium text-gray-700">Kota Tujuan</label>
                    <input type="text" name="arrival_city" id="arrival_city" value="{{ request('arrival_city') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Kota tujuan...">
                </div>
                <div>
                    <label for="departure_date" class="block text-sm font-medium text-gray-700">Tanggal
                        Keberangkatan</label>
                    <input type="date" name="departure_date" id="departure_date" value="{{ request('departure_date') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    @if (request()->hasAny(['departure_city', 'arrival_city', 'departure_date', 'status']))
                        <a href="{{ route('admin.schedules.index') }}"
                            class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Schedules Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
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
                                Waktu
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bus & Harga
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kursi Tersedia
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                            @php
                                $isPast = \Carbon\Carbon::parse($schedule->departure_time)->isPast();
                                $isFull = $schedule->available_seats <= 0;
                            @endphp
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-route text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $schedule->departure_city }} → {{ $schedule->arrival_city }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $schedule->bus->bus_name ?? 'Bus tidak ditemukan' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-plane-departure text-gray-400 mr-1"></i>
                                        {{ \Carbon\Carbon::parse($schedule->departure_time)->format('d M Y, H:i') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-plane-arrival text-gray-400 mr-1"></i>
                                        {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('d M Y, H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $schedule->bus->bus_number ?? '-' }}
                                    </div>
                                    <div class="text-sm font-medium text-green-600">
                                        Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $schedule->available_seats }} /
                                        {{ $schedule->bus->total_seats ?? 0 }}</div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        @php
                                            $totalSeats = $schedule->bus->total_seats ?? 1;
                                            $percentage =
                                                (($totalSeats - $schedule->available_seats) / $totalSeats) * 100;
                                        @endphp
                                        <div class="bg-{{ $isFull ? 'red' : 'green' }}-600 h-2 rounded-full"
                                            style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                    @if ($isFull)
                                        <div class="text-xs text-red-600 mt-1">Penuh</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($isPast)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Selesai
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $schedule->status == 'active'
                                        ? 'bg-green-100 text-green-800'
                                        : ($schedule->status == 'cancelled'
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-yellow-100 text-yellow-800') }}">
                                            @if ($schedule->status == 'active')
                                                <i class="fas fa-play-circle mr-1"></i>
                                            @elseif($schedule->status == 'cancelled')
                                                <i class="fas fa-times-circle mr-1"></i>
                                            @else
                                                <i class="fas fa-pause-circle mr-1"></i>
                                            @endif
                                            {{ $schedule->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.schedules.show', $schedule) }}"
                                            class="text-blue-600 hover:text-blue-900" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}"
                                            class="text-green-600 hover:text-green-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if (!$isPast && $schedule->status != 'cancelled')
                                            <form action="{{ route('admin.schedules.destroy', $schedule) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    onclick="return confirm('Apakah Anda yakin ingin membatalkan jadwal ini?')"
                                                    class="text-red-600 hover:text-red-900" title="Batalkan">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-calendar-alt text-gray-400 text-4xl mb-3"></i>
                                        <p class="text-gray-500">Tidak ada jadwal</p>
                                        <a href="{{ route('admin.schedules.create') }}"
                                            class="mt-2 inline-flex items-center text-green-600 hover:text-green-900">
                                            <i class="fas fa-plus-circle mr-1"></i>
                                            Buat jadwal pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($schedules->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $schedules->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Set today as default for date filter
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('departure_date');
            if (!dateInput.value) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            }
        });
    </script>
@endpush
