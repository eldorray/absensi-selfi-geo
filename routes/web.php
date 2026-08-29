<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Employee;
use App\Http\Controllers\Employee\AccountSwitchController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\Settings;
use Illuminate\Support\Facades\Route;

Route::get('branding/{type}', [\App\Http\Controllers\BrandingAssetController::class, 'show'])
    ->whereIn('type', ['logo', 'icon'])
    ->name('branding.asset');
Route::get('manifest.webmanifest', function () {
    $settings = \App\Models\ApplicationSetting::current();
    $iconUrl = $settings->iconUrl();

    return response()->json([
        'name' => 'Absensi Selfie Geo',
        'short_name' => 'Absensi',
        'description' => 'Aplikasi Absensi Selfie dengan Verifikasi GPS',
        'start_url' => '/attendance/dashboard',
        'scope' => '/',
        'display' => 'standalone',
        'background_color' => '#f7fbf5',
        'theme_color' => '#176b43',
        'orientation' => 'portrait-primary',
        'icons' => $settings->application_icon_path !== null ? [
            ['src' => $iconUrl, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => $iconUrl, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ] : [
            ['src' => '/images/icons/icon-192.png?v=2', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/images/icons/icon-512.png?v=2', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ],
    ])->header('Content-Type', 'application/manifest+json');
})->name('manifest');

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

// Serve avatars via the app (avoids reliance on the public/storage symlink).
// Both guards: Blade authenticates by session, the mobile app by Sanctum token.
// Keep it out of the session-only group or the native client cannot load the image.
Route::get('avatar/{user}', [Employee\AvatarController::class, 'show'])
    ->middleware('auth:sanctum,web')
    ->name('avatar.show');

Route::middleware(['auth'])->group(function () {
    // Settings routes
    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');
    Route::middleware('admin')->group(function () {
        Route::get('settings/branding', [Settings\BrandingController::class, 'edit'])->name('settings.branding.edit');
        Route::put('settings/branding', [Settings\BrandingController::class, 'update'])->name('settings.branding.update');
        Route::delete('settings/branding', [Settings\BrandingController::class, 'destroy'])->name('settings.branding.destroy');
    });

    // Employee dashboard (SRP - separate controller)
    Route::get('attendance/dashboard', [Employee\DashboardController::class, 'index'])->name('attendance.dashboard');
    Route::get('attendance/my-class', [Employee\MyClassController::class, 'index'])->name('attendance.my-class.index');
    Route::get('attendance/my-class/students/{student}', [Employee\MyClassController::class, 'show'])->name('attendance.my-class.show');
    Route::middleware('student-affairs')->group(function () {
        Route::get('attendance/kesiswaan', [Employee\KesiswaanController::class, 'index'])->name('attendance.kesiswaan.index');
        Route::get('attendance/kesiswaan/students/{student}', [Employee\KesiswaanController::class, 'show'])->name('attendance.kesiswaan.show');
    });
    Route::get('attendance/referrals/mine', [Employee\MyReferralController::class, 'index'])->name('attendance.referrals.mine');
    Route::get('attendance/referrals/queue', [Employee\ReferralQueueController::class, 'index'])->name('attendance.referrals.queue');
    Route::get('attendance/kesiswaan/students/{student}/referrals/create', [Employee\StudentReferralController::class, 'create'])->name('attendance.kesiswaan.referrals.create');
    Route::post('attendance/kesiswaan/students/{student}/referrals', [Employee\StudentReferralController::class, 'store'])->name('attendance.kesiswaan.referrals.store');
    Route::get('attendance/kesiswaan/referrals/{referral}', [Employee\StudentReferralController::class, 'show'])->name('attendance.kesiswaan.referrals.show');
    Route::post('attendance/kesiswaan/referrals/{referral}/claim', [Employee\StudentReferralController::class, 'claim'])->name('attendance.kesiswaan.referrals.claim');
    Route::patch('attendance/kesiswaan/referrals/{referral}/transition', [Employee\StudentReferralController::class, 'transition'])->name('attendance.kesiswaan.referrals.transition');
    Route::get('attendance/kesiswaan/referrals/{referral}/attachments/{attachment}', [Employee\StudentReferralController::class, 'attachment'])->name('attendance.kesiswaan.referrals.attachments.show');
    Route::get('attendance/kesiswaan/notifications', [Employee\NotificationController::class, 'index'])->name('attendance.kesiswaan.notifications.index');
    Route::get('attendance/kesiswaan/notifications/{notification}', [Employee\NotificationController::class, 'show'])->name('attendance.kesiswaan.notifications.show');
    Route::patch('attendance/kesiswaan/notifications', [Employee\NotificationController::class, 'readAll'])->name('attendance.kesiswaan.notifications.read-all');

    // Fast, password-less account switching (linked, non-admin accounts only)
    Route::post('account/switch', [AccountSwitchController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('account.switch');

    // Employee attendance routes (SRP - separate controller)
    Route::get('attendance/selfie', [Employee\AttendanceController::class, 'selfie'])->name('attendance.selfie');
    Route::get('attendance/checkout', [Employee\AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::post('attendance/checkout', [Employee\AttendanceController::class, 'storeCheckout'])->name('attendance.checkout.store');
    // Legacy check-in url, and the start_url of older PWA installs. Installed
    // PWAs keep the manifest they were installed with, so this must land on
    // beranda rather than opening the camera on launch.
    Route::redirect('attendance', '/attendance/dashboard')->name('attendance.create');
    Route::post('attendance', [Employee\AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/history', [Employee\AttendanceController::class, 'index'])->name('attendance.index');

    // Employee announcement (Informasi) detail
    Route::get('attendance/information/{announcement}', [Employee\AnnouncementController::class, 'show'])->name('attendance.information.show');

    Route::middleware('bk')->prefix('attendance/bk')->name('attendance.bk.')->group(function () {
        Route::get('/', [Employee\BkRecordController::class, 'index'])->name('index');
        Route::get('/create', [Employee\BkRecordController::class, 'create'])->name('create');
        Route::post('/', [Employee\BkRecordController::class, 'store'])->name('store');
        Route::get('/attachments/{attachment}', [Employee\BkRecordController::class, 'attachment'])->name('attachments.show');
        Route::get('/{record}', [Employee\BkRecordController::class, 'show'])->name('show');
        Route::get('/{record}/edit', [Employee\BkRecordController::class, 'edit'])->name('edit');
        Route::put('/{record}', [Employee\BkRecordController::class, 'update'])->name('update');
        Route::patch('/{record}/archive', [Employee\BkRecordController::class, 'archive'])->name('archive');
        Route::patch('/{record}/restore', [Employee\BkRecordController::class, 'restore'])->name('restore');
        Route::post('/{record}/follow-ups', [Employee\BkRecordController::class, 'followUp'])->name('follow-ups.store');
        Route::post('/{record}/parent-contacts', [Employee\BkRecordController::class, 'parentContact'])->name('parent-contacts.store');
    });

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

    // Read-only Kesiswaan
    Route::get('kesiswaan', [Admin\KesiswaanController::class, 'index'])->name('kesiswaan.index');
    Route::get('kesiswaan/students/{student}', [Admin\KesiswaanController::class, 'show'])->name('kesiswaan.show');
    Route::get('kesiswaan/referrals/{referral}', [Admin\KesiswaanController::class, 'referral'])->name('kesiswaan.referrals.show');
    Route::get('kesiswaan/referrals/{referral}/attachments/{attachment}', [Admin\KesiswaanController::class, 'attachment'])->name('kesiswaan.referrals.attachments.show');

    // Student and class management
    Route::get('students/{schoolLevel}', [Admin\StudentController::class, 'index'])->name('students.index');
    Route::get('students/{schoolLevel}/create', [Admin\StudentController::class, 'create'])->name('students.create');
    Route::post('students/{schoolLevel}', [Admin\StudentController::class, 'store'])->name('students.store');
    Route::post('students/{schoolLevel}/sync', [Admin\StudentController::class, 'sync'])->name('students.sync');
    Route::get('students/{schoolLevel}/{student}/edit', [Admin\StudentController::class, 'edit'])->name('students.edit');
    Route::put('students/{schoolLevel}/{student}', [Admin\StudentController::class, 'update'])->name('students.update');
    Route::delete('students/{schoolLevel}/bulk', [Admin\StudentController::class, 'bulkDestroy'])->name('students.bulk-destroy');
    Route::delete('students/{schoolLevel}/{student}', [Admin\StudentController::class, 'destroy'])->name('students.destroy');

    Route::get('school-classes/{schoolLevel}', [Admin\SchoolClassController::class, 'index'])->name('school-classes.index');
    Route::get('school-classes/{schoolLevel}/create', [Admin\SchoolClassController::class, 'create'])->name('school-classes.create');
    Route::post('school-classes/{schoolLevel}', [Admin\SchoolClassController::class, 'store'])->name('school-classes.store');
    Route::get('school-classes/{schoolLevel}/{schoolClass}/edit', [Admin\SchoolClassController::class, 'edit'])->name('school-classes.edit');
    Route::put('school-classes/{schoolLevel}/{schoolClass}', [Admin\SchoolClassController::class, 'update'])->name('school-classes.update');
    Route::delete('school-classes/{schoolLevel}/{schoolClass}', [Admin\SchoolClassController::class, 'destroy'])->name('school-classes.destroy');

    Route::get('homeroom-assignments', [Admin\HomeroomAssignmentController::class, 'index'])->name('homeroom-assignments.index');
    Route::post('homeroom-assignments', [Admin\HomeroomAssignmentController::class, 'store'])->name('homeroom-assignments.store');
    Route::put('homeroom-assignments/{homeroomAssignment}', [Admin\HomeroomAssignmentController::class, 'update'])->name('homeroom-assignments.update');
    Route::delete('homeroom-assignments/{homeroomAssignment}', [Admin\HomeroomAssignmentController::class, 'destroy'])->name('homeroom-assignments.destroy');
    Route::post('homeroom-assignments/copy-previous', [Admin\HomeroomAssignmentController::class, 'copyPrevious'])->name('homeroom-assignments.copy-previous');

    // Attendance reports
    Route::get('attendances', [Admin\AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/{attendance}', [Admin\AttendanceController::class, 'show'])->name('attendances.show');

    // User management
    Route::post('users/sync', [Admin\UserController::class, 'syncFromApi'])->name('users.sync');
    Route::get('users/export-pdf', [Admin\UserController::class, 'exportPasswordsPdf'])->name('users.export-pdf');
    Route::post('users/{user}/reset-password', [Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', Admin\UserController::class)->except(['show']);

    Route::resource('bk-categories', Admin\BkCategoryController::class)->except(['show']);
    Route::get('bk-records', [Admin\BkRecordController::class, 'index'])->name('bk-records.index');
    Route::get('bk-records/{bkRecord}', [Admin\BkRecordController::class, 'show'])->name('bk-records.show');
    Route::patch('bk-records/{bkRecord}/archive', [Admin\BkRecordController::class, 'archive'])->name('bk-records.archive');
    Route::patch('bk-records/{bkRecord}/restore', [Admin\BkRecordController::class, 'restore'])->name('bk-records.restore');

    // Role management
    Route::resource('roles', Admin\RoleController::class)->except(['show']);

    // Work schedule management
    Route::get('work-schedules', [Admin\WorkScheduleController::class, 'index'])->name('work-schedules.index');
    Route::post('work-schedules/settings', [Admin\WorkScheduleController::class, 'updateSettings'])->name('work-schedules.settings');
    Route::get('work-schedules/{user}/edit', [Admin\WorkScheduleController::class, 'edit'])->name('work-schedules.edit');
    Route::put('work-schedules/{user}', [Admin\WorkScheduleController::class, 'update'])->name('work-schedules.update');
    Route::post('work-schedules/{user}/copy-previous', [Admin\WorkScheduleController::class, 'copyFromPrevious'])->name('work-schedules.copy-previous');
    Route::patch('work-schedules/{schedule}/toggle', [Admin\WorkScheduleController::class, 'toggleStatus'])->name('work-schedules.toggle');

    // Leave management
    Route::get('leaves', [Admin\LeaveController::class, 'index'])->name('leaves.index');
    Route::get('leaves/{leave}', [Admin\LeaveController::class, 'show'])->name('leaves.show');
    Route::post('leaves/{leave}/approve', [Admin\LeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [Admin\LeaveController::class, 'reject'])->name('leaves.reject');

    // Academic year management
    Route::resource('academic-years', Admin\AcademicYearController::class)->except(['show']);
    Route::post('academic-years/{academic_year}/activate', [Admin\AcademicYearController::class, 'activate'])->name('academic-years.activate');

    // Announcement (Informasi) management
    Route::resource('announcements', Admin\AnnouncementController::class)->except(['show']);
    Route::patch('announcements/{announcement}/toggle', [Admin\AnnouncementController::class, 'toggle'])->name('announcements.toggle');

    // Reports
    Route::get('reports/daily', [Admin\ReportController::class, 'daily'])->name('reports.daily');
    Route::get('reports/daily/export-pdf', [Admin\ReportController::class, 'exportDailyPdf'])->name('reports.daily.export-pdf');
    Route::delete('reports/daily/reset/{attendance}', [Admin\ReportController::class, 'resetAttendance'])->name('reports.daily.reset');
    Route::get('reports/monthly', [Admin\ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/monthly/export-pdf', [Admin\ReportController::class, 'exportMonthlyPdf'])->name('reports.monthly.export-pdf');

    Route::get('account-switches', [Admin\AccountSwitchLogController::class, 'index'])->name('account-switches.index');
});

require __DIR__.'/auth.php';
