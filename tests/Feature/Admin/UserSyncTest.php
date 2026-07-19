<?php

use App\Models\Role;
use App\Models\User;

test('user has mass-assignable nip and nik columns', function () {
    $role = Role::create(['name' => 'Guru', 'slug' => 'guru', 'is_admin' => false]);

    $user = User::create([
        'name' => 'Budi',
        'email' => 'budi@guru.local',
        'password' => 'secret-hash',
        'role_id' => $role->id,
        'nip' => '199001011000',
        'nik' => '3200010101900001',
    ]);

    expect($user->fresh())
        ->nip->toBe('199001011000')
        ->nik->toBe('3200010101900001');
});
