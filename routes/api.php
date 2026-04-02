<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ConductorController;
use App\Http\Controllers\Api\PassengerController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\PaymentController;

// 🔓 Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// 🔐 Protected Routes - Semua route di bawah ini memerlukan authentication
Route::middleware(['auth:sanctum'])->group(function () {

    // 👤 Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // 🚌 Bus Routes (Untuk Penumpang)
    Route::prefix('buses')->group(function () {
        Route::get('/search', [BusController::class, 'search']);
        Route::get('/{bus}', [BusController::class, 'show']);
        Route::get('/{bus}/seats', [BusController::class, 'getSeatLayout']);
        Route::get('/popular-routes', [BusController::class, 'popularRoutes']);
    });

    // 📅 Schedule Routes
    Route::prefix('schedules')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']);
        Route::get('/{schedule}', [ScheduleController::class, 'show']);
        Route::get('/{schedule}/availability', [ScheduleController::class, 'checkAvailability']);
    });

    // 🎫 Booking Routes (Untuk Penumpang)
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']); // List booking user
        Route::post('/', [BookingController::class, 'store']); // Buat booking baru
        Route::get('/{booking}', [BookingController::class, 'show']); // Detail booking
        Route::delete('/{booking}', [BookingController::class, 'cancel']); // Batalkan booking
        Route::post('/{booking}/confirm-payment', [BookingController::class, 'confirmPayment']); // Konfirmasi pembayaran
        Route::get('/{booking}/tickets', [BookingController::class, 'getTickets']); // Get tickets untuk booking
    });

    // 🎟️ Ticket Routes
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']); // List tickets user
        Route::get('/{ticket}', [TicketController::class, 'show']); // Detail ticket
        Route::get('/{ticket}/qr', [TicketController::class, 'getQR']); // Generate QR code
        Route::post('/{ticket}/validate', [TicketController::class, 'validateTicket']); // Validasi ticket
    });

    // 💳 Payment Routes
    Route::prefix('payments')->group(function () {
        Route::post('/initiate', [PaymentController::class, 'initiate']);
        Route::post('/callback', [PaymentController::class, 'callback']);
        Route::get('/{payment}/status', [PaymentController::class, 'status']);
        Route::post('/{payment}/verify', [PaymentController::class, 'verify']);
    });

    // 🚍 Conductor Routes (Hanya untuk kondektur)
    Route::prefix('conductor')->middleware(['role:kondektur'])->group(function () {
        Route::get('/dashboard', [ConductorController::class, 'dashboard']);
        Route::get('/today-schedule', [ConductorController::class, 'todaySchedule']);
        Route::get('/schedules/{schedule}/passengers', [ConductorController::class, 'passengerList']);
        Route::post('/scan', [ConductorController::class, 'scanTicket']);
        Route::post('/validate-ticket', [ConductorController::class, 'validateTicket']);
        Route::put('/tickets/{ticket}/status', [ConductorController::class, 'updateTicketStatus']);
        Route::post('/report-departure', [ConductorController::class, 'reportDeparture']);
        Route::post('/report-arrival', [ConductorController::class, 'reportArrival']);
        Route::get('/logs', [ConductorController::class, 'getLogs']);
        Route::get('/statistics', [ConductorController::class, 'getStatistics']);
    });

    // 👤 Passenger Profile Routes
    Route::prefix('profile')->group(function () {
        // Profile routes
        Route::get('/', [PassengerController::class, 'profile']);
        Route::put('/', [PassengerController::class, 'update']);
        Route::post('/change-password', [PassengerController::class, 'changePassword']);
        Route::post('/upload-avatar', [PassengerController::class, 'uploadAvatar']);

        // History routes
        Route::get('/history', [PassengerController::class, 'bookingHistory']);
        Route::get('/upcoming-trips', [PassengerController::class, 'upcomingTrips']);

        // Statistics routes
        Route::get('/statistics', [PassengerController::class, 'statistics']);
        Route::get('/favorite-routes', [PassengerController::class, 'favoriteRoutes']);

        // Account management
        Route::post('/delete-account', [PassengerController::class, 'deleteAccount']);
    });
});

// 🌐 Health Check & Service Status
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toDateTimeString(),
        'service' => 'Bus Ticketing API',
        'version' => '1.0.0'
    ]);
});

// 📄 API Documentation
Route::get('/docs', function () {
    return response()->json([
        'message' => 'Bus Ticketing API Documentation',
        'endpoints' => [
            'auth' => [
                'POST /api/register' => 'Register new passenger',
                'POST /api/login' => 'Login user',
                'POST /api/logout' => 'Logout user (requires auth)',
                'GET /api/user' => 'Get current user (requires auth)',
            ],
            'buses' => [
                'GET /api/buses/search' => 'Search buses with filters',
                'GET /api/buses/{id}' => 'Get bus details',
                'GET /api/buses/{id}/seats' => 'Get seat layout',
            ],
            'bookings' => [
                'GET /api/bookings' => 'Get user bookings',
                'POST /api/bookings' => 'Create new booking',
                'GET /api/bookings/{id}' => 'Get booking details',
                'DELETE /api/bookings/{id}' => 'Cancel booking',
            ],
            'profile' => [
                'GET /api/profile' => 'Get passenger profile',
                'PUT /api/profile' => 'Update profile',
                'POST /api/profile/change-password' => 'Change password',
                'GET /api/profile/history' => 'Get booking history',
                'GET /api/profile/upcoming-trips' => 'Get upcoming trips',
            ],
            'conductor' => [
                'GET /api/conductor/today-schedule' => 'Get today\'s schedule',
                'POST /api/conductor/scan' => 'Scan ticket QR code',
                'GET /api/conductor/schedules/{id}/passengers' => 'Get passenger list',
            ]
        ]
    ]);
});