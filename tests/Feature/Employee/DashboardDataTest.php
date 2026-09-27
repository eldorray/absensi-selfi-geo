<?php

// tests/Feature/Employee/DashboardDataTest.php

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\EmployeeDashboardData;
use App\Services\EmployeeDashboardService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

beforeEach(fn () => Carbon::setTestNow(Carbon::parse('2026-07-20 09:00:00'))); // Senin
afterEach(fn () => Carbon::setTestNow());

function dataTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

function dataLeave(User $user, string $from, string $to, string $status): Leave
{
    return Leave::create(['user_id' => $user->id, 'type' => 'izin', 'start_date' => $from, 'end_date' => $to, 'reason' => 'Keperluan keluarga', 'status' => $status]);
}

function dataWeekdaySchedule(User $user): void
{
    $year = AcademicYear::firstOrCreate(['name' => '2026/2027'], ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat'] as $day) {
        WorkSchedule::create(['user_id' => $user->id, 'academic_year_id' => $year->id, 'day' => $day, 'check_in_time' => '07:00:00', 'check_out_time' => '14:00:00', 'is_active' => true]);
    }
}

test('approved leave counts distinct scheduled dates this month up to today, not already attended', function () {
    $user = dataTeacher();
    dataWeekdaySchedule($user);
    dataLeave($user, '2026-06-29', '2026-07-02', 'approved'); // 1–2 Juli (Rabu, Kamis)
    dataLeave($user, '2026-07-02', '2026-07-03', 'approved'); // 2 Juli tumpang tindih, +3 Juli (Jumat)
    dataLeave($user, '2026-07-18', '2026-07-25', 'approved'); // Sabtu–Minggu tak berjadwal; 20 Juli; sisanya belum terjadi
    dataLeave($user, '2026-07-10', '2026-07-10', 'pending');
    dataLeave($user, '2026-07-13', '2026-07-13', 'rejected');
    $attended = Attendance::create(['user_id' => $user->id, 'status' => AttendanceStatus::Present, 'image_path' => 'x.jpg', 'check_in_lat' => -6.2, 'check_in_long' => 106.8, 'distance_meters' => 5]);
    $attended->created_at = Carbon::parse('2026-07-03 06:55:00'); // hadir walau ada izin
    $attended->save();

    $data = app(EmployeeDashboardService::class)->for($user);

    expect($data->monthlyLeaveDays)->toBe(3) // 1, 2, 20 Juli
        ->and($data->monthlyWorkDays)->toBe(14)
        ->and($data->monthlyRecorded())->toBe(4) // 3 Juli hadir + 3 hari izin
        ->and($data->pendingLeaves)->toBe(1);
});

test('work days count scheduled weekdays this month up to today', function () {
    $user = dataTeacher();
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat'] as $day) {
        WorkSchedule::create(['user_id' => $user->id, 'academic_year_id' => $year->id, 'day' => $day, 'check_in_time' => '07:00:00', 'check_out_time' => '14:00:00', 'is_active' => true]);
    }
    WorkSchedule::create(['user_id' => $user->id, 'academic_year_id' => $year->id, 'day' => 'sabtu', 'check_in_time' => '07:00:00', 'check_out_time' => '12:00:00', 'is_active' => false]);

    // Juli 2026 dimulai Rabu: 1–3, 6–10, 13–17, dan 20 = 14 hari kerja.
    expect(app(EmployeeDashboardService::class)->for($user)->monthlyWorkDays)->toBe(14);
});

test('work days are zero without an active academic year', function () {
    expect(app(EmployeeDashboardService::class)->for(dataTeacher())->monthlyWorkDays)->toBe(0);
});

test('unread notifications only count student referral notifications', function () {
    $user = dataTeacher();
    $make = fn (string $type, ?string $readAt) => $user->notifications()->create([
        'id' => (string) Str::uuid(), 'type' => $type, 'data' => ['ok' => true], 'read_at' => $readAt,
    ]);
    $make('App\Notifications\StudentReferralCreated', null);
    $make('App\Notifications\StudentReferralStatusChanged', null);
    $make('App\Notifications\StudentReferralCreated', '2026-07-19 10:00:00');
    $make('App\Notifications\SomethingElse', null);

    expect(app(EmployeeDashboardService::class)->for($user)->unreadNotifications)->toBe(2);
});

test('on-time days derive from the monthly counts and recorded days are carried through', function () {
    $data = new EmployeeDashboardData(
        todayAttendance: null, todaySchedule: null, checkoutOpensAt: now(), checkoutTimeReached: false,
        monthlyPresent: 12, monthlyLate: 2, announcements: new Collection,
        monthlyLeaveDays: 3, monthlyWorkDays: 14, monthlyRecordedDays: 13,
    );

    expect($data->monthlyOnTime())->toBe(10)
        ->and($data->monthlyRecorded())->toBe(13);
});
