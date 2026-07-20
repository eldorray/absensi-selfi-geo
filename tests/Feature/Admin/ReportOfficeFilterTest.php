<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

function officeAdmin(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'administrator'],
        ['name' => 'Administrator', 'is_admin' => true],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

function officeEmployee(string $name, Office $office): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    $user = User::factory()->create([
        'name' => $name,
        'role_id' => $role->id,
        'office_id' => $office->id,
    ]);

    $att = Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
    ]);
    $att->created_at = Carbon::today()->setTime(7, 0);
    $att->save();

    return $user;
}

function mkOffice(string $name): Office
{
    return Office::create([
        'name' => $name,
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);
}

test('daily report filtered by office shows only that office employees', function () {
    $smp = mkOffice('SMP');
    $mi = mkOffice('MI');
    officeEmployee('Guru SMP Satu', $smp);
    officeEmployee('Guru MI Satu', $mi);

    $this->actingAs(officeAdmin());

    $this->get(route('admin.reports.daily', [
        'date' => Carbon::today()->format('Y-m-d'),
        'office_id' => $smp->id,
    ]))
        ->assertStatus(200)
        ->assertSee('Guru SMP Satu')
        ->assertDontSee('Guru MI Satu');
});

test('daily report without office filter shows all employees', function () {
    $smp = mkOffice('SMP');
    $mi = mkOffice('MI');
    officeEmployee('Guru SMP Satu', $smp);
    officeEmployee('Guru MI Satu', $mi);

    $this->actingAs(officeAdmin());

    $this->get(route('admin.reports.daily', ['date' => Carbon::today()->format('Y-m-d')]))
        ->assertStatus(200)
        ->assertSee('Guru SMP Satu')
        ->assertSee('Guru MI Satu');
});

test('monthly report filtered by office shows only that office employees', function () {
    $smp = mkOffice('SMP');
    $mi = mkOffice('MI');
    officeEmployee('Guru SMP Satu', $smp);
    officeEmployee('Guru MI Satu', $mi);

    $this->actingAs(officeAdmin());

    $this->get(route('admin.reports.monthly', [
        'start_date' => Carbon::today()->startOfMonth()->format('Y-m-d'),
        'end_date' => Carbon::today()->endOfMonth()->format('Y-m-d'),
        'office_id' => $smp->id,
    ]))
        ->assertStatus(200)
        ->assertSee('Guru SMP Satu')
        ->assertDontSee('Guru MI Satu');
});

test('daily pdf export with office filter returns a pdf', function () {
    $smp = mkOffice('SMP');
    officeEmployee('Guru SMP Satu', $smp);

    $this->actingAs(officeAdmin());

    $response = $this->get(route('admin.reports.daily.export-pdf', [
        'date' => Carbon::today()->format('Y-m-d'),
        'office_id' => $smp->id,
    ]));

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('an invalid office filter is ignored and shows all employees', function () {
    $smp = mkOffice('SMP');
    officeEmployee('Guru SMP Satu', $smp);

    $this->actingAs(officeAdmin());

    $this->get(route('admin.reports.daily', [
        'date' => Carbon::today()->format('Y-m-d'),
        'office_id' => 999999,
    ]))
        ->assertStatus(200)
        ->assertSee('Guru SMP Satu');
});
