@extends('layouts.admin')

@section('title', 'Manajemen Refund')

@section('header', 'Manajemen Refund')

@section('content')
<div class="space-y-6">
    <!-- Filters & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
        <form method="GET" action="{{ route('admin.refunds.index') }}" class="w-full sm:w-1/2 md:w-1/3">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari kode booking..." 
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode Booking</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alasan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($refunds as $refund)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $refund->booking->booking_code ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $refund->user->name ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600 truncate max-w-xs block" title="{{ $refund->reason }}">{{ $refund->reason }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-purple-600">Rp {{ number_format($refund->amount, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($refund->status === 'pending')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($refund->status === 'approved')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Disetujui
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($refund->status === 'pending')
                            <div class="flex items-center space-x-3">
                                <form action="{{ route('admin.refunds.update', $refund) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="text-green-600 hover:text-green-900 font-medium" onclick="return confirm('Setujui refund ini?')">
                                        Setujui
                                    </button>
                                </form>
                                <form action="{{ route('admin.refunds.update', $refund) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium" onclick="return confirm('Tolak refund ini?')">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                            @else
                                <span class="text-gray-400">Telah {{ $refund->status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4M8 16l-4-4 4-4"/>
                                </svg>
                                <p class="text-sm">Tidak ada data refund</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($refunds->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $refunds->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
