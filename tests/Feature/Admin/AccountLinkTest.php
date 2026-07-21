<?php

use App\Models\Role;
use App\Models\User;

function linkGuru(string $name): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id]);
}

test('a user exposes its linked accounts through the pivot', function () {
    $mi = linkGuru('Guru MI');
    $smp = linkGuru('Guru SMP');

    $mi->linkedAccounts()->attach($smp->id);

    expect($mi->fresh()->linkedAccounts->pluck('id')->all())->toBe([$smp->id]);
});
