<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (iOS)
|--------------------------------------------------------------------------
|
| Token-authenticated JSON layer for the native app. The Blade application
| keeps its own session-based routes in web.php; nothing here replaces them.
|
*/

Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    Route::post('attendance', [AttendanceController::class, 'store'])->name('api.attendance.store');
    Route::post('attendance/checkout', [AttendanceController::class, 'checkout'])->name('api.attendance.checkout');
});
