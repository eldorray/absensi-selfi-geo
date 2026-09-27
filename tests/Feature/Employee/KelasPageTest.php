<?php

// tests/Feature/Employee/KelasPageTest.php

test('my class views use the guru components', function () {
    $index = file_get_contents(resource_path('views/attendance/my-class/index.blade.php'));
    $show = file_get_contents(resource_path('views/attendance/my-class/show.blade.php'));

    expect($index)->toContain('activeTab="kelas"')->toContain('g-list__item')->not->toContain('theme-')
        ->and($show)->toContain('x-guru.card')->not->toContain('solid-panel');
});
