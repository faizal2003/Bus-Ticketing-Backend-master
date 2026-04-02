@extends('layouts.superadmin')

@section('title', 'Super Admin Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Users</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $totalUsers ?? 0 }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-blue-600">
                        <i class="fas fa-user-shield mr-1"></i>{{ $totalAdmins ?? 0 }} admins
                    </span>
                </div>
            </div>

            <!-- Today's Bookings -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Today's Bookings</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $todayBookings ?? 0 }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-green-600">
                        Rp {{ number_format($todayRevenue ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- System Health -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center">
                            <i class="fas fa-heartbeat text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">System Health</h3>
                        <p class="text-3xl font-bold text-gray-900">98%</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>All systems operational
                    </span>
                </div>
            </div>

            <!-- Revenue -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 rounded-lg bg-gradient-to-r from-yellow-500 to-yellow-600 flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm font-medium">Total Revenue</h3>
                        <p class="text-3xl font-bold text-gray-900">Rp
                            {{ number_format($systemHealth['month_revenue'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-green-600">
                        <i class="fas fa-chart-line mr-1"></i>This month
                    </span>
                </div>
            </div>
        </div>

        <!-- Recent Users & Bookings -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Users -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                    <a href="{{ route('superadmin.users.index') }}" class="text-sm text-purple-600 hover:text-purple-800">
                        View all <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Role
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Joined
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentUsers as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                                                {{ strtoupper(substr($user['name'], 0, 1)) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $user['name'] }}</div>
                                                <div class="text-sm text-gray-500">{{ $user['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $user['role'] == 'super_admin'
                                                ? 'bg-purple-100 text-purple-800'
                                                : ($user['role'] == 'admin'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : ($user['role'] == 'kondektur'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : 'bg-green-100 text-green-800')) }}">
                                            {{ $user['role_name'] ?? $user['role'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user['created_at'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Bookings</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Booking Code
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentBookings as $booking)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $booking['booking_code'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $booking['created_at'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $booking['user_name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-green-600">
                                            Rp {{ number_format($booking['total_price'], 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $booking['status'] == 'confirmed'
                                                    ? 'bg-green-100 text-green-800'
                                                    : ($booking['status'] == 'pending'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($booking['status']) }}
                                            </span>
                                            <div>
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $booking['payment_status'] == 'paid' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($booking['payment_status']) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        No bookings found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Overview -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">System Overview</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900">{{ $systemHealth['total_users'] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Total Users</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900">{{ $systemHealth['total_admins'] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Admins</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900">{{ $systemHealth['total_buses'] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Buses</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900">{{ $systemHealth['total_bookings'] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Bookings</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900">{{ $systemHealth['active_schedules'] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Active Schedules</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900">{{ $systemHealth['month_bookings'] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Month Bookings</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('superadmin.users.create') }}"
                    class="flex items-center justify-center p-6 border-2 border-dashed border-purple-300 rounded-lg hover:bg-purple-50 hover:border-purple-400 transition-colors">
                    <div class="text-center">
                        <i class="fas fa-user-plus text-purple-600 text-3xl mb-3"></i>
                        <p class="text-sm font-medium text-gray-900">Add New User</p>
                        <p class="text-xs text-gray-500 mt-1">Create user account</p>
                    </div>
                </a>

                <a href="{{ route('superadmin.settings.index') }}"
                    class="flex items-center justify-center p-6 border-2 border-dashed border-blue-300 rounded-lg hover:bg-blue-50 hover:border-blue-400 transition-colors">
                    <div class="text-center">
                        <i class="fas fa-cogs text-blue-600 text-3xl mb-3"></i>
                        <p class="text-sm font-medium text-gray-900">System Settings</p>
                        <p class="text-xs text-gray-500 mt-1">Configure system</p>
                    </div>
                </a>

                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center justify-center p-6 border-2 border-dashed border-green-300 rounded-lg hover:bg-green-50 hover:border-green-400 transition-colors">
                    <div class="text-center">
                        <i class="fas fa-shield-alt text-green-600 text-3xl mb-3"></i>
                        <p class="text-sm font-medium text-gray-900">Admin Panel</p>
                        <p class="text-xs text-gray-500 mt-1">Go to admin panel</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Tambahan styling jika diperlukan */
        .transition-colors {
            transition: all 0.3s ease;
        }

        .bg-gradient-to-r {
            background-size: 200% 200%;
            animation: gradient 3s ease infinite;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }
    </style>
@endpush
