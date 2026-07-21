<?php

use App\Models\AccountSwitchLog;
use App\Models\Role;
use App\Models\User;

function logAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

function logGuru(string $name): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id]);
}

test('admin sees the account switch log entries', function () {
    $a = logGuru('Guru MI');
    $b = logGuru('Guru SMP');
    AccountSwitchLog::create(['from_user_id' => $a->id, 'to_user_id' => $b->id, 'ip_address' => '10.0.0.1']);

    $this->actingAs(logAdmin())
        ->get(route('admin.account-switches.index'))
        ->assertStatus(200)
        ->assertSee('Guru MI')
        ->assertSee('Guru SMP')
        ->assertSee('10.0.0.1');
});

test('non-admin cannot open the account switch log page', function () {
    $this->actingAs(logGuru('Guru MI'))
        ->get(route('admin.account-switches.index'))
        ->assertRedirect(route('attendance.dashboard'));
});
