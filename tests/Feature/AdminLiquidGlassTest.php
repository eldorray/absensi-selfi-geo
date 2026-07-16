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

    preg_match_all('/<table\b[^>]*>/s', $source, $tables);

    expect($tables[0])->not->toBeEmpty();

    foreach ($tables[0] as $table) {
        expect($table)->toContain('admin-table');
    }
})->with('admin index views');

test('roles table body stays transparent inside the glass panel', function () {
    $source = file_get_contents(resource_path('views/admin/roles/index.blade.php'));

    expect($source)->not->toMatch('/<tbody\b[^>]*\bbg-white\b[^>]*\bdark:bg-gray-800\b[^>]*>/s');
});

dataset('admin compact text actions', [
    'attendance detail' => [
        'admin/attendances/index.blade.php',
        '/<a\b[^>]*class="[^"]*\badmin-button-primary\b[^"]*\bpx-[34]\b[^"]*"[^>]*>\s*Detail\s*<\/a>/s',
    ],
    'leave detail' => [
        'admin/leaves/index.blade.php',
        '/<a\b[^>]*class="[^"]*\badmin-button-primary\b[^"]*\bpx-[34]\b[^"]*"[^>]*>\s*Detail\s*<\/a>/s',
    ],
    'role edit' => [
        'admin/roles/index.blade.php',
        '/<a\b[^>]*class="[^"]*\badmin-button-primary\b[^"]*\bpx-[34]\b[^"]*"[^>]*>\s*Edit\s*<\/a>/s',
    ],
    'role delete' => [
        'admin/roles/index.blade.php',
        '/<button\b[^>]*class="[^"]*\badmin-button-danger\b[^"]*\bpx-[34]\b[^"]*"[^>]*>\s*Hapus\s*<\/button>/s',
    ],
]);

test('compact admin text actions retain intentional horizontal padding', function (string $view, string $pattern) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)->toMatch($pattern);
})->with('admin compact text actions');

dataset('admin icon action views', [
    'academic years' => 'admin/academic-years/index.blade.php',
    'announcements' => 'admin/announcements/index.blade.php',
    'offices' => 'admin/offices/index.blade.php',
    'users' => 'admin/users/index.blade.php',
]);

test('icon-only admin actions use fixed accessible targets', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));
    $matched = preg_match_all(
        '/<(?<tag>a|button)\b[^>]*class="(?<classes>[^"]*\badmin-button-(?:primary|success|danger)\b[^"]*)"[^>]*>\s*<svg\b(?:(?!<\/svg>).)*<\/svg>\s*<\/\k<tag>>/s',
        $source,
        $actions,
    );

    expect($matched)->toBeGreaterThan(0);

    foreach ($actions['classes'] as $classes) {
        expect($classes)
            ->toContain('size-11')
            ->toContain('p-0');
    }
})->with('admin icon action views');

dataset('admin form surface views', [
    'academic year create' => 'admin/academic-years/create.blade.php',
    'academic year edit' => 'admin/academic-years/edit.blade.php',
    'announcement create' => 'admin/announcements/create.blade.php',
    'announcement edit' => 'admin/announcements/edit.blade.php',
    'office create' => 'admin/offices/create.blade.php',
    'office edit' => 'admin/offices/edit.blade.php',
    'role create' => 'admin/roles/create.blade.php',
    'role edit' => 'admin/roles/edit.blade.php',
    'user create' => 'admin/users/create.blade.php',
    'user edit' => 'admin/users/edit.blade.php',
    'work schedule edit' => 'admin/work-schedules/edit.blade.php',
]);

test('admin form views adopt semantic glass surfaces', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)
        ->toContain('admin-page-header')
        ->toContain('admin-glass-panel');
})->with('admin form surface views');

dataset('admin form field views', [
    'academic year create' => 'admin/academic-years/create.blade.php',
    'academic year edit' => 'admin/academic-years/edit.blade.php',
    'announcement fields' => 'admin/announcements/_form.blade.php',
    'office create' => 'admin/offices/create.blade.php',
    'office edit' => 'admin/offices/edit.blade.php',
    'role create' => 'admin/roles/create.blade.php',
    'role edit' => 'admin/roles/edit.blade.php',
    'user create' => 'admin/users/create.blade.php',
    'user edit' => 'admin/users/edit.blade.php',
    'work schedule edit' => 'admin/work-schedules/edit.blade.php',
]);

test('admin form controls adopt semantic field classes', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    preg_match_all('/<(?:input|select|textarea)\b(?:(?:{{.*?}})|(?:\?->|->)|[^>])*>/s', $source, $controls);

    expect($controls[0])->not->toBeEmpty();

    foreach ($controls[0] as $control) {
        if (preg_match('/<input\b[^>]*\btype=["\']hidden["\']/i', $control)) {
            continue;
        }

        if (preg_match('/<input\b[^>]*\btype=["\']checkbox["\']/i', $control)) {
            test()->assertMatchesRegularExpression(
                '/\badmin-(?:checkbox|toggle)\b/',
                $control,
                "{$view}: checkbox is missing an admin checkbox/toggle class: {$control}",
            );

            continue;
        }

        test()->assertStringContainsString(
            'admin-field',
            $control,
            "{$view}: form control is missing admin-field: {$control}",
        );
    }
})->with('admin form field views');

test('admin form views adopt semantic action classes', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)
        ->toContain('admin-button-primary')
        ->toContain('admin-button-secondary');
})->with('admin form surface views');
