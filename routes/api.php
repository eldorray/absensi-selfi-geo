<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccountSwitchController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BkController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\KesiswaanController;
use App\Http\Controllers\Api\LeaveApprovalController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\MyClassController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StudentReferralController;
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

    /*
     * Persetujuan perizinan — Admin / Kepala Sekolah.
     *
     * Prefix `approval` sengaja dipisah dari `leaves` milik pemohon: satu segmen
     * URL yang berbeda membuat middleware penyetuju mustahil bocor ke jalur
     * pengajuan pribadi.
     */
    Route::middleware('can-approve-leave')->prefix('approval')->name('api.approval.')->group(function (): void {
        Route::get('leaves', [LeaveApprovalController::class, 'index'])->name('leaves.index');
        Route::get('leaves/{leave}', [LeaveApprovalController::class, 'show'])->name('leaves.show');
        Route::post('leaves/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leaves.approve');
        Route::post('leaves/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leaves.reject');
    });

    /*
     * Kelas Saya — wali kelas dengan penugasan aktif.
     */
    Route::middleware('homeroom')->prefix('my-class')->name('api.my-class.')->group(function (): void {
        Route::get('/', [MyClassController::class, 'index'])->name('index');
        Route::get('students/{student}', [MyClassController::class, 'show'])->name('show');
        Route::get('students/{student}/bk-summary', [MyClassController::class, 'bkSummary'])->name('bk-summary');
        Route::post('students/{student}/referrals', [StudentReferralController::class, 'store'])->name('referrals.store');
    });

    /*
     * Kesiswaan + rujukan.
     *
     * Hanya direktori siswa yang dijaga `student-affairs`; rujukan punya aturan
     * kelayakannya sendiri (wali kelas untuk `mine`, Guru BK untuk `queue`,
     * policy untuk baris tunggal) sehingga tidak boleh ikut middleware itu.
     */
    Route::prefix('kesiswaan')->name('api.kesiswaan.')->group(function (): void {
        Route::middleware('student-affairs')->group(function (): void {
            Route::get('students', [KesiswaanController::class, 'index'])->name('students.index');
            Route::get('students/{student}', [KesiswaanController::class, 'show'])->name('students.show');
        });

        Route::get('referrals/meta', [StudentReferralController::class, 'meta'])->name('referrals.meta');
        Route::get('referrals/mine', [StudentReferralController::class, 'mine'])->name('referrals.mine');
        Route::get('referrals/queue', [StudentReferralController::class, 'queue'])->name('referrals.queue');
        Route::get('referrals/{referral}', [StudentReferralController::class, 'show'])->name('referrals.show');
        Route::post('referrals/{referral}/claim', [StudentReferralController::class, 'claim'])->name('referrals.claim');
        Route::match(['put', 'patch'], 'referrals/{referral}/transition', [StudentReferralController::class, 'transition'])->name('referrals.transition');
        Route::get('referrals/{referral}/attachments/{attachment}', [StudentReferralController::class, 'attachment'])->name('referrals.attachments.download');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    });

    Route::middleware('bk')->prefix('bk')->name('api.bk.')->group(function (): void {
        Route::get('meta', [BkController::class, 'meta'])->name('meta');
        Route::get('students', [BkController::class, 'students'])->name('students');
        Route::get('records', [BkController::class, 'index'])->name('records.index');
        Route::post('records', [BkController::class, 'store'])->name('records.store');
        Route::get('records/{record}', [BkController::class, 'show'])->name('records.show');
        Route::match(['put', 'patch'], 'records/{record}', [BkController::class, 'update'])->name('records.update');
        Route::post('records/{record}/archive', [BkController::class, 'archive'])->name('records.archive');
        Route::post('records/{record}/restore', [BkController::class, 'restore'])->name('records.restore');
        Route::get('records/{record}/attachments/{attachment}', [BkController::class, 'download'])->name('attachments.download');
        Route::get('records/{record}/follow-ups', [BkController::class, 'followUps'])->name('follow-ups.index');
        Route::post('records/{record}/follow-ups', [BkController::class, 'storeFollowUp'])->name('follow-ups.store');
        Route::match(['put', 'patch'], 'records/{record}/follow-ups/{followUp}', [BkController::class, 'updateFollowUp'])->name('follow-ups.update');
        Route::get('records/{record}/parent-contacts', [BkController::class, 'parentContacts'])->name('parent-contacts.index');
        Route::post('records/{record}/parent-contacts', [BkController::class, 'storeParentContact'])->name('parent-contacts.store');
        Route::match(['put', 'patch'], 'records/{record}/parent-contacts/{contact}', [BkController::class, 'updateParentContact'])->name('parent-contacts.update');
    });

    Route::get('profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('api.profile.avatar');

    Route::put('password', [PasswordController::class, 'update'])->name('api.password.update');
});
