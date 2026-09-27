<?php

use App\Models\Role;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('admins are redirected to the admin dashboard', function () {
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);
    $this->actingAs(User::factory()->create(['role_id' => $role->id]));

    $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
});

test('employees are redirected to the attendance dashboard', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')->assertRedirect(route('attendance.dashboard'));
});
