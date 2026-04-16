@extends('layouts.admin')

@section('title', 'Detail Jadwal: ' . $schedule->departure_city . ' → ' . $schedule->arrival_city)

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Detail Jadwal Bus</h2>
                    <p class="text-gray-600">Informasi lengkap jadwal perjalanan</p>
                </div>
                <a href="{{ route('admin.schedules.index') }}"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <div class="p-6">
                <!-- Informasi Bus -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Informasi Bus</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-gray-600">Nama Bus:</span>
                                <span class="font-medium ml-2">{{ $schedule->bus->bus_name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Nomor Bus:</span>
                                <span class="font-medium ml-2">{{ $schedule->bus->bus_number ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Plat Nomor:</span>
                                <span class="font-medium ml-2">{{ $schedule->bus->plate_number ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Tipe Bus:</span>
                                <span class="font-medium ml-2">{{ ucfirst($schedule->bus->bus_type ?? '-') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Perjalanan -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Detail Perjalanan</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-gray-600">Rute:</span>
                                <span class="font-medium ml-2">{{ $schedule->departure_city }} →
                                    {{ $schedule->arrival_city }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Keberangkatan:</span>
                                <span class="font-medium ml-2">{{ $schedule->departure_time->format('d M Y, H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Kedatangan:</span>
                                <span class="font-medium ml-2">{{ $schedule->arrival_time->format('d M Y, H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Durasi:</span>
                                <span class="font-medium ml-2">{{ $schedule->duration }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Harga per Kursi:</span>
                                <span class="font-medium ml-2">{{ $schedule->formatted_price }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Kursi Tersedia:</span>
                                <span
                                    class="font-medium ml-2 {{ $schedule->available_seats <= 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $schedule->available_seats }} / {{ $schedule->bus->total_seats ?? 0 }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Status:</span>
                                <span
                                    class="ml-2 px-2 py-1 text-xs rounded-full {{ $schedule->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $schedule->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Catatan:</span>
                                <span class="font-medium ml-2">{{ $schedule->notes ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Booking (Opsional) -->
                @if ($schedule->bookings && $schedule->bookings->count() > 0)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Daftar Pemesanan</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode
                                            Booking</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Penumpang</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                            Kursi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($schedule->bookings as $booking)
                                        <tr>
                                            <td class="px-6 py-4">{{ $booking->booking_code }}</td>
                                            <td class="px-6 py-4">{{ $booking->user->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4">{{ $booking->total_passengers }}</td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full
                                        {{ $booking->booking_status == 'confirmed'
                                            ? 'bg-green-100 text-green-800'
                                            : ($booking->booking_status == 'cancelled'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($booking->booking_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-calendar-alt mr-2"></i> Belum ada pemesanan untuk jadwal ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
