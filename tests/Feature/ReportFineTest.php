<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use Carbon\Carbon;

function mkFineUser(bool $admin): User
{
    $role = Role::create([
        'name' => $admin ? 'Admin' : 'Guru',
        'slug' => $admin ? 'admin' : 'guru',
        'is_admin' => $admin,
    ]);

    return User::factory()->create(['role_id' => $role->id]);
}

// Create a late employee whose check-in is $minutesPastTolerance minutes after
// the tolerance threshold (schedule 07:00 + after_check_in).
function lateEmployee(Carbon $date, int $minutesPastTolerance): User
{
    $settings = WorkSetting::current(); // after_check_in default 10
    $user = mkFineUser(admin: false);

    WorkSchedule::create([
        'user_id' => $user->id,
        'day' => strtolower($date->locale('id')->dayName),
        'check_in_time' => '07:00:00',
        'check_out_time' => '15:00:00',
        'is_active' => true,
    ]);

    $checkIn = $date->copy()->setTime(7, 0)->addMinutes($settings->after_check_in + $minutesPastTolerance);

    $att = Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Late,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
    ]);
    $att->created_at = $checkIn;
    $att->save();

    return $user;
}

test('daily report shows tier-1 fine for small lateness', function () {
    $date = Carbon::today();
    lateEmployee($date, minutesPastTolerance: 5); // 5 <= 15 => tier 1

    $this->actingAs(mkFineUser(admin: true));

    $this->get(route('admin.reports.daily', ['date' => $date->format('Y-m-d')]))
        ->assertStatus(200)
        ->assertSee('Rp 5.000');
});

test('daily report shows tier-2 fine for large lateness', function () {
    $date = Carbon::today();
    lateEmployee($date, minutesPastTolerance: 25); // 25 > 15 => tier 2

    $this->actingAs(mkFineUser(admin: true));

    $this->get(route('admin.reports.daily', ['date' => $date->format('Y-m-d')]))
        ->assertStatus(200)
        ->assertSee('Rp 10.000');
});

test('admin can update fine settings', function () {
    $this->actingAs(mkFineUser(admin: true));

    $this->post(route('admin.work-schedules.settings'), [
        'before_check_in' => 60,
        'after_check_in' => 10,
        'late_limit' => 120,
        'before_check_out' => 30,
        'require_check_in' => '1',
        'fine_tier1_amount' => 7000,
        'fine_tier2_amount' => 15000,
        'fine_tier1_max_minutes' => 20,
    ])->assertRedirect();

    $s = WorkSetting::current();
    expect($s->fine_tier1_amount)->toBe(7000)
        ->and($s->fine_tier2_amount)->toBe(15000)
        ->and($s->fine_tier1_max_minutes)->toBe(20);
});
