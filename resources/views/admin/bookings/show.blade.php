@extends('layouts.admin')

@section('title', 'Detail Pemesanan: ' . $booking['booking_code'])

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Booking Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Pemesanan: {{ $booking['booking_code'] }}</h2>
                        <div class="mt-2 flex items-center space-x-4">
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium
                            {{ $booking['status'] === 'confirmed'
                                ? 'bg-green-100 text-green-800'
                                : ($booking['status'] === 'pending'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-red-100 text-red-800') }}">
                                {{ $booking['status'] }}
                            </span>
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium
                            {{ $booking['payment_status'] === 'paid'
                                ? 'bg-blue-100 text-blue-800'
                                : ($booking['payment_status'] === 'pending'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-red-100 text-red-800') }}">
                                {{ $booking['payment_status'] }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        @if ($booking['status'] == 'pending')
                            <form action="#" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Konfirmasi pemesanan ini?')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                    <i class="fas fa-check mr-2"></i>
                                    Konfirmasi
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.bookings.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Booking Details -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Booking Info -->
                    <div class="lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pemesanan</h3>

                        <div class="bg-gray-50 rounded-lg p-6 space-y-6">
                            <!-- Schedule Info -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Jadwal Perjalanan</h4>
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-route text-blue-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-lg font-medium text-gray-900">
                                            {{ $booking['departure_city'] }} → {{ $booking['arrival_city'] }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $booking['bus_name'] }} ({{ $booking['bus_number'] }})
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm text-gray-600">Keberangkatan</div>
                                        <div class="font-medium">{{ $booking['departure_time'] }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600">Kedatangan</div>
                                        <div class="font-medium">{{ $booking['arrival_time'] }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Passenger Info -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Penumpang</h4>
                                <div class="space-y-3">
                                    <div class="flex">
                                        <div class="w-1/3 text-gray-600">Nama Pemesan:</div>
                                        <div class="w-2/3 font-medium">{{ $booking['passenger_name'] }}</div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 text-gray-600">Email:</div>
                                        <div class="w-2/3 font-medium">
                                            {{ $booking['passenger_email'] ?? 'Tidak tersedia' }}</div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 text-gray-600">Telepon:</div>
                                        <div class="w-2/3 font-medium">
                                            {{ $booking['passenger_phone'] ?? 'Tidak tersedia' }}</div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 text-gray-600">Jumlah Penumpang:</div>
                                        <div class="w-2/3 font-medium">{{ $booking['seat_count'] }} orang</div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 text-gray-600">Kursi:</div>
                                        <div class="w-2/3 font-medium">{{ implode(', ', $booking['seats']) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Info -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Pembayaran</h4>
                                <div class="space-y-3">
                                    <div class="flex">
                                        <div class="w-1/3 text-gray-600">Total Harga:</div>
                                        <div class="w-2/3 font-medium text-green-600">
                                            Rp {{ number_format($booking['total_price'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 text-gray-600">Metode Pembayaran:</div>
                                        <div class="w-2/3 font-medium">{{ $booking['payment_method'] ?? 'Transfer Bank' }}
                                        </div>
                                    </div>
                                    <div class="flex">
                                        <div class="w-1/3 text-gray-600">Status Pembayaran:</div>
                                        <div class="w-2/3">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $booking['payment_status'] === 'paid'
                                                ? 'bg-blue-100 text-blue-800'
                                                : ($booking['payment_status'] === 'pending'
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : 'bg-red-100 text-red-800') }}">
                                                {{ $booking['payment_status'] }}
                                            </span>
                                        </div>
                                    </div>
                                    @if ($booking['payment_date'])
                                        <div class="flex">
                                            <div class="w-1/3 text-gray-600">Tanggal Pembayaran:</div>
                                            <div class="w-2/3 font-medium">{{ $booking['payment_date'] }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Ticket Info -->
                            @if ($booking['ticket_code'])
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Tiket</h4>
                                    <div class="space-y-3">
                                        <div class="flex">
                                            <div class="w-1/3 text-gray-600">Kode Tiket:</div>
                                            <div class="w-2/3 font-medium">{{ $booking['ticket_code'] }}</div>
                                        </div>
                                        <div class="flex">
                                            <div class="w-1/3 text-gray-600">Status Tiket:</div>
                                            <div class="w-2/3">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $booking['ticket_status'] === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : ($booking['ticket_status'] === 'used'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : 'bg-gray-100 text-gray-800') }}">
                                                    {{ $booking['ticket_status'] }}
                                                </span>
                                            </div>
                                        </div>
                                        @if ($booking['boarding_status'])
                                            <div class="flex">
                                                <div class="w-1/3 text-gray-600">Status Naik:</div>
                                                <div class="w-2/3">
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $booking['boarding_status'] === 'boarded' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        {{ $booking['boarding_status'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column - Actions & Timeline -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi & Timeline</h3>

                        <!-- Action Buttons -->
                        <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-3 mb-6">
                            @if ($booking['status'] == 'pending')
                                <form action="#" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Konfirmasi pemesanan ini?')"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Konfirmasi Pemesanan
                                    </button>
                                </form>

                                <form action="#" method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Batalkan pemesanan ini?')"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                                        <i class="fas fa-ban mr-2"></i>
                                        Batalkan Pemesanan
                                    </button>
                                </form>
                            @endif

                            @if ($booking['status'] == 'confirmed' && !$booking['ticket_code'])
                                <form action="#" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Generate tiket untuk pemesanan ini?')"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                        <i class="fas fa-ticket-alt mr-2"></i>
                                        Generate Tiket
                                    </button>
                                </form>
                            @endif

                            <a href="#"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-print mr-2"></i>
                                Cetak Tiket
                            </a>
                        </div>

                        <!-- Timeline -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Timeline</h4>
                            <div class="space-y-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <div class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center">
                                            <i class="fas fa-check text-green-600 text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Pemesanan dibuat</p>
                                        <p class="text-xs text-gray-500">{{ $booking['created_at'] }}</p>
                                    </div>
                                </div>

                                @if ($booking['payment_date'])
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center">
                                                <i class="fas fa-check text-green-600 text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Pembayaran diterima</p>
                                            <p class="text-xs text-gray-500">{{ $booking['payment_date'] }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if ($booking['status'] == 'confirmed')
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center">
                                                <i class="fas fa-check text-green-600 text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Pemesanan dikonfirmasi</p>
                                            <p class="text-xs text-gray-500">Sistem otomatis</p>
                                        </div>
                                    </div>
                                @endif

                                @if ($booking['boarding_status'] == 'boarded')
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center">
                                                <i class="fas fa-check text-green-600 text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Penumpang telah naik</p>
                                            <p class="text-xs text-gray-500">Di-scan oleh kondektur</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- QR Code (if available) -->
                        @if ($booking['ticket_code'])
                            <div class="mt-6 bg-white border border-gray-200 rounded-lg p-4 text-center">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">QR Code Tiket</h4>
                                <div class="bg-gray-100 p-4 rounded-lg inline-block">
                                    <!-- QR Code placeholder -->
                                    <div class="h-32 w-32 bg-white flex items-center justify-center mx-auto">
                                        <i class="fas fa-qrcode text-gray-400 text-4xl"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Scan untuk validasi</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                @if ($booking['notes'])
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Catatan</h3>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-sticky-note text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">{{ $booking['notes'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
