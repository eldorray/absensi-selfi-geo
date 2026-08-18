<?php

use App\Models\Role;
use App\Models\User;

function settingsUser(bool $admin): User
{
    $role = Role::firstOrCreate(
        ['slug' => $admin ? 'admin-settings-material' : 'guru-settings-material'],
        ['name' => $admin ? 'Admin Settings' : 'Guru Settings', 'is_admin' => $admin],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

it('renders every admin settings page with Material You components', function (string $routeName, string $marker) {
    $this->actingAs(settingsUser(admin: true))
        ->get(route($routeName))
        ->assertSuccessful()
        ->assertSee('data-admin-ui="material-you-3"', false)
        ->assertSee('admin-shell', false)
        ->assertSee('settings-navigation-surface', false)
        ->assertSee('settings-content', false)
        ->assertSee($marker, false);
})->with([
    'profile' => ['settings.profile.edit', 'settings-danger-zone'],
    'password' => ['settings.password.edit', 'settings-form'],
    'appearance' => ['settings.appearance.edit', 'settings-theme-options'],
]);

it('keeps teacher settings outside the admin Material You scope', function () {
    $this->actingAs(settingsUser(admin: false))
        ->get(route('settings.profile.edit'))
        ->assertSuccessful()
        ->assertDontSee('data-admin-ui="material-you-3"', false)
        ->assertDontSee('admin-shell', false);
});

it('keeps the profile and password form contracts intact', function () {
    $admin = settingsUser(admin: true);

    $this->actingAs($admin)
        ->get(route('settings.profile.edit'))
        ->assertSee('action="'.route('settings.profile.update').'"', false)
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('action="'.route('settings.profile.destroy').'"', false)
        ->assertSee('name="_method" value="DELETE"', false);

    $this->actingAs($admin)
        ->get(route('settings.password.edit'))
        ->assertSee('action="'.route('settings.password.update').'"', false)
        ->assertSee('name="current_password"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false);
});

it('keeps all appearance choices and pressed-state synchronization', function () {
    $this->actingAs(settingsUser(admin: true))
        ->get(route('settings.appearance.edit'))
        ->assertSuccessful()
        ->assertSee('value="light"', false)
        ->assertSee('value="dark"', false)
        ->assertSee('value="system"', false)
        ->assertSee("setAppearance('light')", false)
        ->assertSee("setAppearance('dark')", false)
        ->assertSee("setAppearance('system')", false)
        ->assertSee('data-appearance="light"', false)
        ->assertSee('data-appearance="dark"', false)
        ->assertSee('data-appearance="system"', false)
        ->assertSee("button.setAttribute('aria-pressed'", false);
});
