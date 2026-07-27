<?php

declare(strict_types=1);

use App\Models\Office;
use App\Models\Role;
use App\Models\User;

function apiTeacher(?Office $office = null): User
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

function apiOffice(string $name = 'MI Daarul Hikmah'): Office
{
    return Office::create([
        'name' => $name,
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);
}

test('login returns a token and the user payload', function () {
    $office = apiOffice();
    $user = apiTeacher($office);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.name', $user->name)
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonPath('user.office', 'MI Daarul Hikmah')
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'office']]);

    expect($response->json('token'))->toBeString()->not->toBeEmpty();
    expect($user->fresh()->tokens()->count())->toBe(1);
});

test('login reports a null office when the teacher has none', function () {
    $user = apiTeacher();

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
        ->assertStatus(200)
        ->assertJsonPath('user.office', null);
});

test('login never leaks sensitive user fields', function () {
    $user = apiTeacher();

    $response = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password']);

    $payload = $response->json('user');
    foreach (['password', 'visible_password', 'remember_token', 'role_id', 'office_id'] as $hidden) {
        expect($payload)->not->toHaveKey($hidden);
    }
});

test('login with a wrong password fails with the standard 422 shape', function () {
    $user = apiTeacher();

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah'])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['email']]);

    expect($user->fresh()->tokens()->count())->toBe(0);
});

test('login requires an email and a password', function () {
    $this->postJson('/api/login', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('logout revokes only the token used for the request', function () {
    $user = apiTeacher();
    $keep = $user->createToken('other-device')->plainTextToken;
    $revoke = $user->createToken('this-device')->plainTextToken;

    $this->withToken($revoke)->postJson('/api/logout')->assertStatus(200);

    expect($user->fresh()->tokens()->count())->toBe(1);

    // The guard instance caches the user it resolved, and the container lives
    // across calls inside one test. Real clients get a fresh container per
    // request, so drop the cached guards to reproduce that here.
    $this->app['auth']->forgetGuards();
    $this->withToken($keep)->getJson('/api/dashboard')->assertStatus(200);

    $this->app['auth']->forgetGuards();
    $this->withToken($revoke)->getJson('/api/dashboard')->assertStatus(401);
});

test('protected endpoints reject a request without a token', function () {
    $this->getJson('/api/dashboard')->assertStatus(401);
    $this->postJson('/api/logout')->assertStatus(401);
});
