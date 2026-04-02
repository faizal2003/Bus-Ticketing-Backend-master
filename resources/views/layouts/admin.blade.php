<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Bus Ticketing')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('styles')
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-lg bg-white flex items-center justify-center">
                        <i class="fas fa-bus text-blue-700 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold">Bus Ticketing</h1>
                        <p class="text-xs text-blue-300">Admin Panel</p>
                    </div>
                </div>
            </div>

            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700 border-r-4 border-yellow-400' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span class="ml-3">Dashboard</span>
                </a>

                <a href="{{ route('admin.buses.index') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('admin.buses.*') ? 'bg-blue-700 border-r-4 border-yellow-400' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-bus w-6"></i>
                    <span class="ml-3">Manajemen Bus</span>
                </a>

                <a href="{{ route('admin.schedules.index') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('admin.schedules.*') ? 'bg-blue-700 border-r-4 border-yellow-400' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-calendar-alt w-6"></i>
                    <span class="ml-3">Jadwal Bus</span>
                </a>

                <a href="{{ route('admin.bookings.index') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('admin.bookings.*') ? 'bg-blue-700 border-r-4 border-yellow-400' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-ticket-alt w-6"></i>
                    <span class="ml-3">Pemesanan</span>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('admin.reports.*') ? 'bg-blue-700 border-r-4 border-yellow-400' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span class="ml-3">Laporan</span>
                </a>

                <a href="{{ route('admin.profile.edit') }}"
                    class="flex items-center px-6 py-3 {{ request()->routeIs('admin.profile.*') ? 'bg-blue-700 border-r-4 border-yellow-400' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-user-cog w-6"></i>
                    <span class="ml-3">Profil</span>
                </a>

                @if (auth()->user()->role === 'super_admin')
                    <a href="{{ route('superadmin.dashboard') }}"
                        class="flex items-center px-6 py-3 hover:bg-blue-700">
                        <i class="fas fa-shield-alt w-6"></i>
                        <span class="ml-3">Super Admin</span>
                    </a>
                @endif
            </nav>

            <div class="absolute bottom-0 w-64 p-6">
                <div class="border-t border-blue-700 pt-4">
                    <div class="flex items-center">
                        <div
                            class="h-10 w-10 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center">
                            <span class="font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
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

                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="text-gray-600 hover:text-gray-900">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full"></span>
                            </button>
                        </div>

                        <div class="relative">
                            <button class="text-gray-600 hover:text-gray-900">
                                <i class="fas fa-question-circle text-xl"></i>
                            </button>
                        </div>
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
