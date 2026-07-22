<?php

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;

afterEach(fn () => Carbon::setTestNow());

function wsAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

function wsGuru(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

function wsYear(string $name, string $start, string $end, bool $active = false): AcademicYear
{
    return AcademicYear::create([
        'name' => $name,
        'start_date' => $start,
        'end_date' => $end,
        'is_active' => $active,
    ]);
}

test('work schedules stay separate per academic year and survive switching years', function () {
    $admin = wsAdmin();
    $guru = wsGuru();

    $yearA = wsYear('2025/2026', '2025-07-01', '2026-06-30', active: true);

    // Fill Monday's schedule under year A.
    actingAs($admin)->put(route('admin.work-schedules.update', $guru), [
        'schedules' => ['senin' => ['check_in_time' => '07:00', 'check_out_time' => '12:45', 'is_active' => '1']],
    ])->assertRedirect();

    // Activate a new year and fill a DIFFERENT Monday schedule.
    $yearB = wsYear('2026/2027', '2026-07-01', '2027-06-30');
    $yearB->activate();

    actingAs($admin)->put(route('admin.work-schedules.update', $guru), [
        'schedules' => ['senin' => ['check_in_time' => '13:00', 'check_out_time' => '17:00', 'is_active' => '1']],
    ])->assertRedirect();

    // Switch back to year A — this used to wipe every schedule.
    $yearA->activate();

    $a = WorkSchedule::where('user_id', $guru->id)->where('academic_year_id', $yearA->id)->where('day', 'senin')->first();
    expect($a)->not->toBeNull();
    expect(Carbon::parse($a->check_in_time)->format('H:i'))->toBe('07:00');
    expect(Carbon::parse($a->check_out_time)->format('H:i'))->toBe('12:45');
    expect($a->is_active)->toBeTrue();

    // Year B's schedule is a separate, untouched row.
    $b = WorkSchedule::where('user_id', $guru->id)->where('academic_year_id', $yearB->id)->where('day', 'senin')->first();
    expect($b)->not->toBeNull();
    expect(Carbon::parse($b->check_in_time)->format('H:i'))->toBe('13:00');
    expect($b->is_active)->toBeTrue();

    // Two distinct rows for the same user+day.
    expect(WorkSchedule::where('user_id', $guru->id)->where('day', 'senin')->count())->toBe(2);
});

test('activating a year no longer deactivates other years schedules', function () {
    $guru = wsGuru();

    $yearA = wsYear('2025/2026', '2025-07-01', '2026-06-30', active: true);
    WorkSchedule::create([
        'user_id' => $guru->id, 'academic_year_id' => $yearA->id,
        'day' => 'senin', 'check_in_time' => '07:00:00', 'check_out_time' => '16:00:00', 'is_active' => true,
    ]);

    $yearB = wsYear('2026/2027', '2026-07-01', '2027-06-30');
    $yearB->activate();

    expect(WorkSchedule::where('academic_year_id', $yearA->id)->where('is_active', true)->count())->toBe(1);
});

test('copy from previous year duplicates schedules into the active year', function () {
    $admin = wsAdmin();
    $guru = wsGuru();

    $yearA = wsYear('2025/2026', '2025-07-01', '2026-06-30', active: true);
    actingAs($admin)->put(route('admin.work-schedules.update', $guru), [
        'schedules' => [
            'senin' => ['check_in_time' => '07:00', 'check_out_time' => '12:45', 'is_active' => '1'],
            'selasa' => ['check_in_time' => '07:30', 'check_out_time' => '13:00', 'is_active' => '1'],
        ],
    ])->assertRedirect();

    $yearB = wsYear('2026/2027', '2026-07-01', '2027-06-30');
    $yearB->activate();

    // Year B has no schedules yet.
    expect(WorkSchedule::where('user_id', $guru->id)->where('academic_year_id', $yearB->id)->count())->toBe(0);

    actingAs($admin)->post(route('admin.work-schedules.copy-previous', $guru))
        ->assertRedirect(route('admin.work-schedules.edit', $guru));

    $copied = WorkSchedule::where('user_id', $guru->id)->where('academic_year_id', $yearB->id)->get();
    expect($copied)->toHaveCount(2);
    expect(Carbon::parse($copied->firstWhere('day', 'senin')->check_out_time)->format('H:i'))->toBe('12:45');
    expect(Carbon::parse($copied->firstWhere('day', 'selasa')->check_in_time)->format('H:i'))->toBe('07:30');
});

test('copy from previous year is rejected when there is nothing to copy', function () {
    $admin = wsAdmin();
    $guru = wsGuru();

    wsYear('2025/2026', '2025-07-01', '2026-06-30', active: true);

    actingAs($admin)->post(route('admin.work-schedules.copy-previous', $guru))
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('editing schedules is blocked until an academic year is active', function () {
    $admin = wsAdmin();
    $guru = wsGuru();

    actingAs($admin)->get(route('admin.work-schedules.edit', $guru))
        ->assertRedirect(route('admin.work-schedules.index'))
        ->assertSessionHas('error');
});

test('employee check-out window uses the active academic year schedule', function () {
    $guru = wsGuru();
    $monday = Carbon::parse('2026-07-20'); // Monday

    // Year A ends the day early (12:00); active Year B ends late (17:00).
    $yearA = wsYear('2025/2026', '2025-07-01', '2026-06-30', active: true);
    WorkSchedule::create([
        'user_id' => $guru->id, 'academic_year_id' => $yearA->id,
        'day' => 'senin', 'check_in_time' => '07:00:00', 'check_out_time' => '12:00:00', 'is_active' => true,
    ]);

    $yearB = wsYear('2026/2027', '2026-07-01', '2027-06-30');
    WorkSchedule::create([
        'user_id' => $guru->id, 'academic_year_id' => $yearB->id,
        'day' => 'senin', 'check_in_time' => '07:00:00', 'check_out_time' => '17:00:00', 'is_active' => true,
    ]);
    $yearB->activate();

    WorkSetting::current()->update(['before_check_out' => 30]);

    // Checked in this morning.
    $att = Attendance::create([
        'user_id' => $guru->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
    ]);
    $att->created_at = $monday->copy()->setTime(7, 0);
    $att->save();

    // 16:00 — Year B opens check-out at 16:30, so it must still be rejected.
    // (If scoping fell back to Year A's 12:00, check-out would have opened at 11:30.)
    Carbon::setTestNow($monday->copy()->setTime(16, 0));

    actingAs($guru)
        ->postJson(route('attendance.checkout.store'), [])
        ->assertStatus(422)
        ->assertJsonPath('errors.time.0', fn ($msg) => str_contains($msg, '16:30'));
});
