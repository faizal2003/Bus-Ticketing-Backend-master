<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Dashboard Global
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BusController as AdminBusController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\RefundController as AdminRefundController;

// Super Admin
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\SettingController as SuperAdminSettingController;
use App\Http\Controllers\SuperAdmin\ReportController as SuperAdminReportController;
use App\Http\Controllers\SuperAdmin\RouteController as SuperAdminRouteController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            'super_admin' => redirect()->route('superadmin.dashboard'),
            'admin'       => redirect()->route('admin.dashboard'),
            default       => redirect()->route('dashboard'),
        };
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Global)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes for all users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (admin & super_admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.role:admin,super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Admin Profile
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [AdminProfileController::class, 'edit'])->name('edit');
            Route::patch('/', [AdminProfileController::class, 'update'])->name('update');
            Route::put('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');
            Route::delete('/', [AdminProfileController::class, 'destroy'])->name('destroy');
            Route::post('/logout', [AdminProfileController::class, 'logout'])->name('logout');
        });

        // Bus Management
        Route::get('buses', [AdminBusController::class, 'index'])->name('buses.index');

        Route::middleware(['check.role:super_admin'])->group(function () {
            Route::get('buses/create', [AdminBusController::class, 'create'])->name('buses.create');
            Route::post('buses', [AdminBusController::class, 'store'])->name('buses.store');
            Route::get('buses/{bus}/edit', [AdminBusController::class, 'edit'])->name('buses.edit');
            Route::match(['put', 'patch'], 'buses/{bus}', [AdminBusController::class, 'update'])->name('buses.update');
            Route::delete('buses/{bus}', [AdminBusController::class, 'destroy'])->name('buses.destroy');
            Route::post('buses/{bus}/toggle-status', [AdminBusController::class, 'toggleStatus'])->name('buses.toggle-status');
        });

        Route::get('buses/{bus}', [AdminBusController::class, 'show'])->name('buses.show');

        // Schedule Management
        Route::resource('schedules', AdminScheduleController::class);
        Route::post('schedules/{schedule}/toggle-status', [AdminScheduleController::class, 'toggleStatus'])->name('schedules.toggle-status');

        // Refund Management
        Route::get('refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
        Route::put('refunds/{refund}', [AdminRefundController::class, 'update'])->name('refunds.update');

        // Booking Management
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [AdminBookingController::class, 'index'])->name('index');
            Route::get('/create', [AdminBookingController::class, 'create'])->name('create');
            Route::post('/', [AdminBookingController::class, 'store'])->name('store');
            Route::get('/{booking}', [AdminBookingController::class, 'show'])->name('show');
            Route::get('/{booking}/print', [AdminBookingController::class, 'print'])->name('print');
            Route::post('/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('confirm');
            Route::post('/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('cancel');
        });

        // Reports
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

        // Session Management
        Route::prefix('session')->name('session.')->group(function () {
            Route::post('/keepalive', fn() => response()->json(['status' => 'session extended']))->name('keepalive');
            Route::get('/check', fn() => response()->json([
                'authenticated' => Auth::check(),
                'expires_in' => config('session.lifetime') * 60,
            ]))->name('check');
            Route::post('/clear', fn() => response()->json(['status' => 'session cleared']))->name('clear');
        });
    });

/*
|--------------------------------------------------------------------------
| Super Admin Routes (only super_admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.role:super_admin'])
    ->prefix('super-admin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::resource('users', SuperAdminUserController::class);
        Route::patch('/users/{user}/toggle-status', [SuperAdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Route Management
        Route::resource('routes', SuperAdminRouteController::class);

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SuperAdminSettingController::class, 'index'])->name('index');
            Route::post('/', [SuperAdminSettingController::class, 'update'])->name('update');
            Route::post('/cache-clear', [SuperAdminSettingController::class, 'clearCache'])->name('cache.clear');
            Route::post('/maintenance/run', [SuperAdminSettingController::class, 'runMaintenance'])->name('maintenance.run');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [SuperAdminReportController::class, 'index'])->name('index');
            Route::get('export/bookings/pdf', [SuperAdminReportController::class, 'exportBookingsPDF'])->name('export.bookings.pdf');
            Route::get('export/bookings/excel', [SuperAdminReportController::class, 'exportBookingsExcel'])->name('export.bookings.excel');
            Route::get('export/revenue/pdf', [SuperAdminReportController::class, 'exportRevenuePDF'])->name('export.revenue.pdf');
            Route::get('export/revenue/excel', [SuperAdminReportController::class, 'exportRevenueExcel'])->name('export.revenue.excel');
            Route::get('export/buses/pdf', [SuperAdminReportController::class, 'exportBusesPDF'])->name('export.buses.pdf');
            Route::get('export/buses/excel', [SuperAdminReportController::class, 'exportBusesExcel'])->name('export.buses.excel');
        });
    });

// Authentication Routes
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Fallback Route (for 404)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect('/');
});
