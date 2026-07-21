<?php

use App\Models\AccountSwitchLog;
use App\Models\Role;
use App\Models\User;

function switchGuru(string $name): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id]);
}

test('account switch log stores from/to and exposes relations', function () {
    $a = switchGuru('A');
    $b = switchGuru('B');

    $log = AccountSwitchLog::create([
        'from_user_id' => $a->id,
        'to_user_id' => $b->id,
        'ip_address' => '127.0.0.1',
    ]);

    expect($log->fromUser->id)->toBe($a->id)
        ->and($log->toUser->id)->toBe($b->id)
        ->and($log->ip_address)->toBe('127.0.0.1');
});

test('a user can switch to a linked account', function () {
    $mi = switchGuru('Guru MI');
    $smp = switchGuru('Guru SMP');
    $mi->linkedAccounts()->attach($smp->id);
    $smp->linkedAccounts()->attach($mi->id);

    $this->actingAs($mi)
        ->post(route('account.switch'), ['target_id' => $smp->id])
        ->assertRedirect(route('attendance.dashboard'))
        ->assertSessionHas('success');

    $this->assertAuthenticatedAs($smp);
    expect(AccountSwitchLog::where('from_user_id', $mi->id)->where('to_user_id', $smp->id)->count())->toBe(1);
});

test('switching to a non-linked account is forbidden', function () {
    $mi = switchGuru('Guru MI');
    $other = switchGuru('Orang Lain');

    $this->actingAs($mi)
        ->post(route('account.switch'), ['target_id' => $other->id])
        ->assertForbidden();

    $this->assertAuthenticatedAs($mi);
    expect(AccountSwitchLog::count())->toBe(0);
});

test('switching to an admin account is forbidden even if linked', function () {
    $mi = switchGuru('Guru MI');
    $adminRole = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $mi->linkedAccounts()->attach($admin->id);

    $this->actingAs($mi)
        ->post(route('account.switch'), ['target_id' => $admin->id])
        ->assertForbidden();

    $this->assertAuthenticatedAs($mi);
});

test('the switch endpoint is rate limited', function () {
    $mi = switchGuru('Guru MI');
    $other = switchGuru('Orang Lain');

    $this->actingAs($mi);

    for ($i = 0; $i < 10; $i++) {
        $this->post(route('account.switch'), ['target_id' => $other->id])->assertForbidden();
    }

    $this->post(route('account.switch'), ['target_id' => $other->id])->assertStatus(429);
});

test('the dashboard shows a switch control for linked accounts', function () {
    $mi = switchGuru('Guru MI');
    $smp = switchGuru('Guru SMP');
    $mi->linkedAccounts()->attach($smp->id);

    $this->actingAs($mi)
        ->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('Ganti Akun')
        ->assertSee('Guru SMP');
});
