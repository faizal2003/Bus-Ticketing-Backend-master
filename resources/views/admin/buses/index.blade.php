@extends('layouts.admin')

@section('title', 'Manajemen Bus')

@section('content')
    <div class="space-y-6">
        <!-- Header with Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Manajemen Bus</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Kelola data bus dan armada transportasi
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('admin.buses.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Tambah Bus Baru
                </a>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()"
                            class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600">
                            <i class="fas fa-times h-4 w-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()"
                            class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600">
                            <i class="fas fa-times h-4 w-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4">
            <form method="GET" action="{{ route('admin.buses.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Cari Bus</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Nama bus atau plat nomor...">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Tipe Bus</label>
                    <select name="type" id="type"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Semua Tipe</option>
                        <option value="reguler" {{ request('type') == 'reguler' ? 'selected' : '' }}>Reguler</option>
                        <option value="premium" {{ request('type') == 'premium' ? 'selected' : '' }}>Premium</option>
                        <option value="vip" {{ request('type') == 'vip' ? 'selected' : '' }}>VIP</option>
                        <option value="bisnis" {{ request('type') == 'bisnis' ? 'selected' : '' }}>Bisnis</option>
                        <option value="executive" {{ request('type') == 'executive' ? 'selected' : '' }}>Executive</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance
                        </option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'type', 'status']))
                        <a href="{{ route('admin.buses.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Buses Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Informasi Bus
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Spesifikasi
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
                        @forelse($buses as $bus)
                            @php
                                $typeColors = [
                                    'reguler' => 'bg-gray-100 text-gray-800',
                                    'premium' => 'bg-blue-100 text-blue-800',
                                    'vip' => 'bg-yellow-100 text-yellow-800',
                                    'bisnis' => 'bg-purple-100 text-purple-800',
                                    'executive' => 'bg-indigo-100 text-indigo-800',
                                ];

                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'inactive' => 'bg-red-100 text-red-800',
                                    'maintenance' => 'bg-yellow-100 text-yellow-800',
                                ];

                                $busType = $bus->bus_type ?? 'reguler';
                                $busStatus = $bus->status ?? 'inactive';
                            @endphp
                            <tr>
                                <!-- Informasi Bus -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-bus text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $bus->bus_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <i class="fas fa-car-alt mr-1"></i>
                                                {{ $bus->plate_number }}
                                            </div>
                                            @if ($bus->facilities)
                                                <div class="mt-1">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                        <i class="fas fa-tools mr-1"></i>
                                                        @if (is_array($bus->facilities))
                                                            {{ implode(', ', array_slice($bus->facilities, 0, 2)) }}
                                                            @if (count($bus->facilities) > 2)
                                                                +{{ count($bus->facilities) - 2 }} lebih
                                                            @endif
                                                        @else
                                                            {{ Str::limit($bus->facilities, 30) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Spesifikasi -->
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center text-sm text-gray-900">
                                            <i class="fas fa-users mr-2 text-gray-400"></i>
                                            <span class="font-medium">{{ $bus->total_seats }}</span>
                                            <span class="ml-1 text-gray-500">kursi</span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-900">
                                            <i class="fas fa-bus mr-2 text-gray-400"></i>
                                            <span class="font-medium capitalize">{{ $busType }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <i class="far fa-clock mr-1"></i>
                                            {{ $bus->created_at->format('d M Y') }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$busStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                        <i
                                            class="fas fa-circle mr-1 text-xs {{ $busStatus == 'active' ? 'text-green-500' : ($busStatus == 'maintenance' ? 'text-yellow-500' : 'text-red-500') }}"></i>
                                        {{ ucfirst($busStatus) }}
                                    </span>
                                    <div class="mt-2 text-xs text-gray-500">
                                        @if ($bus->schedules_count ?? 0 > 0)
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            {{ $bus->schedules_count ?? 0 }} jadwal aktif
                                        @else
                                            <i class="fas fa-calendar-times mr-1"></i>
                                            Belum ada jadwal
                                        @endif
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <!-- Detail Button -->
                                        <a href="{{ route('admin.buses.show', $bus->id) }}"
                                            class="text-blue-600 hover:text-blue-900" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Edit Button -->
                                        <a href="{{ route('admin.buses.edit', $bus->id) }}"
                                            class="text-green-600 hover:text-green-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Toggle Status Button -->
                                        <form action="{{ route('admin.buses.toggle-status', $bus->id) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin {{ $busStatus == 'active' ? 'menonaktifkan' : 'mengaktifkan' }} bus ini?')">
                                            @csrf
                                            <!-- HAPUS baris @method('PATCH') -->
                                            <button type="submit" ...>
                                                <i
                                                    class="fas fa-{{ $busStatus == 'active' ? 'ban' : 'check-circle' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.buses.destroy', $bus->id) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus bus ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="text-center">
                                        <i class="fas fa-bus text-gray-400 text-4xl mb-3"></i>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada bus</h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Mulai dengan menambahkan bus baru ke sistem.
                                        </p>
                                        <div class="mt-6">
                                            <a href="{{ route('admin.buses.create') }}"
                                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                Tambah Bus Pertama
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($buses->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $buses->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="busDetailModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Detail Bus
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div id="busDetailContent">
                                    <!-- Detail content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showBusDetail(busId) {
            fetch(`/admin/buses/${busId}/detail`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('busDetailContent').innerHTML = html;
                    document.getElementById('busDetailModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function closeModal() {
            document.getElementById('busDetailModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('busDetailModal').addEventListener('click', function(e) {
            if (e.target.id === 'busDetailModal') {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
@endpush
