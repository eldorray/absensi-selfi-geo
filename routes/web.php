<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Employee;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\Settings;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Offline page for PWA
Route::get('/offline', function () {
    return response()->file(public_path('offline.html'));
})->name('offline');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Settings routes
    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');

    // Employee dashboard (SRP - separate controller)
    Route::get('attendance/dashboard', [Employee\DashboardController::class, 'index'])->name('attendance.dashboard');

    // Employee attendance routes (SRP - separate controller)
    Route::get('attendance/selfie', [Employee\AttendanceController::class, 'selfie'])->name('attendance.selfie');
    Route::get('attendance/checkout', [Employee\AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::post('attendance/checkout', [Employee\AttendanceController::class, 'storeCheckout'])->name('attendance.checkout.store');
    Route::get('attendance', [Employee\AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('attendance', [Employee\AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/history', [Employee\AttendanceController::class, 'index'])->name('attendance.index');
    
    // Employee profile routes (SRP - separate controller)
    Route::get('attendance/profile', [Employee\ProfileController::class, 'show'])->name('attendance.profile');
    Route::put('attendance/profile', [Employee\ProfileController::class, 'update'])->name('attendance.profile.update');
    Route::get('attendance/password', [Employee\ProfileController::class, 'showPassword'])->name('attendance.password');
    Route::put('attendance/password', [Employee\ProfileController::class, 'updatePassword'])->name('attendance.password.update');

    // Employee leave/permission routes (mobile)
    Route::get('attendance/leaves', [LeaveController::class, 'index'])->name('attendance.leaves.index');
    Route::get('attendance/leaves/create', [LeaveController::class, 'create'])->name('attendance.leaves.create');
    Route::post('attendance/leaves', [LeaveController::class, 'store'])->name('attendance.leaves.store');
    Route::get('attendance/leaves/{leave}', [LeaveController::class, 'show'])->name('attendance.leaves.show');
});

// Leave approval routes (for Admin and Kepala Sekolah)
Route::middleware(['auth', 'can-approve-leave'])->prefix('approval')->name('approval.')->group(function () {
    Route::get('leaves', [LeaveController::class, 'approvalIndex'])->name('leaves.index');
    Route::get('leaves/{leave}', [LeaveController::class, 'approvalShow'])->name('leaves.show');
    Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [Admin\AdminController::class, 'index'])->name('dashboard');

    // Office management
    Route::resource('offices', Admin\OfficeController::class)->except(['show']);

    // Attendance reports
    Route::get('attendances', [Admin\AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/{attendance}', [Admin\AttendanceController::class, 'show'])->name('attendances.show');

    // User management
    Route::resource('users', Admin\UserController::class)->except(['show']);

    // Role management
    Route::resource('roles', Admin\RoleController::class)->except(['show']);

    // Work schedule management
    Route::get('work-schedules', [Admin\WorkScheduleController::class, 'index'])->name('work-schedules.index');
    Route::post('work-schedules/settings', [Admin\WorkScheduleController::class, 'updateSettings'])->name('work-schedules.settings');
    Route::get('work-schedules/{user}/edit', [Admin\WorkScheduleController::class, 'edit'])->name('work-schedules.edit');
    Route::put('work-schedules/{user}', [Admin\WorkScheduleController::class, 'update'])->name('work-schedules.update');
    Route::patch('work-schedules/{schedule}/toggle', [Admin\WorkScheduleController::class, 'toggleStatus'])->name('work-schedules.toggle');

    // Leave management
    Route::get('leaves', [Admin\LeaveController::class, 'index'])->name('leaves.index');
    Route::get('leaves/{leave}', [Admin\LeaveController::class, 'show'])->name('leaves.show');
    Route::post('leaves/{leave}/approve', [Admin\LeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [Admin\LeaveController::class, 'reject'])->name('leaves.reject');

    // Academic year management
    Route::resource('academic-years', Admin\AcademicYearController::class)->except(['show']);
    Route::post('academic-years/{academic_year}/activate', [Admin\AcademicYearController::class, 'activate'])->name('academic-years.activate');

    // Reports
    Route::get('reports/daily', [Admin\ReportController::class, 'daily'])->name('reports.daily');
    Route::get('reports/daily/export-pdf', [Admin\ReportController::class, 'exportDailyPdf'])->name('reports.daily.export-pdf');
    Route::get('reports/monthly', [Admin\ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/monthly/export-pdf', [Admin\ReportController::class, 'exportMonthlyPdf'])->name('reports.monthly.export-pdf');
});

require __DIR__.'/auth.php';
