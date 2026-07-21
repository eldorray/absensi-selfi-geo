<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function linkGuru(string $name): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id]);
}

function linkAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('a user exposes its linked accounts through the pivot', function () {
    $mi = linkGuru('Guru MI');
    $smp = linkGuru('Guru SMP');

    $mi->linkedAccounts()->attach($smp->id);

    expect($mi->fresh()->linkedAccounts->pluck('id')->all())->toBe([$smp->id]);
});

test('admin links two non-admin accounts symmetrically', function () {
    $mi = linkGuru('Guru MI');
    $smp = linkGuru('Guru SMP');

    $this->actingAs(linkAdmin())
        ->put(route('admin.users.update', $mi), [
            'name' => $mi->name,
            'email' => $mi->email,
            'role_id' => $mi->role_id,
            'linked_accounts' => [$smp->id],
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($mi->fresh()->linkedAccounts->pluck('id')->all())->toBe([$smp->id])
        ->and($smp->fresh()->linkedAccounts->pluck('id')->all())->toBe([$mi->id]);
});

test('unchecking a link removes it on both sides', function () {
    $mi = linkGuru('Guru MI');
    $smp = linkGuru('Guru SMP');
    $mi->linkedAccounts()->attach($smp->id);
    $smp->linkedAccounts()->attach($mi->id);

    $this->actingAs(linkAdmin())
        ->put(route('admin.users.update', $mi), [
            'name' => $mi->name,
            'email' => $mi->email,
            'role_id' => $mi->role_id,
            'linked_accounts' => [],
        ]);

    expect($mi->fresh()->linkedAccounts)->toHaveCount(0)
        ->and($smp->fresh()->linkedAccounts)->toHaveCount(0);
});

test('admin accounts cannot be linked as a switch target', function () {
    $mi = linkGuru('Guru MI');
    $adminTarget = linkAdmin();

    $this->actingAs(linkAdmin())
        ->put(route('admin.users.update', $mi), [
            'name' => $mi->name,
            'email' => $mi->email,
            'role_id' => $mi->role_id,
            'linked_accounts' => [$adminTarget->id],
        ])
        ->assertSessionHasErrors('linked_accounts.0');

    expect($mi->fresh()->linkedAccounts)->toHaveCount(0);
});
