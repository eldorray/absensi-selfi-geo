<?php

declare(strict_types=1);

use App\Models\Office;
use App\Models\Role;
use App\Models\User;

function profTeacher(?Office $office = null): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create([
        'role_id' => $role->id,
        'office_id' => $office?->id,
    ]);
}

test('profile returns the signed-in teacher', function () {
    $office = Office::create(['name' => 'MI Daarul Hikmah', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    $user = profTeacher($office);
    $user->update(['phone' => '08123456789']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/profile')
        ->assertStatus(200)
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('name', $user->name)
        ->assertJsonPath('email', $user->email)
        ->assertJsonPath('office', 'MI Daarul Hikmah')
        ->assertJsonPath('phone', '08123456789')
        ->assertJsonStructure(['id', 'name', 'email', 'office', 'phone', 'avatar_url']);
});

test('profile never leaks sensitive fields', function () {
    $user = profTeacher();

    $payload = $this->actingAs($user, 'sanctum')->getJson('/api/profile')->json();

    foreach (['password', 'visible_password', 'remember_token', 'role_id', 'office_id'] as $hidden) {
        expect($payload)->not->toHaveKey($hidden);
    }
});

test('profile update changes the name and phone', function () {
    $user = profTeacher();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', ['name' => 'Fahmi Al Khudhorie', 'phone' => '08999888777'])
        ->assertStatus(200)
        ->assertJsonPath('name', 'Fahmi Al Khudhorie')
        ->assertJsonPath('phone', '08999888777');

    $user->refresh();
    expect($user->name)->toBe('Fahmi Al Khudhorie')
        ->and($user->phone)->toBe('08999888777');
});

test('profile update accepts clearing the phone', function () {
    $user = profTeacher();
    $user->update(['phone' => '0811111111']);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', ['name' => $user->name, 'phone' => null])
        ->assertStatus(200)
        ->assertJsonPath('phone', null);

    expect($user->refresh()->phone)->toBeNull();
});

test('profile update validates the name', function () {
    $user = profTeacher();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', ['name' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('profile update cannot change the email, role or office', function () {
    $office = Office::create(['name' => 'MI', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    $other = Office::create(['name' => 'SMP', 'latitude' => -6.3, 'longitude' => 106.9, 'radius_meters' => 100]);
    $adminRole = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    $user = profTeacher($office);
    $originalEmail = $user->email;

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/profile', [
            'name' => 'Baru',
            'email' => 'penyusup@example.com',
            'office_id' => $other->id,
            'role_id' => $adminRole->id,
        ])
        ->assertStatus(200);

    $user->refresh();
    expect($user->email)->toBe($originalEmail)
        ->and($user->office_id)->toBe($office->id)
        ->and($user->role_id)->not->toBe($adminRole->id);
});

test('profile update only ever touches the token own user', function () {
    $a = profTeacher();
    $b = profTeacher();
    $bName = $b->name;

    $this->actingAs($a, 'sanctum')
        ->putJson('/api/profile', ['name' => 'Diubah', 'id' => $b->id])
        ->assertStatus(200);

    expect($b->refresh()->name)->toBe($bName)
        ->and($a->refresh()->name)->toBe('Diubah');
});
