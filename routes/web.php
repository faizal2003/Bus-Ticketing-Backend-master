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

// Super Admin
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\SettingController as SuperAdminSettingController;

// Conductor
use App\Http\Controllers\Conductor\DashboardController as ConductorDashboardController;
use App\Http\Controllers\Conductor\ScanController as ConductorScanController;

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
            'kondektur'   => redirect()->route('conductor.dashboard'),
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
        Route::resource('buses', AdminBusController::class)->except(['show']);
        Route::post('buses/{bus}/toggle-status', [AdminBusController::class, 'toggleStatus'])->name('buses.toggle-status');

        // Schedule Management
        Route::resource('schedules', AdminScheduleController::class);
        Route::post('schedules/{schedule}/toggle-status', [AdminScheduleController::class, 'toggleStatus'])->name('schedules.toggle-status');

        // Booking Management
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [AdminBookingController::class, 'index'])->name('index');
            Route::get('/{booking}', [AdminBookingController::class, 'show'])->name('show');
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

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SuperAdminSettingController::class, 'index'])->name('index');
            Route::post('/', [SuperAdminSettingController::class, 'update'])->name('update');
            Route::post('/cache-clear', [SuperAdminSettingController::class, 'clearCache'])->name('cache.clear');
            Route::post('/maintenance/run', [SuperAdminSettingController::class, 'runMaintenance'])->name('maintenance.run');
        });
    });

/*
|--------------------------------------------------------------------------
| Conductor Routes (only kondektur)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.role:kondektur'])
    ->prefix('conductor')
    ->name('conductor.')
    ->group(function () {
        Route::get('/dashboard', [ConductorDashboardController::class, 'index'])->name('dashboard');

        // QR Scanning
        Route::prefix('scan')->name('scan.')->group(function () {
            Route::get('/', [ConductorScanController::class, 'index'])->name('index');
            Route::post('/', [ConductorScanController::class, 'processScan'])->name('process');
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
