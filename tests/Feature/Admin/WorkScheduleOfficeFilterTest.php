<?php

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
