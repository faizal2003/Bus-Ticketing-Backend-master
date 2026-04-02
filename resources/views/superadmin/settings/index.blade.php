@extends('layouts.superadmin')

@section('title', 'Pengaturan Sistem')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Pengaturan Sistem</h2>
                <p class="text-gray-600">Konfigurasi dan pengaturan sistem</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showTab('general')" id="tab-general"
                    class="py-4 px-1 border-b-2 font-medium text-sm
                           {{ request('tab', 'general') == 'general'
                               ? 'border-purple-500 text-purple-600'
                               : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-cog mr-2"></i> Umum
                </button>
                <button onclick="showTab('cache')" id="tab-cache"
                    class="py-4 px-1 border-b-2 font-medium text-sm
                           {{ request('tab') == 'cache'
                               ? 'border-purple-500 text-purple-600'
                               : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-database mr-2"></i> Cache
                </button>
                <button onclick="showTab('system')" id="tab-system"
                    class="py-4 px-1 border-b-2 font-medium text-sm
                           {{ request('tab') == 'system'
                               ? 'border-purple-500 text-purple-600'
                               : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-info-circle mr-2"></i> Info Sistem
                </button>
            </nav>
        </div>

        <!-- General Settings Tab -->
        <div id="tab-content-general" class="{{ request('tab', 'general') == 'general' ? '' : 'hidden' }}">
            <form method="POST" action="{{ route('superadmin.settings.update') }}">
                @csrf

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengaturan Umum</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- App Name -->
                        <div>
                            <label for="app_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Aplikasi
                            </label>
                            <input type="text" id="app_name" name="app_name" value="{{ $settings['app_name'] }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <!-- Timezone -->
                        <div>
                            <label for="app_timezone" class="block text-sm font-medium text-gray-700 mb-2">
                                Zona Waktu
                            </label>
                            <select id="app_timezone" name="app_timezone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="Asia/Jakarta"
                                    {{ $settings['app_timezone'] == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)
                                </option>
                                <option value="Asia/Makassar"
                                    {{ $settings['app_timezone'] == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar
                                    (WITA)</option>
                                <option value="Asia/Jayapura"
                                    {{ $settings['app_timezone'] == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)
                                </option>
                                <option value="UTC" {{ $settings['app_timezone'] == 'UTC' ? 'selected' : '' }}>UTC
                                </option>
                            </select>
                        </div>

                        <!-- Locale -->
                        <div>
                            <label for="app_locale" class="block text-sm font-medium text-gray-700 mb-2">
                                Bahasa
                            </label>
                            <select id="app_locale" name="app_locale"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="id" {{ $settings['app_locale'] == 'id' ? 'selected' : '' }}>Bahasa
                                    Indonesia</option>
                                <option value="en" {{ $settings['app_locale'] == 'en' ? 'selected' : '' }}>English
                                </option>
                            </select>
                        </div>

                        <!-- Session Lifetime -->
                        <div>
                            <label for="session_lifetime" class="block text-sm font-medium text-gray-700 mb-2">
                                Masa Aktif Session (menit)
                            </label>
                            <input type="number" id="session_lifetime" name="session_lifetime"
                                value="{{ $settings['session_lifetime'] }}" min="1" max="1440"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <!-- Mail From Address -->
                        <div>
                            <label for="mail_from_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Pengirim
                            </label>
                            <input type="email" id="mail_from_address" name="mail_from_address"
                                value="{{ $settings['mail_from_address'] }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <!-- Mail From Name -->
                        <div>
                            <label for="mail_from_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Pengirim Email
                            </label>
                            <input type="text" id="mail_from_name" name="mail_from_name"
                                value="{{ $settings['mail_from_name'] }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                            class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-2 rounded-lg hover:from-purple-700 hover:to-purple-800 transition-colors">
                            <i class="fas fa-save mr-2"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cache Management Tab -->
        <div id="tab-content-cache" class="{{ request('tab') == 'cache' ? '' : 'hidden' }}">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Manajemen Cache</h3>

                <div class="space-y-4">
                    <!-- Cache Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500">Driver Cache</p>
                            <p class="text-lg font-semibold">{{ $settings['cache_driver'] }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500">Driver Queue</p>
                            <p class="text-lg font-semibold">{{ $settings['queue_driver'] }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500">Item Cache</p>
                            <p class="text-lg font-semibold">{{ $cacheInfo['cache_items'] }}</p>
                        </div>
                    </div>

                    <!-- Clear Cache Buttons -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Bersihkan Cache</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <form method="POST" action="{{ route('superadmin.settings.cache.clear') }}">
                                @csrf
                                <input type="hidden" name="type" value="all">
                                <button type="submit"
                                    class="w-full bg-red-100 text-red-700 px-4 py-3 rounded-lg hover:bg-red-200 transition-colors text-left">
                                    <i class="fas fa-broom text-lg mb-2"></i>
                                    <p class="font-semibold">Semua Cache</p>
                                    <p class="text-sm">Bersihkan semua cache</p>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('superadmin.settings.cache.clear') }}">
                                @csrf
                                <input type="hidden" name="type" value="config">
                                <button type="submit"
                                    class="w-full bg-blue-100 text-blue-700 px-4 py-3 rounded-lg hover:bg-blue-200 transition-colors text-left">
                                    <i class="fas fa-cogs text-lg mb-2"></i>
                                    <p class="font-semibold">Cache Konfig</p>
                                    <p class="text-sm">Bersihkan cache konfigurasi</p>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('superadmin.settings.cache.clear') }}">
                                @csrf
                                <input type="hidden" name="type" value="view">
                                <button type="submit"
                                    class="w-full bg-green-100 text-green-700 px-4 py-3 rounded-lg hover:bg-green-200 transition-colors text-left">
                                    <i class="fas fa-eye text-lg mb-2"></i>
                                    <p class="font-semibold">Cache View</p>
                                    <p class="text-sm">Bersihkan cache tampilan</p>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('superadmin.settings.cache.clear') }}">
                                @csrf
                                <input type="hidden" name="type" value="route">
                                <button type="submit"
                                    class="w-full bg-yellow-100 text-yellow-700 px-4 py-3 rounded-lg hover:bg-yellow-200 transition-colors text-left">
                                    <i class="fas fa-route text-lg mb-2"></i>
                                    <p class="font-semibold">Cache Route</p>
                                    <p class="text-sm">Bersihkan cache rute</p>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Maintenance Commands -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Perintah Maintenance</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <form method="POST" action="{{ route('superadmin.settings.maintenance.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="optimize">
                                <button type="submit"
                                    class="w-full bg-purple-100 text-purple-700 px-4 py-3 rounded-lg hover:bg-purple-200 transition-colors text-left">
                                    <i class="fas fa-tachometer-alt text-lg mb-2"></i>
                                    <p class="font-semibold">Optimize</p>
                                    <p class="text-sm">Optimalkan aplikasi</p>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('superadmin.settings.maintenance.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="migrate">
                                <button type="submit"
                                    class="w-full bg-indigo-100 text-indigo-700 px-4 py-3 rounded-lg hover:bg-indigo-200 transition-colors text-left">
                                    <i class="fas fa-database text-lg mb-2"></i>
                                    <p class="font-semibold">Migrate</p>
                                    <p class="text-sm">Jalankan migrasi</p>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('superadmin.settings.maintenance.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="db:seed">
                                <button type="submit"
                                    class="w-full bg-teal-100 text-teal-700 px-4 py-3 rounded-lg hover:bg-teal-200 transition-colors text-left">
                                    <i class="fas fa-seedling text-lg mb-2"></i>
                                    <p class="font-semibold">Seed</p>
                                    <p class="text-sm">Jalankan seeder</p>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('superadmin.settings.maintenance.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="storage:link">
                                <button type="submit"
                                    class="w-full bg-orange-100 text-orange-700 px-4 py-3 rounded-lg hover:bg-orange-200 transition-colors text-left">
                                    <i class="fas fa-link text-lg mb-2"></i>
                                    <p class="font-semibold">Storage Link</p>
                                    <p class="text-sm">Buat symlink storage</p>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info Tab -->
        <div id="tab-content-system" class="{{ request('tab') == 'system' ? '' : 'hidden' }}">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>

                <div class="space-y-6">
                    <!-- Server Info -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-3">Informasi Server</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">Sistem Operasi</p>
                                <p class="text-lg font-semibold">{{ $systemInfo['server_os'] }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">Web Server</p>
                                <p class="text-lg font-semibold">{{ $systemInfo['server_software'] }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">PHP Version</p>
                                <p class="text-lg font-semibold">{{ $systemInfo['php_version'] }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">Laravel Version</p>
                                <p class="text-lg font-semibold">{{ $systemInfo['laravel_version'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Database Info -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Informasi Database</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">Database Driver</p>
                                <p class="text-lg font-semibold">{{ $systemInfo['database_driver'] }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">Database Name</p>
                                <p class="text-lg font-semibold">
                                    {{ config('database.connections.' . config('database.default') . '.database') }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">Database Host</p>
                                <p class="text-lg font-semibold">
                                    {{ config('database.connections.' . config('database.default') . '.host') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Application Info -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-3">Informasi Aplikasi</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">URL Aplikasi</span>
                                <span class="font-medium">{{ $settings['app_url'] }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Environment</span>
                                <span class="font-medium">{{ app()->environment() }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Debug Mode</span>
                                <span class="font-medium {{ config('app.debug') ? 'text-green-600' : 'text-gray-600' }}">
                                    {{ config('app.debug') ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Waktu Server</span>
                                <span class="font-medium">{{ now()->format('d/m/Y H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('[id^="tab-content-"]').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Remove active class from all tabs
            document.querySelectorAll('[id^="tab-"]').forEach(tab => {
                tab.classList.remove('border-purple-500', 'text-purple-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected tab content
            document.getElementById('tab-content-' + tabName).classList.remove('hidden');

            // Add active class to selected tab
            document.getElementById('tab-' + tabName).classList.add('border-purple-500', 'text-purple-600');
            document.getElementById('tab-' + tabName).classList.remove('border-transparent', 'text-gray-500');

            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }

        // Confirm before running maintenance commands
        document.addEventListener('DOMContentLoaded', function() {
            const maintenanceForms = document.querySelectorAll('form[action*="maintenance"]');
            maintenanceForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Apakah Anda yakin ingin menjalankan perintah ini?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush
