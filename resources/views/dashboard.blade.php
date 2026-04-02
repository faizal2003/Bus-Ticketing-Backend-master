<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bus Ticketing</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-800">Bus Ticketing System</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Halo, {{ auth()->user()->name }}</span>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-medium
                            @if (auth()->user()->role == 'super_admin') bg-purple-100 text-purple-800
                            @elseif(auth()->user()->role == 'admin') bg-blue-100 text-blue-800
                            @elseif(auth()->user()->role == 'kondektur') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Welcome to Bus Ticketing System</h2>

                        <div class="mb-6">
                            <p class="text-gray-600 mb-4">
                                You are logged in as <strong>{{ auth()->user()->role }}</strong>.
                            </p>

                            @if (auth()->user()->role == 'penumpang')
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                    <p class="text-blue-700">
                                        Anda login sebagai penumpang. Fitur mobile app tersedia untuk:
                                    </p>
                                    <ul class="list-disc pl-5 mt-2 text-blue-600">
                                        <li>Pemesanan tiket bus</li>
                                        <li>Pembayaran online</li>
                                        <li>E-ticket dengan QR Code</li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <!-- Role Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-blue-800 mb-2">Penumpang</h3>
                                <p class="text-blue-600">Dapat melakukan pemesanan tiket melalui mobile app.</p>
                            </div>

                            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Kondektur</h3>
                                <p class="text-yellow-600">Dapat memvalidasi tiket melalui mobile app.</p>
                            </div>

                            <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-green-800 mb-2">Admin</h3>
                                <p class="text-green-600">Mengelola jadwal, bus, dan laporan pemesanan.</p>
                            </div>

                            <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-6 rounded-lg shadow">
                                <h3 class="text-lg font-semibold text-purple-800 mb-2">Super Admin</h3>
                                <p class="text-purple-600">Mengelola user, role, dan konfigurasi sistem.</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                            <div class="flex flex-wrap gap-4">
                                <a href="{{ route('profile.edit') }}"
                                    class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                                    Edit Profile
                                </a>

                                @if (auth()->user()->role == 'penumpang')
                                    <a href="#"
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">
                                        Book Ticket
                                    </a>
                                @endif

                                @if (in_array(auth()->user()->role, ['admin', 'super_admin']))
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500">
                                        Admin Panel
                                    </a>
                                @endif

                                @if (auth()->user()->role == 'kondektur')
                                    <a href="{{ route('conductor.dashboard') }}"
                                        class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-500">
                                        Conductor Panel
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
