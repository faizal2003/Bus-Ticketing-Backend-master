<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', (auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin') . ' - ' . $companyName)</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('styles')
    <style>
        /* Custom Scrollbar for Sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-purple-900 to-purple-800 text-white flex flex-col h-screen">
            <div class="p-6 shrink-0">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-lg bg-white flex items-center justify-center overflow-hidden">
                        @if($companyLogo)
                            <img src="{{ $companyLogo }}" alt="{{ $companyName }}" class="h-full w-full object-cover">
                        @else
                            <i class="fas fa-bus text-purple-700 text-xl"></i>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold">{{ $companyName }}</h1>
                        <p class="text-xs text-purple-300">{{ auth()->user()->role === 'super_admin' ? 'Super Admin Panel' : 'Admin Panel' }}</p>
                    </div>
                </div>
            </div>

            <nav class="mt-6 space-y-1 flex-1 overflow-y-auto pb-4 custom-scrollbar">
                <!-- 1. Dashboard -->
                <a href="{{ auth()->user()->role === 'super_admin' ? route('superadmin.dashboard') : route('admin.dashboard') }}"
                    class="flex items-center px-6 py-3 {{ (request()->routeIs('admin.dashboard') || request()->routeIs('superadmin.dashboard')) ? 'bg-purple-700 border-r-4 border-yellow-400' : 'hover:bg-purple-700' }}">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span class="ml-3">Dashboard</span>
                </a>

                @if (auth()->user()->role === 'super_admin')
                    <!-- Super Admin Menu -->

                    <!-- 2. Manajemen Operasional -->
                    @php
                        $isOpActive = request()->routeIs('admin.buses.*') || request()->routeIs('admin.schedules.*') || request()->routeIs('superadmin.routes.*') || request()->routeIs('superadmin.drivers.*');
                    @endphp
                    <div x-data="{ open: {{ $isOpActive ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-6 py-3 text-left hover:bg-purple-700 focus:outline-none transition-colors {{ $isOpActive ? 'bg-purple-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-tasks w-6"></i>
                                <span class="ml-3">Manajemen Operasional</span>
                            </div>
                            <i class="fas fa-chevron-right text-xs transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        <div x-show="open" class="bg-purple-950/30 py-1" style="display: none;">
                            <a href="{{ route('admin.buses.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.buses.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-bus w-5 text-xs opacity-70"></i>
                                <span>Manajemen Bus</span>
                            </a>
                            <a href="{{ route('admin.schedules.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.schedules.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-calendar-alt w-5 text-xs opacity-70"></i>
                                <span>Jadwal Bus</span>
                            </a>
                            <a href="{{ route('superadmin.routes.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('superadmin.routes.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-route w-5 text-xs opacity-70"></i>
                                <span>Manajemen Rute</span>
                            </a>
                            <a href="{{ route('superadmin.drivers.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('superadmin.drivers.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-id-card w-5 text-xs opacity-70"></i>
                                <span>Manajemen Sopir</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3. Transaksi & Laporan -->
                    @php
                        $isTxActive = request()->routeIs('admin.bookings.*') || request()->routeIs('admin.refunds.*') || request()->routeIs('admin.reports.*') || request()->routeIs('superadmin.reports.*');
                    @endphp
                    <div x-data="{ open: {{ $isTxActive ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-6 py-3 text-left hover:bg-purple-700 focus:outline-none transition-colors {{ $isTxActive ? 'bg-purple-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-file-invoice-dollar w-6"></i>
                                <span class="ml-3">Transaksi & Laporan</span>
                            </div>
                            <i class="fas fa-chevron-right text-xs transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        <div x-show="open" class="bg-purple-950/30 py-1" style="display: none;">
                            <a href="{{ route('admin.bookings.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.bookings.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-ticket-alt w-5 text-xs opacity-70"></i>
                                <span>Pemesanan</span>
                            </a>
                            <a href="{{ route('admin.refunds.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.refunds.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-undo-alt w-5 text-xs opacity-70"></i>
                                <span>Refund</span>
                            </a>
                            <a href="{{ route('superadmin.reports.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('superadmin.reports.*') || request()->routeIs('admin.reports.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-chart-bar w-5 text-xs opacity-70"></i>
                                <span>Laporan</span>
                            </a>
                        </div>
                    </div>

                    <!-- 4. Manajemen Pengguna -->
                    @php
                        $isUserActive = request()->routeIs('superadmin.users.*');
                    @endphp
                    <div x-data="{ open: {{ $isUserActive ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-6 py-3 text-left hover:bg-purple-700 focus:outline-none transition-colors {{ $isUserActive ? 'bg-purple-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-users-cog w-6"></i>
                                <span class="ml-3">Manajemen Pengguna</span>
                            </div>
                            <i class="fas fa-chevron-right text-xs transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        <div x-show="open" class="bg-purple-950/30 py-1" style="display: none;">
                            <a href="{{ route('superadmin.users.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('superadmin.users.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-users w-5 text-xs opacity-70"></i>
                                <span>User Management</span>
                            </a>
                        </div>
                    </div>

                    <!-- 5. Sistem -->
                    @php
                        $isSysActive = request()->routeIs('superadmin.settings.*') || request()->routeIs('admin.profile.*');
                    @endphp
                    <div x-data="{ open: {{ $isSysActive ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-6 py-3 text-left hover:bg-purple-700 focus:outline-none transition-colors {{ $isSysActive ? 'bg-purple-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-cogs w-6"></i>
                                <span class="ml-3">Sistem</span>
                            </div>
                            <i class="fas fa-chevron-right text-xs transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        <div x-show="open" class="bg-purple-950/30 py-1" style="display: none;">
                            <a href="{{ route('superadmin.settings.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('superadmin.settings.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-cog w-5 text-xs opacity-70"></i>
                                <span>Pengaturan</span>
                            </a>
                            <a href="{{ route('admin.profile.edit') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.profile.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-user-cog w-5 text-xs opacity-70"></i>
                                <span>Profil</span>
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Admin Menu -->

                    <!-- 2. Manajemen Operasional -->
                    @php
                        $isOpActive = request()->routeIs('admin.buses.*') || request()->routeIs('admin.schedules.*');
                    @endphp
                    <div x-data="{ open: {{ $isOpActive ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-6 py-3 text-left hover:bg-purple-700 focus:outline-none transition-colors {{ $isOpActive ? 'bg-purple-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-tasks w-6"></i>
                                <span class="ml-3">Manajemen Operasional</span>
                            </div>
                            <i class="fas fa-chevron-right text-xs transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        <div x-show="open" class="bg-purple-950/30 py-1" style="display: none;">
                            <a href="{{ route('admin.buses.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.buses.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-bus w-5 text-xs opacity-70"></i>
                                <span>Manajemen Bus</span>
                            </a>
                            <a href="{{ route('admin.schedules.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.schedules.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-calendar-alt w-5 text-xs opacity-70"></i>
                                <span>Jadwal Bus</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3. Transaksi -->
                    @php
                        $isTxActive = request()->routeIs('admin.bookings.*') || request()->routeIs('admin.refunds.*');
                    @endphp
                    <div x-data="{ open: {{ $isTxActive ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-6 py-3 text-left hover:bg-purple-700 focus:outline-none transition-colors {{ $isTxActive ? 'bg-purple-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-file-invoice-dollar w-6"></i>
                                <span class="ml-3">Transaksi</span>
                            </div>
                            <i class="fas fa-chevron-right text-xs transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        <div x-show="open" class="bg-purple-950/30 py-1" style="display: none;">
                            <a href="{{ route('admin.bookings.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.bookings.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-ticket-alt w-5 text-xs opacity-70"></i>
                                <span>Pemesanan</span>
                            </a>
                            <a href="{{ route('admin.refunds.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.refunds.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-undo-alt w-5 text-xs opacity-70"></i>
                                <span>Refund</span>
                            </a>
                        </div>
                    </div>

                    <!-- 4. Analitik & Akun -->
                    @php
                        $isAnalyticActive = request()->routeIs('admin.reports.*') || request()->routeIs('admin.profile.*');
                    @endphp
                    <div x-data="{ open: {{ $isAnalyticActive ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                            class="flex items-center justify-between w-full px-6 py-3 text-left hover:bg-purple-700 focus:outline-none transition-colors {{ $isAnalyticActive ? 'bg-purple-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-chart-line w-6"></i>
                                <span class="ml-3">Analitik & Akun</span>
                            </div>
                            <i class="fas fa-chevron-right text-xs transition-transform duration-200" :class="open ? 'rotate-90' : ''"></i>
                        </button>
                        <div x-show="open" class="bg-purple-950/30 py-1" style="display: none;">
                            <a href="{{ route('admin.reports.index') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.reports.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-chart-bar w-5 text-xs opacity-70"></i>
                                <span>Laporan</span>
                            </a>
                            <a href="{{ route('admin.profile.edit') }}"
                                class="flex items-center pl-14 pr-6 py-2 text-sm {{ request()->routeIs('admin.profile.*') ? 'text-yellow-400 font-semibold' : 'text-purple-100 hover:text-white' }}">
                                <i class="fas fa-user-cog w-5 text-xs opacity-70"></i>
                                <span>Profil</span>
                            </a>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="p-6 shrink-0 mt-auto">
                <div class="border-t border-blue-700 pt-4">
                    <div class="flex items-center">
                        <div
                            class="h-10 w-10 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center overflow-hidden">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-blue-300">
                                @if (auth()->user()->role === 'super_admin')
                                    Super Admin
                                @elseif(auth()->user()->role === 'admin')
                                    Admin
                                @elseif(auth()->user()->role === 'kondektur')
                                    Kondektur
                                @else
                                    Penumpang
                                @endif
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center px-4 py-2 bg-red-500 hover:bg-red-600 rounded-lg text-sm transition-colors">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="bg-white shadow">
                <div class="px-6 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">@yield('title')</h2>
                        <p class="text-gray-600 text-sm">{{ now()->format('l, j F Y') }}</p>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                @if (session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3"
                            onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative"
                        role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3"
                            onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg relative"
                        role="alert">
                        <span class="block sm:inline">{{ session('warning') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3"
                            onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative"
                        role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3"
                            onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
