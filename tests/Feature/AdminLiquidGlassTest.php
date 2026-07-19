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
        ->toMatch('/admin-page-header|<x-admin\.page-header\b/')
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
        ->toMatch('/admin-page-header|<x-admin\.page-header\b/')
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

dataset('admin fields with conditional error borders', [
    'academic year date' => ['admin/academic-years/create.blade.php', 'start_date'],
    'announcement image' => ['admin/announcements/_form.blade.php', 'image'],
]);

test('admin fields retain conditional error border classes', function (string $view, string $field) {
    $source = file_get_contents(resource_path("views/{$view}"));

    preg_match_all('/<(?:input|select|textarea)\b(?:(?:{{.*?}})|(?:\?->|->)|[^>])*>/s', $source, $controls);

    $control = collect($controls[0])->first(
        fn (string $tag): bool => str_contains($tag, "name=\"{$field}\""),
    );

    expect($control)
        ->toBeString()
        ->toContain('admin-field')
        ->toContain("@error('{$field}') border-red-500 @enderror");
})->with('admin fields with conditional error borders');

test('work schedule toggle retains peer behavior with a semantic visible track', function () {
    $source = file_get_contents(resource_path('views/admin/work-schedules/edit.blade.php'));
    $matched = preg_match(
        '/(?<checkbox><input\b(?:(?:{{.*?}})|(?:\?->|->)|[^>])*type="checkbox"(?:(?:{{.*?}})|(?:\?->|->)|[^>])*>)[\t\r\n ]*(?<track><div\b[^>]*>)/s',
        $source,
        $toggle,
    );

    expect($matched)->toBe(1);
    expect($toggle['checkbox'])
        ->toContain('sr-only')
        ->toContain('peer')
        ->toContain('admin-toggle')
        ->not->toContain('admin-checkbox');
    expect($toggle['track'])->toContain('admin-toggle-track');
});

test('admin form views adopt semantic action classes', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)
        ->toContain('admin-button-primary')
        ->toContain('admin-button-secondary');
})->with('admin form surface views');

dataset('admin detail views', [
    'attendance detail' => 'admin/attendances/show.blade.php',
    'leave detail' => 'admin/leaves/show.blade.php',
]);

test('admin detail views adopt semantic glass surfaces', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)
        ->toMatch('/admin-page-header|<x-admin\.page-header\b/')
        ->toContain('admin-glass-panel')
        ->not->toContain('bg-white dark:bg-gray-800');
})->with('admin detail views');

test('admin detail views retain semantic status and approval mappings', function () {
    $attendance = file_get_contents(resource_path('views/admin/attendances/show.blade.php'));
    $leave = file_get_contents(resource_path('views/admin/leaves/show.blade.php'));

    expect($attendance)->toContain(
        "{{ \$attendance->status->value === 'present' ? 'admin-status-success' : 'admin-status-warning' }}",
    );
    expect($leave)
        ->toContain("'pending' => 'admin-status-warning'")
        ->toContain("'approved' => 'admin-status-success'")
        ->toContain("'rejected' => 'admin-status-danger'")
        ->toMatch('/<button\b[^>]*class="[^"]*\badmin-button-success\b[^"]*"[^>]*onclick="return confirm\(\'Setujui pengajuan ini\?\'\)"/s')
        ->toMatch('/<button\b[^>]*class="[^"]*\badmin-button-danger\b[^"]*"[^>]*onclick="return confirm\(\'Tolak pengajuan ini\?\'\)"/s');

    $matched = preg_match(
        '/<a href="\{\{ route\(\'admin\.attendances\.index\'\) \}\}" class="(?<classes>[^"]*)"/s',
        $attendance,
        $backLink,
    );

    expect($matched)->toBe(1);
    expect($backLink['classes'])
        ->toContain('admin-button-secondary')
        ->toContain('px-3');
});

dataset('leave type semantic mappings', [
    'sakit' => ['sakit', 'admin-status-danger'],
    'izin' => ['izin', 'admin-status-info'],
    'cuti' => ['cuti', 'admin-status-info'],
]);

