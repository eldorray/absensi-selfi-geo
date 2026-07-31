<?php

declare(strict_types=1);

use App\Models\AccountSwitchLog;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;

function switchTeacher(?Office $office = null): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    return User::factory()->create([
        'role_id' => $role->id,
        'office_id' => $office?->id,
    ]);
}

test('switch returns a fresh token for the linked target account', function () {
    $current = switchTeacher();
    $target = switchTeacher();
    $current->linkedAccounts()->attach($target->id);

    $this->actingAs($current, 'sanctum')
        ->postJson('/api/account/switch', ['target_id' => $target->id])
        ->assertStatus(200)
        ->assertJsonPath('user.id', $target->id)
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);

    expect(AccountSwitchLog::query()
        ->where('from_user_id', $current->id)
        ->where('to_user_id', $target->id)
        ->exists())->toBeTrue();
});

test('switch revokes the token that made the request', function () {
    $current = switchTeacher();
    $target = switchTeacher();
    $current->linkedAccounts()->attach($target->id);

    $plainToken = $current->createToken('ios')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $plainToken")
        ->postJson('/api/account/switch', ['target_id' => $target->id])
        ->assertStatus(200);

    // DB-level check: the sanctum guard caches the resolved user between
    // requests inside a single test, so a follow-up HTTP call would
    // misleadingly return 200 even though the token row is gone.
    expect(\Laravel\Sanctum\PersonalAccessToken::findToken($plainToken))->toBeNull();
});

test('switch rejects an account that is not linked', function () {
    $current = switchTeacher();
    $stranger = switchTeacher();

    $this->actingAs($current, 'sanctum')
        ->postJson('/api/account/switch', ['target_id' => $stranger->id])
        ->assertForbidden();
});

test('switch rejects switching to oneself', function () {
    $current = switchTeacher();

    $this->actingAs($current, 'sanctum')
        ->postJson('/api/account/switch', ['target_id' => $current->id])
        ->assertForbidden();
});

test('switch rejects admin accounts', function () {
    $adminRole = Role::firstOrCreate(
        ['slug' => 'admin'],
        ['name' => 'Admin', 'is_admin' => true],
    );
    $current = switchTeacher();
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $current->linkedAccounts()->attach($admin->id);

    $this->actingAs($current, 'sanctum')
        ->postJson('/api/account/switch', ['target_id' => $admin->id])
        ->assertForbidden();
});

test('switch requires a token', function () {
    $this->postJson('/api/account/switch', ['target_id' => 1])
        ->assertUnauthorized();
});
