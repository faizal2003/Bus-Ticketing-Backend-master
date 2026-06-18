@extends('layouts.admin')

@section('title', 'Buat Pemesanan Manual')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Buat Pemesanan Manual</h2>
                        <p class="text-sm text-gray-600 mt-1">Untuk pemesanan walk-in atau telepon</p>
                    </div>
                    <a href="{{ route('admin.bookings.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.bookings.store') }}" method="POST" class="p-6">
                @csrf

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Terdapat {{ $errors->count() }} kesalahan:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Schedule Selection -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">1. Pilih Jadwal</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label for="schedule_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Jadwal Bus <span class="text-red-500">*</span>
                        </label>
                        <select name="schedule_id" id="schedule_id" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="updateScheduleInfo()">
                            <option value="">Pilih Jadwal</option>
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->id }}" 
                                    data-route="{{ $schedule->departure_city }} → {{ $schedule->arrival_city }}"
                                    data-bus="{{ $schedule->bus->bus_name }}"
                                    data-departure="{{ $schedule->departure_time->format('d M Y, H:i') }}"
                                    data-price="{{ $schedule->price_per_seat }}"
                                    data-available="{{ $schedule->available_seats }}"
                                    {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                    {{ $schedule->departure_city }} → {{ $schedule->arrival_city }} | 
                                    {{ $schedule->bus->bus_name }} | 
                                    {{ $schedule->departure_time->format('d M Y, H:i') }} | 
                                    ({{ $schedule->available_seats }} kursi tersedia)
                                </option>
                            @endforeach
                        </select>

                        <div id="schedule-info" class="mt-3 hidden">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Rute:</span>
                                    <span id="info-route" class="font-medium ml-2"></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Bus:</span>
                                    <span id="info-bus" class="font-medium ml-2"></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Keberangkatan:</span>
                                    <span id="info-departure" class="font-medium ml-2"></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Harga/Kursi:</span>
                                    <span id="info-price" class="font-medium ml-2"></span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Kursi Tersedia:</span>
                                    <span id="info-available" class="font-medium ml-2"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">2. Informasi Pelanggan</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_name" id="customer_name" required
                                    value="{{ old('customer_name') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Nama lengkap pelanggan">
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nomor Telepon <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_phone" id="customer_phone" required
                                    value="{{ old('customer_phone') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="08xxxxxxxxxx">
                            </div>

                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email (Opsional)
                                </label>
                                <input type="email" name="customer_email" id="customer_email"
                                    value="{{ old('customer_email') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="email@example.com">
                            </div>

                            <div>
                                <label for="id_number" class="block text-sm font-medium text-gray-700 mb-1">
                                    No. KTP/Identitas (Opsional)
                                </label>
                                <input type="text" name="id_number" id="id_number"
                                    value="{{ old('id_number') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="3201xxxxxxxxxx">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Passenger & Seats -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">3. Jumlah Penumpang & Kursi</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <div>
                            <label for="total_passengers" class="block text-sm font-medium text-gray-700 mb-1">
                                Jumlah Penumpang <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="total_passengers" id="total_passengers" required
                                value="{{ old('total_passengers', 1) }}" min="1" max="10"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                onchange="updateSeatsInput()">
                            <p class="text-xs text-gray-500 mt-1">Masukkan jumlah penumpang (1-10 orang)</p>
                        </div>

                        <div id="seats-container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Kursi <span class="text-red-500">*</span>
                            </label>
                            <div id="seat-inputs" class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <input type="text" name="seats[]" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="A1">
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Format: A1, A2, B1, B2, dst. (Huruf = Baris, Angka = Nomor)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">4. Metode Pembayaran</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Metode <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method" value="cash" checked
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">
                                        <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                        Tunai/Cash
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method" value="bank_transfer"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">
                                        <i class="fas fa-university text-blue-600 mr-1"></i>
                                        Transfer Bank
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="auto_confirm" value="1" checked
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">
                                    Konfirmasi pembayaran otomatis (langsung generate tiket)
                                </span>
                            </label>
                            <p class="text-xs text-gray-500 ml-6 mt-1">
                                Centang jika pelanggan sudah membayar tunai
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Catatan tambahan untuk pemesanan ini">{{ old('notes') }}</textarea>
                </div>

                <!-- Price Summary -->
                <div class="mb-6 bg-blue-50 rounded-lg p-4" id="price-summary" style="display: none;">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Ringkasan Harga</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal (<span id="summary-passengers">1</span> penumpang):</span>
                            <span id="summary-subtotal" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Pajak (10%):</span>
                            <span id="summary-tax" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Biaya Layanan:</span>
                            <span class="font-medium">Rp 5.000</span>
                        </div>
                        <div class="border-t border-blue-200 pt-2 flex justify-between">
                            <span class="font-semibold text-gray-800">Total:</span>
                            <span id="summary-total" class="font-bold text-blue-600">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.bookings.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Buat Pemesanan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateScheduleInfo() {
            const select = document.getElementById('schedule_id');
            const option = select.options[select.selectedIndex];
            const infoDiv = document.getElementById('schedule-info');

            if (option.value) {
                document.getElementById('info-route').textContent = option.dataset.route;
                document.getElementById('info-bus').textContent = option.dataset.bus;
                document.getElementById('info-departure').textContent = option.dataset.departure;
                document.getElementById('info-price').textContent = 'Rp ' + parseInt(option.dataset.price).toLocaleString('id-ID');
                document.getElementById('info-available').textContent = option.dataset.available + ' kursi';
                infoDiv.classList.remove('hidden');
                
                updatePriceSummary();
            } else {
                infoDiv.classList.add('hidden');
            }
        }

        function updateSeatsInput() {
            const count = parseInt(document.getElementById('total_passengers').value) || 1;
            const container = document.getElementById('seat-inputs');
            container.innerHTML = '';

            for (let i = 0; i < count; i++) {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'seats[]';
                input.required = true;
                input.className = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500';
                input.placeholder = String.fromCharCode(65 + Math.floor(i / 4)) + (i % 4 + 1);
                container.appendChild(input);
            }

            updatePriceSummary();
        }

        function updatePriceSummary() {
            const select = document.getElementById('schedule_id');
            const option = select.options[select.selectedIndex];
            const passengers = parseInt(document.getElementById('total_passengers').value) || 1;
            const summaryDiv = document.getElementById('price-summary');

            if (option.value) {
                const pricePerSeat = parseInt(option.dataset.price);
                const subtotal = pricePerSeat * passengers;
                const tax = subtotal * 0.1;
                const serviceFee = 5000;
                const total = subtotal + tax + serviceFee;

                document.getElementById('summary-passengers').textContent = passengers;
                document.getElementById('summary-subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
                document.getElementById('summary-tax').textContent = 'Rp ' + Math.round(tax).toLocaleString('id-ID');
                document.getElementById('summary-total').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
                
                summaryDiv.style.display = 'block';
            } else {
                summaryDiv.style.display = 'none';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateScheduleInfo();
            updateSeatsInput();
        });
    </script>
@endsection
