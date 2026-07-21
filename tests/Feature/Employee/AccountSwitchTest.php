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
