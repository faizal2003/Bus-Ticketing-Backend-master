@extends('layouts.admin')

@section('title', 'Edit Data Bus')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Edit Bus: {{ $bus->bus_name }}</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Perbarui informasi bus di bawah ini
                </p>
            </div>

            <form action="{{ route('admin.buses.update', $bus) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bus Name -->
                    <div>
                        <label for="bus_name" class="block text-sm font-medium text-gray-700">
                            Nama Bus <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="bus_name" id="bus_name" value="{{ old('bus_name', $bus->bus_name) }}"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('bus_name') border-red-500 @enderror"
                            required>
                        @error('bus_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bus Number -->
                    <div>
                        <label for="bus_number" class="block text-sm font-medium text-gray-700">
                            Nomor Bus <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="bus_number" id="bus_number"
                            value="{{ old('bus_number', $bus->bus_number) }}"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('bus_number') border-red-500 @enderror"
                            required>
                        @error('bus_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Plate Number -->
                    <div>
                        <label for="plate_number" class="block text-sm font-medium text-gray-700">
                            Plat Nomor <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="plate_number" id="plate_number"
                            value="{{ old('plate_number', $bus->plate_number) }}"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('plate_number') border-red-500 @enderror"
                            required>
                        @error('plate_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bus Type -->
                    <div>
                        <label for="bus_type" class="block text-sm font-medium text-gray-700">
                            Tipe Bus <span class="text-red-500">*</span>
                        </label>
                        <select name="bus_type" id="bus_type"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('bus_type') border-red-500 @enderror"
                            required>
                            <option value="">Pilih Tipe Bus</option>
                            <option value="Regular" {{ old('bus_type', $bus->bus_type) == 'Regular' ? 'selected' : '' }}>
                                Reguler</option>
                            <option value="Executive"
                                {{ old('bus_type', $bus->bus_type) == 'Executive' ? 'selected' : '' }}>Eksekutif</option>
                            <option value="VIP" {{ old('bus_type', $bus->bus_type) == 'VIP' ? 'selected' : '' }}>VIP
                            </option>
                            <option value="Super" {{ old('bus_type', $bus->bus_type) == 'Super' ? 'selected' : '' }}>
                                Super</option>
                        </select>
                        @error('bus_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Total Seats -->
                    <div>
                        <label for="total_seats" class="block text-sm font-medium text-gray-700">
                            Jumlah Kursi <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_seats" id="total_seats"
                            value="{{ old('total_seats', $bus->total_seats) }}" min="1" max="100"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('total_seats') border-red-500 @enderror"
                            required>
                        @error('total_seats')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('status') border-red-500 @enderror"
                            required>
                            <option value="">Pilih Status</option>
                            <option value="active" {{ old('status', $bus->status) == 'active' ? 'selected' : '' }}>Aktif
                            </option>
                            <option value="maintenance"
                                {{ old('status', $bus->status) == 'maintenance' ? 'selected' : '' }}>Perawatan</option>
                            <option value="inactive" {{ old('status', $bus->status) == 'inactive' ? 'selected' : '' }}>
                                Non-Aktif</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Facilities -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fasilitas
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php
                            $facilities = [
                                'AC',
                                'Toilet',
                                'TV',
                                'WiFi',
                                'Snack',
                                'Selimut',
                                'Bantal',
                                'USB Charger',
                                'Water Dispenser',
                                'Bagasi Besar',
                            ];
                            $selectedFacilities = old('facilities', $bus->facilities ?? []);
                        @endphp
                        @foreach ($facilities as $facility)
                            <div class="flex items-center">
                                <input type="checkbox" name="facilities[]" id="facility_{{ $loop->index }}"
                                    value="{{ $facility }}"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    {{ is_array($selectedFacilities) && in_array($facility, $selectedFacilities) ? 'checked' : '' }}>
                                <label for="facility_{{ $loop->index }}" class="ml-2 text-sm text-gray-700">
                                    {{ $facility }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('facilities')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.buses.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
