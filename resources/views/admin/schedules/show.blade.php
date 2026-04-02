@extends('layouts.admin')

@section('title', 'Detail Jadwal')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Schedule Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $schedule->departure_city }} → {{ $schedule->arrival_city }}
                        </h2>
                        <div class="mt-2 flex items-center space-x-4">
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium
                            {{ $schedule->status == 'active'
                                ? 'bg-green-100 text-green-800'
                                : ($schedule->status == 'completed'
                                    ? 'bg-gray-100 text-gray-800'
                                    : 'bg-red-100 text-red-800') }}">
                                {{ $schedule->status == 'active' ? 'Aktif' : ($schedule->status == 'completed' ? 'Selesai' : 'Dibatalkan') }}
                            </span>
                            <span class="text-gray-600">
                                <i class="far fa-calendar-alt mr-1"></i>
                                {{ \Carbon\Carbon::parse($schedule->departure_time)->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        @if (!$schedule->isPast() && $schedule->status != 'cancelled')
                            <a href="{{ route('admin.schedules.edit', $schedule) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-edit mr-2"></i>
                                Edit
                            </a>
                        @endif
                        <a href="{{ route('admin.schedules.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Schedule Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column - Schedule Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Jadwal</h3>

                        <div class="space-y-4">
                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Bus:</div>
                                <div class="w-2/3 font-medium">
                                    {{ $schedule->bus->bus_name ?? 'Bus tidak ditemukan' }}
                                    <span class="text-gray-500 text-sm">({{ $schedule->bus->bus_number ?? '-' }})</span>
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Rute:</div>
                                <div class="w-2/3 font-medium">
                                    {{ $schedule->departure_city }} → {{ $schedule->arrival_city }}
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Waktu Keberangkatan:</div>
                                <div class="w-2/3 font-medium">
                                    {{ \Carbon\Carbon::parse($schedule->departure_time)->format('d M Y, H:i') }}
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Waktu Tiba:</div>
                                <div class="w-2/3 font-medium">
                                    {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('d M Y, H:i') }}
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Durasi:</div>
                                <div class="w-2/3 font-medium">
                                    @php
                                        $departure = \Carbon\Carbon::parse($schedule->departure_time);
                                        $arrival = \Carbon\Carbon::parse($schedule->arrival_time);
                                        $duration = $departure->diff($arrival);
                                    @endphp
                                    {{ $duration->h }} jam {{ $duration->i }} menit
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Harga per Kursi:</div>
                                <div class="w-2/3 font-medium text-green-600">
                                    Rp {{ number_format($schedule->price_per_seat, 0, ',', '.') }}
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Kursi Tersedia:</div>
                                <div class="w-2/3">
                                    <div class="flex items-center">
                                        <span class="font-medium mr-2">{{ $schedule->available_seats }} /
                                            {{ $schedule->bus->total_seats ?? 0 }}</span>
                                        @php
                                            $totalSeats = $schedule->bus->total_seats ?? 1;
                                            $bookedSeats = $totalSeats - $schedule->available_seats;
                                            $percentage = ($bookedSeats / $totalSeats) * 100;
                                        @endphp
                                        <div class="w-32 bg-gray-200 rounded-full h-2">
                                            <div class="bg-{{ $schedule->isFull() ? 'red' : 'green' }}-600 h-2 rounded-full"
                                                style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                    </div>
                                    @if ($schedule->isFull())
                                        <div class="text-sm text-red-600 mt-1">Jadwal sudah penuh</div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Status:</div>
                                <div class="w-2/3">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $schedule->status == 'active'
                                        ? 'bg-green-100 text-green-800'
                                        : ($schedule->status == 'completed'
                                            ? 'bg-gray-100 text-gray-800'
                                            : 'bg-red-100 text-red-800') }}">
                                        {{ $schedule->status == 'active' ? 'Aktif' : ($schedule->status == 'completed' ? 'Selesai' : 'Dibatalkan') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Tanggal Dibuat:</div>
                                <div class="w-2/3 font-medium">{{ $schedule->created_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>

                        <!-- Bookings Count -->
                        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">Statistik Pemesanan:</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ $bookingsCount ?? 0 }}</div>
                                    <div class="text-sm text-blue-500">Total Pemesanan</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $confirmedBookings ?? 0 }}</div>
                                    <div class="text-sm text-green-500">Terkonfirmasi</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Bus Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Bus</h3>

                        @if ($schedule->bus)
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-bus text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800">{{ $schedule->bus->bus_name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $schedule->bus->bus_number }}</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tipe Bus:</span>
                                        <span class="font-medium">{{ $schedule->bus->bus_type }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Plat Nomor:</span>
                                        <span class="font-medium font-mono">{{ $schedule->bus->plate_number }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Jumlah Kursi:</span>
                                        <span class="font-medium">{{ $schedule->bus->total_seats }} kursi</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status Bus:</span>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $schedule->bus->status == 'active'
                                        ? 'bg-green-100 text-green-800'
                                        : ($schedule->bus->status == 'maintenance'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-red-100 text-red-800') }}">
                                            {{ $schedule->bus->status == 'active'
                                                ? 'Aktif'
                                                : ($schedule->bus->status == 'maintenance'
                                                    ? 'Perawatan'
                                                    : 'Non-Aktif') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Facilities -->
                                @if ($schedule->bus->facilities && is_array($schedule->bus->facilities) && count($schedule->bus->facilities) > 0)
                                    <div class="mt-6">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Fasilitas:</h5>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($schedule->bus->facilities as $facility)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-check-circle mr-1 text-xs"></i>
                                                    {{ $facility }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <a href="{{ route('admin.buses.show', $schedule->bus) }}"
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-external-link-alt mr-1"></i>
                                        Lihat detail bus
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="bg-red-50 rounded-lg p-6 text-center">
                                <i class="fas fa-exclamation-triangle text-red-400 text-3xl mb-3"></i>
                                <p class="text-red-600">Bus tidak ditemukan atau telah dihapus</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Bookings for This Schedule -->
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Pemesanan Terbaru untuk Jadwal Ini</h3>
                        @if (!$schedule->isPast() && !$schedule->isFull() && $schedule->status == 'active')
                            <a href="#" class="text-sm text-green-600 hover:text-green-800">
                                <i class="fas fa-plus-circle mr-1"></i>
                                Tambah Pemesanan Manual
                            </a>
                        @endif
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-600 text-center py-4">
                            <i class="fas fa-ticket-alt mr-2"></i>
                            Tidak ada pemesanan untuk jadwal ini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
