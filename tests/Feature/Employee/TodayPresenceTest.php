<?php

// tests/Feature/Employee/TodayPresenceTest.php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use App\Services\EmployeeDashboardData;
use App\Services\TodayPresence;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

afterEach(fn () => Carbon::setTestNow());

function presence(string $now, ?Attendance $attendance = null, bool $schedule = true, bool $checkoutOpen = false): TodayPresence
{
    Carbon::setTestNow(Carbon::parse($now));

    $data = new EmployeeDashboardData(
        todayAttendance: $attendance,
        todaySchedule: $schedule ? new WorkSchedule(['check_in_time' => '07:00:00', 'check_out_time' => '14:00:00']) : null,
        checkoutOpensAt: Carbon::parse('2026-07-20 13:30:00'),
        checkoutTimeReached: $checkoutOpen,
        monthlyPresent: 0,
        monthlyLate: 0,
        announcements: new Collection,
    );

    $settings = new WorkSetting(['before_check_in' => 60, 'after_check_in' => 10, 'late_limit' => 120, 'before_check_out' => 30]);

    return TodayPresence::for($data, $settings, now());
}

function checkIn(string $at, AttendanceStatus $status = AttendanceStatus::Present, ?string $out = null, ?bool $liveness = null): Attendance
{
    $attendance = new Attendance(['status' => $status, 'distance_meters' => 38, 'check_out_at' => $out, 'liveness_verified' => $liveness]);
    $attendance->created_at = Carbon::parse($at);

    return $attendance;
}

test('before the check-in window opens', function () {
    $p = presence('2026-07-20 05:30:00');

    expect($p->status)->toBe('Belum absen')
        ->and($p->progressText)->toBe('Absen masuk dibuka 06.00')
        ->and($p->checkIn)->toBeNull()
        ->and($p->scheduleStart)->toBe('07.00')
        ->and($p->scheduleEnd)->toBe('14.00')
        ->and($p->action)->toBe(['label' => 'Absen masuk dibuka 06.00', 'href' => null, 'icon' => 'clock', 'disabled' => true])
        ->and($p->locationText)->toBe('Lokasi dicek saat absen');
});

test('the check-in action becomes available once the window opens', function () {
    $p = presence('2026-07-20 06:00:00');

    expect($p->action['label'])->toBe('Absen Masuk')
        ->and($p->action['href'])->toBe(route('attendance.selfie'))
        ->and($p->action['disabled'])->toBeFalse();
});

test('inside the on-time and late windows', function () {
    expect(presence('2026-07-20 07:05:00')->progressText)->toBe('Tepat waktu s.d. 07.10')
        ->and(presence('2026-07-20 07:30:00')->progressText)->toBe('Terlambat · tutup 09.00');
});

test('after check-in closes without attendance there is no check-in action', function () {
    $p = presence('2026-07-20 09:30:00');

    expect($p->status)->toBe('Absen ditutup')->and($p->action)->toBeNull();
});

test('checked in and waiting for the check-out window', function () {
    $p = presence('2026-07-20 09:48:00', checkIn('2026-07-20 06:52:00'));

    expect($p->status)->toBe('Tepat waktu')
        ->and($p->late)->toBeFalse()
        ->and($p->checkIn)->toBe('06.52')
        ->and($p->checkOut)->toBeNull()
        ->and($p->progress)->toBe(40)
        ->and($p->progressText)->toBe('Absen pulang dibuka dalam 3 j 42 m')
        ->and($p->action)->toBe(['label' => 'Pulang dibuka 13.30', 'href' => null, 'icon' => 'clock', 'disabled' => true])
        ->and($p->locationText)->toBe('Dalam area sekolah · 38 m dari titik absen');
});

test('check-out window open offers Absen Pulang', function () {
    $p = presence('2026-07-20 13:40:00', checkIn('2026-07-20 06:52:00'), checkoutOpen: true);

    expect($p->progressText)->toBe('Absen pulang sudah dibuka')
        ->and($p->action['label'])->toBe('Absen Pulang')
        ->and($p->action['href'])->toBe(route('attendance.checkout'));
});

test('checked out links to the history', function () {
    $p = presence('2026-07-20 14:10:00', checkIn('2026-07-20 06:52:00', out: '2026-07-20 13:45:00'), checkoutOpen: true);

    expect($p->checkOut)->toBe('13.45')
        ->and($p->progress)->toBe(100)
        ->and($p->progressText)->toBe('Selesai hari ini')
        ->and($p->action['label'])->toBe('Lihat riwayat');
});

test('a Sunday without a schedule is a holiday without actions', function () {
    $p = presence('2026-07-19 08:00:00', schedule: false);

    expect($p->status)->toBe('Libur')->and($p->action)->toBeNull()->and($p->scheduleStart)->toBeNull();
});

// The server accepts attendance on unscheduled weekdays with its default hours
// (AttendanceService::dayOffError only blocks Sunday), so the hero must offer it too.
test('a weekday without a schedule falls back to the default hours', function () {
    $p = presence('2026-07-20 07:05:00', schedule: false);

    expect($p->status)->toBe('Belum absen')
        ->and($p->scheduleStart)->toBe('07.00')
        ->and($p->scheduleEnd)->toBe('16.00')
        ->and($p->action['href'])->toBe(route('attendance.selfie'));
});

test('checked in without a schedule can still check out', function () {
    $p = presence('2026-07-20 16:10:00', checkIn('2026-07-20 07:00:00'), schedule: false, checkoutOpen: true);

    expect($p->action['label'])->toBe('Absen Pulang')
        ->and($p->action['href'])->toBe(route('attendance.checkout'));
});

test('late check-in with a manual photo is flagged', function () {
    $p = presence('2026-07-20 09:00:00', checkIn('2026-07-20 07:25:00', AttendanceStatus::Late, liveness: false));

    expect($p->status)->toBe('Terlambat')
        ->and($p->late)->toBeTrue()
        ->and($p->locationText)->toContain('foto manual, menunggu pemeriksaan');
});
