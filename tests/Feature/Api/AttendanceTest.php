<?php

declare(strict_types=1);

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

afterEach(fn () => Carbon::setTestNow());

function attOffice(): Office
{
    return Office::create([
        'name' => 'MI Daarul Hikmah',
        'latitude' => -6.200000,
        'longitude' => 106.800000,
        'radius_meters' => 100,
    ]);
}

/** A teacher assigned to $office, with a 07:00-15:00 schedule on $day. */
function attTeacher(Office $office, Carbon $day): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);

    $year = AcademicYear::firstOrCreate(
        ['name' => '2026/2027'],
        ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true],
    );

    WorkSchedule::create([
        'user_id' => $user->id,
        'academic_year_id' => $year->id,
        'day' => strtolower($day->locale('id')->dayName),
        'check_in_time' => '07:00:00',
        'check_out_time' => '15:00:00',
        'is_active' => true,
    ]);

    return $user;
}

function attPhoto(): UploadedFile
{
    return UploadedFile::fake()->image('selfie.jpg', 600, 800);
}

test('check-in records attendance and reports on_time inside the window', function () {
    $day = Carbon::parse('2026-07-20'); // Monday
    Carbon::setTestNow($day->copy()->setTime(6, 55));
    $office = attOffice();
    $user = attTeacher($office, $day);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => attPhoto(),
        'latitude' => -6.200000,
        'longitude' => 106.800000,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'on_time')
        ->assertJsonPath('check_in_time', '06:55')
        ->assertJsonStructure(['status', 'check_in_time', 'message']);

    $attendance = Attendance::where('user_id', $user->id)->firstOrFail();
    expect($attendance->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($attendance->image_path);
});

test('check-in past the grace period is recorded as late', function () {
    $day = Carbon::parse('2026-07-20');
    $office = attOffice();
    Carbon::setTestNow($day->copy()->setTime(6, 0));
    $user = attTeacher($office, $day);

    WorkSetting::current()->update(['after_check_in' => 10, 'late_limit' => 240]);

    // 07:30 is past 07:00 + 10 minutes of grace.
    Carbon::setTestNow($day->copy()->setTime(7, 30));

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => attPhoto(),
        'latitude' => -6.200000,
        'longitude' => 106.800000,
    ])->assertStatus(201)->assertJsonPath('status', 'late');
});

test('a second check-in on the same day is rejected', function () {
    $day = Carbon::parse('2026-07-20');
    Carbon::setTestNow($day->copy()->setTime(6, 55));
    $office = attOffice();
    $user = attTeacher($office, $day);

    $payload = fn () => [
        'photo' => attPhoto(),
        'latitude' => -6.200000,
        'longitude' => 106.800000,
    ];

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', $payload())->assertStatus(201);

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', $payload())
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);

    expect(Attendance::where('user_id', $user->id)->count())->toBe(1);
});

test('check-in from outside the office radius is rejected', function () {
    $day = Carbon::parse('2026-07-20');
    Carbon::setTestNow($day->copy()->setTime(6, 55));
    $office = attOffice();
    $user = attTeacher($office, $day);

    // Roughly 1.5 km away, well beyond the 100 m radius.
    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => attPhoto(),
        'latitude' => -6.213500,
        'longitude' => 106.800000,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['location']);

    expect(Attendance::where('user_id', $user->id)->count())->toBe(0);
});

test('check-in validates the photo and the coordinates', function () {
    $day = Carbon::parse('2026-07-20');
    Carbon::setTestNow($day->copy()->setTime(6, 55));
    $office = attOffice();
    $user = attTeacher($office, $day);

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['photo', 'latitude', 'longitude']);

    // A PDF is not a selfie.
    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])->assertStatus(422)->assertJsonValidationErrors(['photo']);

    // Over the 4 MB ceiling.
    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => UploadedFile::fake()->create('huge.jpg', 5000, 'image/jpeg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])->assertStatus(422)->assertJsonValidationErrors(['photo']);
});

test('checkout stamps the time on today record', function () {
    $day = Carbon::parse('2026-07-20');
    $office = attOffice();
    Carbon::setTestNow($day->copy()->setTime(6, 55));
    $user = attTeacher($office, $day);

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => attPhoto(),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])->assertStatus(201);

    Carbon::setTestNow($day->copy()->setTime(15, 3));

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', [
        'photo' => attPhoto(),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])
        ->assertStatus(200)
        ->assertJsonPath('check_out_time', '15:03')
        ->assertJsonStructure(['check_out_time', 'message']);

    expect(Attendance::where('user_id', $user->id)->first()->check_out_at)->not->toBeNull();
});

test('checkout works without a photo or coordinates', function () {
    $day = Carbon::parse('2026-07-20');
    $office = attOffice();
    Carbon::setTestNow($day->copy()->setTime(6, 55));
    $user = attTeacher($office, $day);

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => attPhoto(),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])->assertStatus(201);

    Carbon::setTestNow($day->copy()->setTime(15, 10));

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', [])
        ->assertStatus(200)
        ->assertJsonPath('check_out_time', '15:10');
});

test('checkout without a check-in is rejected', function () {
    $day = Carbon::parse('2026-07-20');
    Carbon::setTestNow($day->copy()->setTime(15, 10));
    $office = attOffice();
    $user = attTeacher($office, $day);

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', [])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

test('a second checkout is rejected', function () {
    $day = Carbon::parse('2026-07-20');
    $office = attOffice();
    Carbon::setTestNow($day->copy()->setTime(6, 55));
    $user = attTeacher($office, $day);

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => attPhoto(),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])->assertStatus(201);

    Carbon::setTestNow($day->copy()->setTime(15, 10));
    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', [])->assertStatus(200);

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', [])
        ->assertStatus(422);
});

test('checkout never touches another teacher record', function () {
    $day = Carbon::parse('2026-07-20');
    $office = attOffice();
    Carbon::setTestNow($day->copy()->setTime(6, 55));

    $a = attTeacher($office, $day);
    $b = attTeacher($office, $day);

    // Only B has checked in.
    $this->actingAs($b, 'sanctum')->postJson('/api/attendance', [
        'photo' => attPhoto(),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])->assertStatus(201);

    Carbon::setTestNow($day->copy()->setTime(15, 10));

    // A has nothing to check out of, and B's record must stay open.
    $this->actingAs($a, 'sanctum')->postJson('/api/attendance/checkout', [])->assertStatus(422);

    expect(Attendance::where('user_id', $b->id)->first()->check_out_at)->toBeNull();
});
