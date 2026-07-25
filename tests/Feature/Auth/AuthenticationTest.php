<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    // Non-admin users are redirected to attendance dashboard
    $response->assertRedirect(route('attendance.dashboard', absolute: false));
});

test('admin login lands on the admin dashboard and ignores an intended url', function () {
    $role = \App\Models\Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);
    $admin = User::factory()->create(['role_id' => $role->id]);

    $response = $this->withSession(['url.intended' => route('admin.users.index')])
        ->post('/login', ['email' => $admin->email, 'password' => 'password']);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
