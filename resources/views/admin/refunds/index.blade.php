@extends('layouts.admin')

@section('title', 'Manajemen Refund')

@section('header', 'Manajemen Refund')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Refund</p>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $statusCounts['all'] }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4M8 16l-4-4 4-4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Menunggu</p>
                    <h3 class="text-2xl font-bold text-yellow-600 mt-1">{{ $statusCounts['pending'] }}</h3>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Disetujui</p>
                    <h3 class="text-2xl font-bold text-green-600 mt-1">{{ $statusCounts['approved'] }}</h3>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Ditolak</p>
                    <h3 class="text-2xl font-bold text-red-600 mt-1">{{ $statusCounts['rejected'] }}</h3>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 gap-4">
        <form method="GET" action="{{ route('admin.refunds.index') }}" class="w-full sm:w-1/2 md:w-1/3">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari kode booking atau nama user..." 
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </form>

        <!-- Status Filter -->
        <div class="flex gap-2">
            <a href="{{ route('admin.refunds.index') }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium {{ !request('status') ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-200 transition-colors">
                Semua
            </a>
            <a href="{{ route('admin.refunds.index', ['status' => 'pending']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium {{ request('status') == 'pending' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-200 transition-colors">
                Pending
            </a>
            <a href="{{ route('admin.refunds.index', ['status' => 'approved']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium {{ request('status') == 'approved' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-200 transition-colors">
                Disetujui
            </a>
            <a href="{{ route('admin.refunds.index', ['status' => 'rejected']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium {{ request('status') == 'rejected' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-200 transition-colors">
                Ditolak
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode Booking</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rute</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alasan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
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
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $refund->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $refund->user->email ?? '-' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <p class="text-gray-900 font-medium">{{ $refund->booking->schedule->departure_city ?? '-' }}</p>
                                <p class="text-gray-500">→ {{ $refund->booking->schedule->arrival_city ?? '-' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600 truncate max-w-xs block" title="{{ $refund->reason }}">{{ Str::limit($refund->reason, 50) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-purple-600">Rp {{ number_format($refund->amount, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($refund->status === 'pending')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Menunggu
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
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">
                                <p>{{ $refund->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $refund->created_at->format('H:i') }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center space-x-2">
                                <button 
                                    onclick="openRefundModal({{ $refund->id }}, '{{ $refund->booking->booking_code }}', {{ $refund->amount }}, '{{ addslashes($refund->reason) }}')"
                                    class="text-blue-600 hover:text-blue-900 font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                @if($refund->status === 'pending')
                                <button 
                                    onclick="openProcessModal({{ $refund->id }}, 'approved')"
                                    class="text-green-600 hover:text-green-900 font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <button 
                                    onclick="openProcessModal({{ $refund->id }}, 'rejected')"
                                    class="text-red-600 hover:text-red-900 font-medium">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                @else
                                    <span class="text-gray-400 text-xs">{{ $refund->status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
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

<!-- Refund Detail Modal -->
<div id="refundModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Detail Refund</h3>
                <button onclick="closeRefundModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-500">Kode Booking</label>
                <p id="modalBookingCode" class="text-lg font-semibold text-gray-900 mt-1"></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Nominal Refund</label>
                <p id="modalAmount" class="text-lg font-semibold text-purple-600 mt-1"></p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-500">Alasan Pembatalan</label>
                <p id="modalReason" class="text-gray-900 mt-1"></p>
            </div>
        </div>
    </div>
</div>

<!-- Process Refund Modal -->
<div id="processModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <h3 id="processModalTitle" class="text-xl font-bold text-gray-900">Proses Refund</h3>
        </div>
        <form id="processForm" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <input type="hidden" name="status" id="processStatus">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Admin <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="admin_notes" 
                        rows="4" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="Masukkan catatan atau alasan keputusan..."></textarea>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 flex gap-3">
                <button type="button" onclick="closeProcessModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" id="processSubmitBtn" class="flex-1 px-4 py-2 rounded-lg text-white font-medium">
                    Proses
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRefundModal(id, bookingCode, amount, reason) {
    document.getElementById('modalBookingCode').textContent = bookingCode;
    document.getElementById('modalAmount').textContent = 'Rp ' + amount.toLocaleString('id-ID');
    document.getElementById('modalReason').textContent = reason;
    document.getElementById('refundModal').classList.remove('hidden');
}

function closeRefundModal() {
    document.getElementById('refundModal').classList.add('hidden');
}

function openProcessModal(refundId, status) {
    const modal = document.getElementById('processModal');
    const form = document.getElementById('processForm');
    const title = document.getElementById('processModalTitle');
    const submitBtn = document.getElementById('processSubmitBtn');
    const statusInput = document.getElementById('processStatus');
    
    form.action = `/admin/refunds/${refundId}`;
    statusInput.value = status;
    
    if (status === 'approved') {
        title.textContent = 'Setujui Refund';
        submitBtn.textContent = 'Setujui';
        submitBtn.className = 'flex-1 px-4 py-2 rounded-lg text-white font-medium bg-green-600 hover:bg-green-700';
    } else {
        title.textContent = 'Tolak Refund';
        submitBtn.textContent = 'Tolak';
        submitBtn.className = 'flex-1 px-4 py-2 rounded-lg text-white font-medium bg-red-600 hover:bg-red-700';
    }
    
    modal.classList.remove('hidden');
}

function closeProcessModal() {
    document.getElementById('processModal').classList.add('hidden');
    document.getElementById('processForm').reset();
}

// Close modals on outside click
document.getElementById('refundModal').addEventListener('click', function(e) {
    if (e.target === this) closeRefundModal();
});

document.getElementById('processModal').addEventListener('click', function(e) {
    if (e.target === this) closeProcessModal();
});
</script>
@endsection