test('leave types retain their semantic mappings', function (string $type, string $expectedClass) {
    $source = file_get_contents(resource_path('views/admin/leaves/show.blade.php'));
    $matched = preg_match(
        '/\{\{ \$leave->type === \'(?<dangerType>[^\']+)\' \? \'(?<dangerClass>[^\']+)\' : \'(?<otherClass>[^\']+)\' \}\}/',
        $source,
        $mapping,
    );

    expect($matched)->toBe(1);

    $actualClass = $type === $mapping['dangerType']
        ? $mapping['dangerClass']
        : $mapping['otherClass'];

    expect($actualClass)->toBe($expectedClass);
})->with('leave type semantic mappings');

test('daily report photo modal adopts semantic glass classes and an accessible close target', function () {
    $source = file_get_contents(resource_path('views/admin/reports/daily.blade.php'));

    expect($source)
        ->toContain('admin-modal-overlay')
        ->toContain('admin-glass-modal')
        ->toContain('@open-photo-modal.window="open = true; imageUrl = $event.detail.url; title = $event.detail.title"')
        ->toContain('@click.away="open = false"')
        ->toContain('x-text="title"')
        ->toContain(':src="imageUrl"');

    $matched = preg_match(
        '/<button\b[^>]*@click="open = false"[^>]*class="(?<classes>[^"]*)"[^>]*>/s',
        $source,
        $closeButton,
    );

    expect($matched)->toBe(1);
    expect($closeButton['classes'])
        ->toContain('admin-button-secondary')
        ->toContain('size-11')
        ->toContain('p-0');
});

dataset('admin pdf report views', [
    'daily PDF' => 'admin/reports/daily-pdf.blade.php',
    'monthly PDF' => 'admin/reports/monthly-pdf.blade.php',
]);

test('pdf report templates stay isolated from admin screen classes', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)->not->toMatch('/\badmin-[a-z0-9-]+/i');
})->with('admin pdf report views');

dataset('direct admin feedback containers', [
    'leaves index success' => ['admin/leaves/index.blade.php', "session\\('success'\\)", 'admin-alert-success'],
    'leaves index error' => ['admin/leaves/index.blade.php', "session\\('error'\\)", 'admin-alert-danger'],
    'leave detail success' => ['admin/leaves/show.blade.php', "session\\('success'\\)", 'admin-alert-success'],
    'leave detail error' => ['admin/leaves/show.blade.php', "session\\('error'\\)", 'admin-alert-danger'],
    'users success' => ['admin/users/index.blade.php', "session\\('success'\\)", 'admin-alert-success'],
    'users error' => ['admin/users/index.blade.php', "session\\('error'\\)", 'admin-alert-danger'],
    'offices success' => ['admin/offices/index.blade.php', "session\\('success'\\)", 'admin-alert-success'],
    'roles success' => ['admin/roles/index.blade.php', "session\\('success'\\)", 'admin-alert-success'],
    'roles errors' => ['admin/roles/index.blade.php', '\\$errors->any\\(\\)', 'admin-alert-danger'],
    'work schedules success' => ['admin/work-schedules/index.blade.php', "session\\('success'\\)", 'admin-alert-success'],
    'work schedule edit errors' => ['admin/work-schedules/edit.blade.php', '\\$errors->any\\(\\)', 'admin-alert-danger'],
]);

test('direct admin feedback containers use semantic alert classes', function (string $view, string $condition, string $semanticClass) {
    $source = file_get_contents(resource_path("views/{$view}"));
    $matched = preg_match(
        "/@if\\s*\\(\\s*{$condition}\\s*\\)\\s*<div\\b[^>]*class=\"(?<classes>[^\"]*)\"/s",
        $source,
        $alert,
    );

    expect($matched, "Missing direct feedback container in {$view}")->toBe(1);
    expect($alert['classes'])->toContain($semanticClass);
})->with('direct admin feedback containers');

test('leave rejection feedback panels use the semantic danger alert', function () {
    $source = file_get_contents(resource_path('views/admin/leaves/show.blade.php'));
    $matched = preg_match_all(
        '/<div\b[^>]*class="(?<classes>[^"]*\bbg-red-50\b[^"]*\bdark:bg-red-900\/20\b[^"]*)"[^>]*>/s',
        $source,
        $panels,
    );

    expect($matched)->toBe(2);

    foreach ($panels['classes'] as $classes) {
        expect($classes)->toContain('admin-alert-danger');
    }
});
