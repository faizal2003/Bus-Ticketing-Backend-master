@extends('layouts.admin')

@section('title', 'Detail Bus: ' . $bus->bus_name)

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Bus Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $bus->bus_name }}</h2>
                        <div class="mt-2 flex items-center space-x-4">
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium
                            {{ $bus->status == 'active'
                                ? 'bg-green-100 text-green-800'
                                : ($bus->status == 'maintenance'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-red-100 text-red-800') }}">
                                {{ $bus->status == 'active' ? 'Aktif' : ($bus->status == 'maintenance' ? 'Sedang Perawatan' : 'Non-Aktif') }}
                            </span>
                            <span class="text-gray-600">
                                <i class="fas fa-bus mr-1"></i> {{ $bus->bus_type }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.buses.edit', $bus) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            <i class="fas fa-edit mr-2"></i>
                            Edit
                        </a>
                        <a href="{{ route('admin.buses.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Bus Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Bus</h3>

                        <div class="space-y-4">
                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Nomor Bus:</div>
                                <div class="w-2/3 font-medium">{{ $bus->bus_number }}</div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Plat Nomor:</div>
                                <div class="w-2/3 font-medium font-mono">{{ $bus->plate_number }}</div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Tipe Bus:</div>
                                <div class="w-2/3 font-medium">{{ $bus->bus_type }}</div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Jumlah Kursi:</div>
                                <div class="w-2/3 font-medium">{{ $bus->total_seats }} kursi</div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Status:</div>
                                <div class="w-2/3">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $bus->status == 'active'
                                        ? 'bg-green-100 text-green-800'
                                        : ($bus->status == 'maintenance'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-red-100 text-red-800') }}">
                                        {{ $bus->status == 'active' ? 'Aktif' : ($bus->status == 'maintenance' ? 'Sedang Perawatan' : 'Non-Aktif') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex">
                                <div class="w-1/3 text-gray-600">Tanggal Ditambahkan:</div>
                                <div class="w-2/3 font-medium">{{ $bus->created_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>

                        <!-- Facilities -->
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Fasilitas</h3>
                            @if ($bus->facilities && is_array($bus->facilities) && count($bus->facilities) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($bus->facilities as $facility)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ $facility }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">Tidak ada fasilitas khusus</p>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column - Bus Seats -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Denah Kursi</h3>

                        <!-- Seat Legend -->
                        <div class="mb-6 bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Keterangan:</h4>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <div
                                        class="w-6 h-6 bg-green-100 border border-green-300 rounded flex items-center justify-center mr-2">
                                        <i class="fas fa-chair text-green-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-600">Tersedia</span>
                                </div>
                                <div class="flex items-center">
                                    <div
                                        class="w-6 h-6 bg-red-100 border border-red-300 rounded flex items-center justify-center mr-2">
                                        <i class="fas fa-chair text-red-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-600">Terisi</span>
                                </div>
                                <div class="flex items-center">
                                    <div
                                        class="w-6 h-6 bg-blue-100 border border-blue-300 rounded flex items-center justify-center mr-2">
                                        <i class="fas fa-wheelchair text-blue-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm text-gray-600">Special</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bus Layout -->
                        <div class="bg-gray-100 rounded-lg p-6">
                            <!-- Driver Area -->
                            <div class="mb-8">
                                <div class="bg-gray-800 text-white text-center py-2 rounded-lg mb-4">
                                    <i class="fas fa-steering-wheel mr-2"></i> Driver
                                </div>
                            </div>

                            <!-- Seats Grid -->
                            <div class="grid grid-cols-4 gap-4">
                                @php
                                    // Simulate seats - in real app, get from database
                                    $seats = [];
                                    $rows = ceil($bus->total_seats / 4);
                                    $seatLetters = ['A', 'B', 'C', 'D'];

                                    for ($row = 1; $row <= $rows; $row++) {
                                        foreach ($seatLetters as $letter) {
                                            $seatNumber = $letter . $row;
                                            $seats[] = [
                                                'number' => $seatNumber,
                                                'available' => rand(0, 1) == 1,
                                                'class' => 'regular',
                                            ];
                                        }
                                    }

                                    // Limit to total seats
                                    $seats = array_slice($seats, 0, $bus->total_seats);
                                @endphp

                                @foreach ($seats as $seat)
                                    <div class="relative">
                                        <div
                                            class="w-full h-10 rounded flex items-center justify-center
                                        {{ $seat['available'] ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300' }}
                                        {{ $seat['class'] == 'special' ? 'border-blue-300 bg-blue-100' : '' }}">
                                            <span
                                                class="text-sm font-medium
                                            {{ $seat['available'] ? 'text-green-800' : 'text-red-800' }}">
                                                {{ $seat['number'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 text-center text-sm text-gray-500">
                                Total: {{ $bus->total_seats }} kursi
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Schedules -->
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Jadwal Terbaru</h3>
                        <a href="{{ route('admin.schedules.create') }}" class="text-sm text-blue-600 hover:text-blue-800">
                            <i class="fas fa-plus-circle mr-1"></i>
                            Tambah Jadwal
                        </a>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-600 text-center py-4">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Tidak ada jadwal untuk bus ini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
