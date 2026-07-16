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
    session()->flash('status', 'Admin settings updated.');

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('admin-shell', escape: false)
        ->assertSee('admin-main', escape: false)
        ->assertSee('admin-header', escape: false)
        ->assertSee('admin-sidebar', escape: false)
        ->assertSee('admin-nav-link', escape: false)
        ->assertSee('admin-glass-popover', escape: false)
        ->assertSee('aria-current="page"', escape: false)
        ->assertSee('admin-alert-success', escape: false);
});

test('shared non-admin pages do not expose the admin shell', function () {
    $this->actingAs(liquidGlassUser(admin: false));

    $this->get(route('settings.profile.edit'))
        ->assertOk()
        ->assertDontSee('admin-shell', escape: false)
        ->assertDontSee('admin-main', escape: false)
        ->assertDontSee('admin-header', escape: false)
        ->assertDontSee('admin-sidebar', escape: false)
        ->assertDontSee('admin-nav-link', escape: false)
        ->assertDontSee('admin-glass-popover', escape: false);
});

dataset('admin index views', [
    'dashboard' => 'admin/dashboard.blade.php',
    'academic years' => 'admin/academic-years/index.blade.php',
    'announcements' => 'admin/announcements/index.blade.php',
    'attendances' => 'admin/attendances/index.blade.php',
    'leaves' => 'admin/leaves/index.blade.php',
    'offices' => 'admin/offices/index.blade.php',
    'daily report' => 'admin/reports/daily.blade.php',
    'monthly report' => 'admin/reports/monthly.blade.php',
    'roles' => 'admin/roles/index.blade.php',
    'users' => 'admin/users/index.blade.php',
    'work schedules' => 'admin/work-schedules/index.blade.php',
]);

test('admin index views adopt semantic glass surfaces', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)
        ->toContain('admin-page-header')
        ->toContain('admin-glass-panel');

    if (str_contains($source, '<table')) {
        expect($source)->toContain('admin-table');
    }
})->with('admin index views');
