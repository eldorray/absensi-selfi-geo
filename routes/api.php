<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccountSwitchController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProfileController;
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

    Route::post('account/switch', [AccountSwitchController::class, 'store'])->name('api.account.switch');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    Route::post('attendance', [AttendanceController::class, 'store'])->name('api.attendance.store');
    Route::post('attendance/checkout', [AttendanceController::class, 'checkout'])->name('api.attendance.checkout');
    Route::get('attendance/history', [HistoryController::class, 'index'])->name('api.attendance.history');

    Route::get('leaves', [LeaveController::class, 'index'])->name('api.leaves.index');
    Route::post('leaves', [LeaveController::class, 'store'])->name('api.leaves.store');
    Route::get('leaves/{leave}', [LeaveController::class, 'show'])->name('api.leaves.show');

    Route::get('profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('api.profile.avatar');

    Route::put('password', [PasswordController::class, 'update'])->name('api.password.update');
});
