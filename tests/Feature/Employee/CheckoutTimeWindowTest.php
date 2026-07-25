<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use Carbon\Carbon;

afterEach(fn () => Carbon::setTestNow());

function checkoutEmployee(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

// Employee who has checked in today and has a work schedule ending at 16:00.
function checkedInEmployee(Carbon $today): User
{
    $user = checkoutEmployee();

    WorkSchedule::create([
        'user_id' => $user->id,
        'day' => strtolower($today->locale('id')->dayName),
        'check_in_time' => '07:00:00',
        'check_out_time' => '16:00:00',
        'is_active' => true,
    ]);

    $att = Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
    ]);
    $att->created_at = $today->copy()->setTime(7, 0);
    $att->save();

    return $user;
}

test('checkoutOpensAt subtracts the before-checkout window from schedule end', function () {
    $schedule = new WorkSchedule(['check_out_time' => '16:00:00']);

    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));

    expect(WorkSchedule::checkoutOpensAt($schedule, 30)->format('H:i'))->toBe('15:30');
});

test('checkoutOpensAt falls back to a default end time without a schedule', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));

    expect(WorkSchedule::checkoutOpensAt(null, 30)->format('H:i'))->toBe('15:30');
});

test('checkout is rejected before the check-out window opens', function () {
    $today = Carbon::parse('2026-07-20'); // a Monday
    WorkSetting::current()->update(['before_check_out' => 30]);

    $user = checkedInEmployee($today);

    // 15:29 — one minute before the 15:30 window opens.
    Carbon::setTestNow($today->copy()->setTime(15, 29));

    $this->actingAs($user)
        ->postJson(route('attendance.checkout.store'), [])
        ->assertStatus(422)
        ->assertJsonPath('errors.time.0', fn ($msg) => str_contains($msg, 'Belum waktunya')
            && str_contains($msg, '15:30'));

    expect(Attendance::where('user_id', $user->id)->first()->check_out_at)->toBeNull();
});

test('checkout passes the time gate once the window opens', function () {
    $today = Carbon::parse('2026-07-20');
    WorkSetting::current()->update(['before_check_out' => 30]);

    $user = checkedInEmployee($today);

    // 15:30 — window is open; time gate must not be the blocker anymore.
    Carbon::setTestNow($today->copy()->setTime(15, 30));

    $response = $this->actingAs($user)
        ->postJson(route('attendance.checkout.store'), []);

    // The request still fails validation (no office/photo), but NOT on the time gate.
    $errors = $response->json('errors') ?? [];
    expect($errors['time'] ?? null)->toBeNull();
});

test('dashboard hides the check-out button before the window opens', function () {
    $today = Carbon::parse('2026-07-20');
    WorkSetting::current()->update(['before_check_out' => 30]);

    $user = checkedInEmployee($today);

    Carbon::setTestNow($today->copy()->setTime(15, 29));

    $this->actingAs($user)
        ->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertDontSee('nav-fab nav-fab-pulang');
});

test('dashboard shows the check-out button once the window opens', function () {
    $today = Carbon::parse('2026-07-20');
    WorkSetting::current()->update(['before_check_out' => 30]);

    $user = checkedInEmployee($today);

    Carbon::setTestNow($today->copy()->setTime(15, 30));

    $this->actingAs($user)
        ->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('nav-fab nav-fab-pulang');
});
