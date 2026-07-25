<?php

use App\Models\AcademicYear;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;

function jadwalAdmin(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'administrator'],
        ['name' => 'Administrator', 'is_admin' => true],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

function jadwalOffice(string $name): Office
{
    return Office::create([
        'name' => $name,
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);
}

function jadwalGuru(string $name, Office $office): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    return User::factory()->create([
        'name' => $name,
        'role_id' => $role->id,
        'office_id' => $office->id,
    ]);
}

test('work schedules filtered by office shows only that office employees', function () {
    $smp = jadwalOffice('SMP');
    $mi = jadwalOffice('MI');
    jadwalGuru('Guru SMP Satu', $smp);
    jadwalGuru('Guru MI Satu', $mi);

    $this->actingAs(jadwalAdmin())
        ->get(route('admin.work-schedules.index', ['office_id' => $smp->id]))
        ->assertStatus(200)
        ->assertSee('Guru SMP Satu')
        ->assertDontSee('Guru MI Satu');
});

test('work schedules without an office filter shows all employees', function () {
    $smp = jadwalOffice('SMP');
    $mi = jadwalOffice('MI');
    jadwalGuru('Guru SMP Satu', $smp);
    jadwalGuru('Guru MI Satu', $mi);

    $this->actingAs(jadwalAdmin())
        ->get(route('admin.work-schedules.index'))
        ->assertStatus(200)
        ->assertSee('Guru SMP Satu')
        ->assertSee('Guru MI Satu');
});

test('an invalid office filter is ignored on work schedules', function () {
    $smp = jadwalOffice('SMP');
    jadwalGuru('Guru SMP Satu', $smp);

    $this->actingAs(jadwalAdmin())
        ->get(route('admin.work-schedules.index', ['office_id' => 999999]))
        ->assertStatus(200)
        ->assertSee('Guru SMP Satu');
});

test('work schedules lists all office employees without a 10-row page cap', function () {
    $smp = jadwalOffice('SMP');
    for ($i = 1; $i <= 12; $i++) {
        jadwalGuru(sprintf('Guru %02d', $i), $smp); // zero-padded so "Guru 12" sorts last
    }

    $this->actingAs(jadwalAdmin())
        ->get(route('admin.work-schedules.index', ['office_id' => $smp->id]))
        ->assertStatus(200)
        ->assertSee('Guru 01')
        ->assertSee('Guru 12'); // last by name → would be on page 2 under the old paginate(10)
});

test('work schedules page renders a live search input', function () {
    jadwalOffice('SMP');

    $this->actingAs(jadwalAdmin())
        ->get(route('admin.work-schedules.index'))
        ->assertStatus(200)
        ->assertSee('Cari nama');
});

test('saving a schedule returns to the active office filter', function () {
    $mi = jadwalOffice('MI Daarul Hikmah');
    $guru = jadwalGuru('Guru MI', $mi);
    AcademicYear::create([
        'name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);

    $this->actingAs(jadwalAdmin())
        ->put(route('admin.work-schedules.update', ['user' => $guru, 'office_id' => $mi->id]), [
            'schedules' => ['senin' => ['check_in_time' => '07:00', 'check_out_time' => '12:00', 'is_active' => '1']],
        ])
        ->assertRedirect(route('admin.work-schedules.index', ['office_id' => $mi->id]));
});

test('the schedule edit link carries the active office filter', function () {
    $mi = jadwalOffice('MI Daarul Hikmah');
    $guru = jadwalGuru('Guru MI', $mi);

    $this->actingAs(jadwalAdmin())
        ->get(route('admin.work-schedules.index', ['office_id' => $mi->id]))
        ->assertStatus(200)
        ->assertSee(route('admin.work-schedules.edit', ['user' => $guru, 'office_id' => $mi->id]), false);
});
