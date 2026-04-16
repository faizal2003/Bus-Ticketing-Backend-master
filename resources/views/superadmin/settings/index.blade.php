@extends('layouts.superadmin')

@section('title', 'Konfigurasi Sistem')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Konfigurasi Sistem</h2>
                <p class="text-gray-600">Atur pengaturan global aplikasi</p>
            </div>

            <form method="POST" action="{{ route('superadmin.settings.update') }}" enctype="multipart/form-data"
                class="p-6 space-y-8">
                @csrf

                <!-- Bagian Umum -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Perusahaan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Perusahaan</label>
                            <input type="text" name="company_name"
                                value="{{ old('company_name', $settingValues['company_name'] ?? 'Bus Ticketing System') }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-blue-500">
                            @error('company_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Logo Perusahaan</label>
                            @php
                                $logo = $settingValues['company_logo'] ?? null;
                                $logoUrl =
                                    $logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo)
                                        ? \Illuminate\Support\Facades\Storage::url($logo)
                                        : null;
                            @endphp
                            @if ($logoUrl)
                                <div class="mb-2">
                                    <img src="{{ $logoUrl }}" class="h-16 w-auto object-contain">
                                </div>
                            @endif
                            <input type="file" name="company_logo" accept="image/*"
                                class="mt-1 w-full border rounded-lg px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Ukuran maksimal 2MB (JPG, PNG). Kosongkan jika tidak ingin
                                mengganti.</p>
                            @error('company_logo')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Bagian Pembayaran (Midtrans) -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Pembayaran (Midtrans)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Server Key</label>
                            <input type="text" name="midtrans_server_key"
                                value="{{ old('midtrans_server_key', $settingValues['midtrans_server_key'] ?? '') }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2">
                            @error('midtrans_server_key')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Client Key</label>
                            <input type="text" name="midtrans_client_key"
                                value="{{ old('midtrans_client_key', $settingValues['midtrans_client_key'] ?? '') }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2">
                            @error('midtrans_client_key')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Merchant ID</label>
                            <input type="text" name="midtrans_merchant_id"
                                value="{{ old('midtrans_merchant_id', $settingValues['midtrans_merchant_id'] ?? '') }}"
                                class="mt-1 w-full border rounded-lg px-3 py-2">
                            @error('midtrans_merchant_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Environment</label>
                            <select name="midtrans_environment" class="mt-1 w-full border rounded-lg px-3 py-2">
                                <option value="sandbox"
                                    {{ ($settingValues['midtrans_environment'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>
                                    Sandbox (Testing)</option>
                                <option value="production"
                                    {{ ($settingValues['midtrans_environment'] ?? '') == 'production' ? 'selected' : '' }}>
                                    Production (Live)</option>
                            </select>
                            @error('midtrans_environment')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Timeout Pembayaran (menit)</label>
                            <input type="number" name="payment_timeout"
                                value="{{ old('payment_timeout', $settingValues['payment_timeout'] ?? 60) }}"
                                min="1" max="1440" class="mt-1 w-full border rounded-lg px-3 py-2">
                            @error('payment_timeout')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Bagian QR Code -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan QR Code</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ukuran QR (px)</label>
                            <input type="number" name="qr_size"
                                value="{{ old('qr_size', $settingValues['qr_size'] ?? 300) }}" min="100"
                                max="800" class="mt-1 w-full border rounded-lg px-3 py-2">
                            @error('qr_size')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Margin (px)</label>
                            <input type="number" name="qr_margin"
                                value="{{ old('qr_margin', $settingValues['qr_margin'] ?? 10) }}" min="0"
                                max="50" class="mt-1 w-full border rounded-lg px-3 py-2">
                            @error('qr_margin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Error Correction</label>
                            <select name="qr_error_correction" class="mt-1 w-full border rounded-lg px-3 py-2">
                                <option value="L"
                                    {{ ($settingValues['qr_error_correction'] ?? 'M') == 'L' ? 'selected' : '' }}>L - 7%
                                </option>
                                <option value="M"
                                    {{ ($settingValues['qr_error_correction'] ?? 'M') == 'M' ? 'selected' : '' }}>M - 15%
                                </option>
                                <option value="Q"
                                    {{ ($settingValues['qr_error_correction'] ?? 'M') == 'Q' ? 'selected' : '' }}>Q - 25%
                                </option>
                                <option value="H"
                                    {{ ($settingValues['qr_error_correction'] ?? 'M') == 'H' ? 'selected' : '' }}>H - 30%
                                </option>
                            </select>
                            @error('qr_error_correction')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Bagian Format Tiket -->
                <div class="pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Format Tiket (Template)</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Template Tiket</label>
                        <textarea name="ticket_format" rows="6" class="mt-1 w-full border rounded-lg px-3 py-2 font-mono text-sm">{{ old('ticket_format', $settingValues['ticket_format'] ?? '') }}</textarea>
                        @error('ticket_format')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i> Simpan Konfigurasi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
