<?php

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('the leave create page renders with the type selector', function () {
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $guru = User::factory()->create(['role_id' => $role->id]);

    actingAs($guru)->get(route('attendance.leaves.create'))
        ->assertStatus(200)
        ->assertSee('Jenis Perizinan')
        ->assertSee('peer-checked:opacity-100', false);
});
