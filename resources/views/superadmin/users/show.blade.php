@extends('layouts.superadmin')

@section('title', 'Detail Pengguna')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail Pengguna</h2>
                <p class="text-gray-600">Informasi lengkap pengguna</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('superadmin.users.edit', $user->id) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                <a href="{{ route('superadmin.users.index') }}"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>

        <!-- User Info Card -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center mb-6">
                    <div
                        class="h-20 w-20 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="ml-6">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <div class="flex items-center mt-2 space-x-4">
                            <span
                                class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            {{ $user->role == 'super_admin'
                                ? 'bg-purple-100 text-purple-800'
                                : ($user->role == 'admin'
                                    ? 'bg-blue-100 text-blue-800'
                                    : ($user->role == 'kondektur'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-green-100 text-green-800')) }}">
                                {{ $user->role_name }}
                            </span>
                            <span
                                class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pribadi</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Nama Lengkap</span>
                                <span class="font-medium">{{ $user->name }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Email</span>
                                <span class="font-medium">{{ $user->email }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Nomor Telepon</span>
                                <span class="font-medium">{{ $user->phone ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">ID Pengguna</span>
                                <span class="font-medium text-gray-500">#{{ $user->id }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Akun</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Role</span>
                                <span class="font-medium">{{ $user->role_name }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Status Akun</span>
                                <span class="font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-600">Email Terverifikasi</span>
                                <span
                                    class="font-medium {{ $user->email_verified_at ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $user->email_verified_at ? 'Ya' : 'Belum' }}
                                </span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">Terdaftar Sejak</span>
                                <span class="font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-gray-900">0</div>
                            <div class="text-sm text-gray-600">Total Booking</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-gray-900">0</div>
                            <div class="text-sm text-gray-600">Booking Aktif</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-gray-900">Rp 0</div>
                            <div class="text-sm text-gray-600">Total Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                <div>
                    <p class="text-sm text-gray-600">
                        Terakhir diperbarui: {{ $user->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    @if ($user->id !== auth()->id())
                        <form action="{{ route('superadmin.users.toggle-status', $user->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors flex items-center">
                                @if ($user->is_active)
                                    <i class="fas fa-user-slash mr-2"></i> Nonaktifkan
                                @else
                                    <i class="fas fa-user-check mr-2"></i> Aktifkan
                                @endif
                            </button>
                        </form>

                        <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center">
                                <i class="fas fa-trash mr-2"></i> Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
