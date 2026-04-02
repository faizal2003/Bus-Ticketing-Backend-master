@extends('layouts.admin')

@section('title', 'Edit Jadwal')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Edit Jadwal</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Perbarui informasi jadwal di bawah ini
                </p>
            </div>

            <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bus Selection -->
                    <div class="md:col-span-2">
                        <label for="bus_id" class="block text-sm font-medium text-gray-700">
                            Pilih Bus <span class="text-red-500">*</span>
                        </label>
                        <select name="bus_id" id="bus_id"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('bus_id') border-red-500 @enderror"
                            required>
                            <option value="">-- Pilih Bus --</option>
                            @foreach ($buses ?? [] as $bus)
                                <option value="{{ $bus->id }}"
                                    {{ old('bus_id', $schedule->bus_id) == $bus->id ? 'selected' : '' }}>
                                    {{ $bus->bus_name }} ({{ $bus->bus_number }}) - {{ $bus->bus_type }} -
                                    {{ $bus->total_seats }} kursi
                                </option>
                            @endforeach
                        </select>
                        @error('bus_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Departure City -->
                    <div>
                        <label for="departure_city" class="block text-sm font-medium text-gray-700">
                            Kota Keberangkatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="departure_city" id="departure_city"
                            value="{{ old('departure_city', $schedule->departure_city) }}"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('departure_city') border-red-500 @enderror"
                            required>
                        @error('departure_city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Arrival City -->
                    <div>
                        <label for="arrival_city" class="block text-sm font-medium text-gray-700">
                            Kota Tujuan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="arrival_city" id="arrival_city"
                            value="{{ old('arrival_city', $schedule->arrival_city) }}"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('arrival_city') border-red-500 @enderror"
                            required>
                        @error('arrival_city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Departure Time -->
                    <div>
                        <label for="departure_time" class="block text-sm font-medium text-gray-700">
                            Waktu Keberangkatan <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="departure_time" id="departure_time"
                            value="{{ old('departure_time', date('Y-m-d\TH:i', strtotime($schedule->departure_time))) }}"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('departure_time') border-red-500 @enderror"
                            required>
                        @error('departure_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Arrival Time -->
                    <div>
                        <label for="arrival_time" class="block text-sm font-medium text-gray-700">
                            Waktu Tiba <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="arrival_time" id="arrival_time"
                            value="{{ old('arrival_time', date('Y-m-d\TH:i', strtotime($schedule->arrival_time))) }}"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('arrival_time') border-red-500 @enderror"
                            required>
                        @error('arrival_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price Per Seat -->
                    <div>
                        <label for="price_per_seat" class="block text-sm font-medium text-gray-700">
                            Harga per Kursi <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="price_per_seat" id="price_per_seat"
                                value="{{ old('price_per_seat', $schedule->price_per_seat) }}" min="10000"
                                step="1000"
                                class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md @error('price_per_seat') border-red-500 @enderror"
                                required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">IDR</span>
                            </div>
                        </div>
                        @error('price_per_seat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Available Seats -->
                    <div>
                        <label for="available_seats" class="block text-sm font-medium text-gray-700">
                            Kursi Tersedia <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="available_seats" id="available_seats"
                            value="{{ old('available_seats', $schedule->available_seats) }}" min="0" max="100"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('available_seats') border-red-500 @enderror"
                            required>
                        @error('available_seats')
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
                            <option value="active" {{ old('status', $schedule->status) == 'active' ? 'selected' : '' }}>
                                Aktif</option>
                            <option value="pending" {{ old('status', $schedule->status) == 'pending' ? 'selected' : '' }}>
                                Menunggu</option>
                            <option value="cancelled"
                                {{ old('status', $schedule->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            <option value="completed"
                                {{ old('status', $schedule->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Catatan Tambahan (Opsional)
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes', $schedule->notes) }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.schedules.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-save mr-2"></i>
                        Perbarui Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
