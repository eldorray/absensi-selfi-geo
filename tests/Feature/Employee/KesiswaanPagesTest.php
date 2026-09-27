<?php

// tests/Feature/Employee/KesiswaanPagesTest.php

test('kesiswaan views use the guru components and translate raw values', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/kesiswaan/{$view}.blade.php"));

    expect($template)
        ->not->toContain('solid-panel')
        ->not->toContain('theme-')
        ->not->toContain('border-l-4')
        ->not->toContain('strtoupper($referral->urgency->value)')
        ->not->toContain("ucfirst(str_replace('_', ' ', \$referral->status->value))");
})->with(['index', 'show', 'my-referrals', 'referral-queue']);
