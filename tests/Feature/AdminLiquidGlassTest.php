<?php

use App\Models\Role;
use App\Models\User;

function liquidGlassUser(bool $admin): User
{
    $role = Role::create([
        'name' => $admin ? 'Administrator' : 'Guru',
        'slug' => $admin ? 'administrator-liquid-glass' : 'guru-liquid-glass',
        'is_admin' => $admin,
    ]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('admin routes expose the liquid glass shell', function () {
    $this->actingAs(liquidGlassUser(admin: true));

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('admin-shell', escape: false)
        ->assertSee('admin-main', escape: false)
        ->assertSee('admin-header', escape: false)
        ->assertSee('admin-sidebar', escape: false)
        ->assertSee('admin-nav-link', escape: false)
        ->assertSee('admin-glass-popover', escape: false);
});

test('shared non-admin pages do not expose the admin shell', function () {
    $this->actingAs(liquidGlassUser(admin: false));

    $this->get(route('settings.profile.edit'))
        ->assertOk()
        ->assertDontSee('admin-shell', escape: false)
        ->assertDontSee('admin-main', escape: false)
        ->assertDontSee('admin-header', escape: false)
        ->assertDontSee('admin-sidebar', escape: false)
        ->assertDontSee('admin-nav-link', escape: false);
});
