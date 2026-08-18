<?php

use App\Models\Role;
use App\Models\User;

it('renders the admin dashboard with its Material You 3 structure', function () {
    $role = Role::firstOrCreate(
        ['slug' => 'admin-material-you'],
        ['name' => 'Admin Material You', 'is_admin' => true],
    );
    $admin = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('data-admin-ui="material-you-3"', false)
        ->assertSee('admin-m3-hero', false)
        ->assertSee('admin-stat-tone-indigo', false)
        ->assertSee('admin-stat-tone-violet', false)
        ->assertSee('admin-stat-tone-emerald', false)
        ->assertSee('admin-stat-tone-amber', false)
        ->assertSee('admin-m3-action-office', false)
        ->assertSee('admin-m3-action-user', false)
        ->assertSee('admin-m3-action-report', false)
        ->assertSee('admin-m3-table-panel', false);
});

it('defines scoped emerald multitone tokens and solid Material You surfaces', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('.admin-shell[data-admin-ui="material-you-3"]')
        ->toContain('--admin-primary: #176b43;')
        ->toContain('--admin-primary-soft: #c8ecd5;')
        ->toContain('.dark .admin-shell[data-admin-ui="material-you-3"]')
        ->toContain('--admin-primary: #9bd5b5;')
        ->toContain('.admin-stat-tone-indigo')
        ->toContain('background: #eadcff !important;')
        ->toContain('.admin-m3-action-office')
        ->toContain('background: #dce6ff !important;')
        ->toContain('-webkit-backdrop-filter: none;')
        ->toContain('background-image: none !important;')
        ->toContain('@media (max-width: 767px)')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

it('keeps non-admin pages outside the Material You admin scope', function () {
    $role = Role::firstOrCreate(
        ['slug' => 'guru-material-you'],
        ['name' => 'Guru Material You', 'is_admin' => false],
    );
    $teacher = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($teacher)
        ->get(route('settings.profile.edit'))
        ->assertSuccessful()
        ->assertDontSee('data-admin-ui="material-you-3"', false);
});
