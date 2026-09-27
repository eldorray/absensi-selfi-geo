<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Carbon::setTestNow(Carbon::parse('2026-07-20 07:00:00')); // Monday
});

afterEach(fn () => Carbon::setTestNow());

function livenessUser(): User
{
    $office = Office::create(['name' => 'Kantor SMP', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);

    WorkSchedule::create([
        'user_id' => $user->id,
        'day' => 'senin',
        'check_in_time' => '07:00:00',
        'check_out_time' => '07:00:00',
        'is_active' => true,
    ]);

    return $user;
}

function livenessPayload(array $extra = []): array
{
    return array_merge([
        'office_id' => User::latest('id')->first()->office_id,
        'latitude' => -6.2,
        'longitude' => 106.8,
        'image_base64' => 'data:image/jpeg;base64,'.base64_encode('fake-jpeg'),
    ], $extra);
}

test('a manual fallback check-in is flagged as not liveness-verified', function () {
    $user = livenessUser();

    $this->actingAs($user)
        ->postJson(route('attendance.store'), livenessPayload(['liveness_verified' => '0']))
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(Attendance::where('user_id', $user->id)->first()->liveness_verified)->toBeFalse();
});

test('a blink-verified check-in is flagged as verified', function () {
    $user = livenessUser();

    $this->actingAs($user)
        ->postJson(route('attendance.store'), livenessPayload(['liveness_verified' => '1']))
        ->assertOk();

    expect(Attendance::where('user_id', $user->id)->first()->liveness_verified)->toBeTrue();
});

test('a check-in without the field leaves the flag unknown', function () {
    $user = livenessUser();

    $this->actingAs($user)->postJson(route('attendance.store'), livenessPayload())->assertOk();

    expect(Attendance::where('user_id', $user->id)->first()->liveness_verified)->toBeNull();
});

test('a manual fallback check-out is flagged separately', function () {
    $user = livenessUser();
    Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'x.jpg',
        'liveness_verified' => true,
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 0,
    ]);
    Carbon::setTestNow(Carbon::parse('2026-07-20 16:00:00'));

    $this->actingAs($user)
        ->postJson(route('attendance.checkout.store'), livenessPayload(['liveness_verified' => '0']))
        ->assertOk();

    $attendance = Attendance::where('user_id', $user->id)->first();
    expect($attendance->liveness_verified)->toBeTrue();
    expect($attendance->check_out_liveness_verified)->toBeFalse();
});

test('admin sees the manual flag on the attendance detail page', function () {
    $user = livenessUser();
    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'x.jpg',
        'liveness_verified' => false,
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 0,
    ]);
    $adminRole = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $this->actingAs($admin)
        ->get(route('admin.attendances.show', $attendance))
        ->assertOk()
        ->assertSee('Foto manual, periksa wajah');
});

test('the selfie page offers the manual fallback and sends the flag', function () {
    $this->actingAs(livenessUser())
        ->get(route('attendance.selfie'))
        ->assertOk()
        ->assertSee('Ambil Foto Manual')
        ->assertSee("formData.append('liveness_verified'", false);
});

test('the daily report flags manual photos and keeps apostrophe names JS-safe', function () {
    $user = livenessUser();
    $user->update(['name' => "Nur'aini"]);
    Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'x.jpg',
        'liveness_verified' => false,
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 0,
    ]);
    $adminRole = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    $this->actingAs(User::factory()->create(['role_id' => $adminRole->id]))
        ->get(route('admin.reports.daily', ['date' => '2026-07-20']))
        ->assertOk()
        ->assertSee('>Manual</span>', false)
        ->assertDontSee("'Foto Masuk - Nur&#039;aini'", false);
});
