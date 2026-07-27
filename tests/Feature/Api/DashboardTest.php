<?php

declare(strict_types=1);

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;

afterEach(fn () => Carbon::setTestNow());

function dashTeacher(?Office $office = null): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    return User::factory()->create([
        'role_id' => $role->id,
        'office_id' => $office?->id,
    ]);
}

function dashSchedule(User $user, Carbon $day): WorkSchedule
{
    $year = AcademicYear::firstOrCreate(
        ['name' => '2026/2027'],
        ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true],
    );

    return WorkSchedule::create([
        'user_id' => $user->id,
        'academic_year_id' => $year->id,
        'day' => strtolower($day->locale('id')->dayName),
        'check_in_time' => '07:00:00',
        'check_out_time' => '15:00:00',
        'is_active' => true,
    ]);
}

test('dashboard reports a null status before the teacher checks in', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 06:30:00'));
    $user = dashTeacher();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertStatus(200)
        ->assertJsonPath('status', null)
        ->assertJsonPath('check_in_time', null)
        ->assertJsonPath('check_out_time', null);
});

test('dashboard maps the stored present status to on_time', function () {
    $today = Carbon::parse('2026-07-20 09:00:00');
    Carbon::setTestNow($today);
    $user = dashTeacher();

    $att = Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
    ]);
    $att->created_at = $today->copy()->setTime(7, 12);
    $att->save();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertStatus(200)
        ->assertJsonPath('status', 'on_time')
        ->assertJsonPath('check_in_time', '07:12');
});

test('dashboard reports late as late and includes the check-out time', function () {
    $today = Carbon::parse('2026-07-20 16:00:00');
    Carbon::setTestNow($today);
    $user = dashTeacher();

    $att = Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Late,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
        'check_out_at' => $today->copy()->setTime(15, 3),
    ]);
    $att->created_at = $today->copy()->setTime(7, 45);
    $att->save();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertStatus(200)
        ->assertJsonPath('status', 'late')
        ->assertJsonPath('check_in_time', '07:45')
        ->assertJsonPath('check_out_time', '15:03');
});

test('dashboard returns the schedule as HH:mm and the Indonesian day name', function () {
    $today = Carbon::parse('2026-07-20 08:00:00'); // a Monday
    Carbon::setTestNow($today);
    $user = dashTeacher();
    dashSchedule($user, $today);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertStatus(200)
        ->assertJsonPath('schedule.start', '07:00')
        ->assertJsonPath('schedule.end', '15:00')
        ->assertJsonPath('day_name', 'Senin')
        ->assertJsonPath('date', '2026-07-20');
});

test('dashboard returns a null schedule on a day off', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-19 08:00:00')); // Sunday, no schedule
    $user = dashTeacher();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertStatus(200)
        ->assertJsonPath('schedule', null);
});

test('dashboard summarises the current month', function () {
    $today = Carbon::parse('2026-07-20 08:00:00');
    Carbon::setTestNow($today);
    $user = dashTeacher();

    foreach ([[1, AttendanceStatus::Present], [2, AttendanceStatus::Present], [3, AttendanceStatus::Late]] as [$day, $status]) {
        $att = Attendance::create([
            'user_id' => $user->id,
            'status' => $status,
            'image_path' => 'x.jpg',
            'check_in_lat' => -6.2,
            'check_in_long' => 106.8,
            'distance_meters' => 10,
        ]);
        $att->created_at = $today->copy()->setDay($day)->setTime(7, 0);
        $att->save();
    }

    // A record from last month must not be counted.
    $old = Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
    ]);
    $old->created_at = $today->copy()->subMonth()->setTime(7, 0);
    $old->save();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertStatus(200)
        ->assertJsonPath('summary.present', 3)
        ->assertJsonPath('summary.late', 1)
        ->assertJsonPath('summary.total', 3);
});

test('dashboard only shows announcements meant for the teacher office', function () {
    $mine = Office::create(['name' => 'MI', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    $other = Office::create(['name' => 'SMP', 'latitude' => -6.3, 'longitude' => 106.9, 'radius_meters' => 100]);
    $user = dashTeacher($mine);

    Announcement::create(['title' => 'Untuk semua', 'summary' => 'a', 'body' => 'b', 'is_active' => true, 'office_id' => null]);
    Announcement::create(['title' => 'Untuk MI', 'summary' => 'c', 'body' => 'd', 'is_active' => true, 'office_id' => $mine->id]);
    Announcement::create(['title' => 'Untuk SMP', 'summary' => 'e', 'body' => 'f', 'is_active' => true, 'office_id' => $other->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard')->assertStatus(200);

    $titles = array_column($response->json('announcements'), 'title');
    expect($titles)->toContain('Untuk semua')
        ->toContain('Untuk MI')
        ->not->toContain('Untuk SMP');

    $response->assertJsonStructure(['announcements' => [['id', 'title', 'summary', 'image_url']]]);
});

test('one teacher never sees another teacher figures', function () {
    $today = Carbon::parse('2026-07-20 09:00:00');
    Carbon::setTestNow($today);

    $a = dashTeacher();
    $b = dashTeacher();

    // Only B has checked in today.
    $att = Attendance::create([
        'user_id' => $b->id,
        'status' => AttendanceStatus::Late,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
    ]);
    $att->created_at = $today->copy()->setTime(7, 50);
    $att->save();

    $this->actingAs($a, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertStatus(200)
        ->assertJsonPath('user.id', $a->id)
        ->assertJsonPath('status', null)
        ->assertJsonPath('summary.present', 0);
});
