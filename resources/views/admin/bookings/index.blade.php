@extends('layouts.admin')

@section('title', 'Kelola Pemesanan')

@section('content')
    <div class="space-y-6">
        <!-- Header with Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Daftar Pemesanan</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Kelola semua pemesanan tiket bus
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button type="button"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-download mr-2"></i>
                    Export Laporan
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Pemesanan</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $totalBookings ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Terkonfirmasi</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $confirmedBookings ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Menunggu</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $pendingBookings ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Pendapatan</h3>
                        <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="booking_code" class="block text-sm font-medium text-gray-700">Kode Booking</label>
                    <input type="text" name="booking_code" id="booking_code" value="{{ request('booking_code') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="BK-123456...">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status Pemesanan</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Terkonfirmasi
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                </div>
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700">Status Pembayaran</label>
                    <select name="payment_status" id="payment_status"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Menunggu
                        </option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Dibayar</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    @if (request()->hasAny(['booking_code', 'status', 'payment_status']))
                        <a href="{{ route('admin.bookings.index') }}"
                            class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kode Booking
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Penumpang & Rute
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Detail
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
                        @forelse($bookings as $booking)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $booking->booking_code }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->created_at->format('d M Y, H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $booking->schedule->departure_city ?? '-' }} →
                                        {{ $booking->schedule->arrival_city ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $booking->schedule->departure_time->format('d M Y, H:i') ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $booking->total_passengers }} kursi</div>
                                    <div class="text-sm font-medium text-green-600">
                                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        {{ $booking->booking_status === 'confirmed'
                            ? 'bg-green-100 text-green-800'
                            : ($booking->booking_status === 'pending'
                                ? 'bg-yellow-100 text-yellow-800'
                                : 'bg-red-100 text-red-800') }}">
                                            {{ $booking->booking_status }}
                                        </span>
                                        <div>
                                            <span
                                                class="text-xs px-2 inline-flex leading-5 font-semibold rounded-full
                            {{ $booking->payment_status === 'paid'
                                ? 'bg-blue-100 text-blue-800'
                                : ($booking->payment_status === 'pending'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-red-100 text-red-800') }}">
                                                {{ $booking->payment_status }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                            class="text-blue-600 hover:text-blue-900" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($booking->booking_status == 'pending')
                                            <form action="{{ route('admin.bookings.confirm', $booking->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit"
                                                    onclick="return confirm('Konfirmasi pemesanan ini?')"
                                                    class="text-green-600 hover:text-green-900" title="Konfirmasi">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($booking->booking_status != 'cancelled')
                                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" onclick="return confirm('Batalkan pemesanan ini?')"
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
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-ticket-alt text-gray-400 text-4xl mb-3"></i>
                                        <p class="text-gray-500">Tidak ada data pemesanan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($bookings->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
