<?php

use App\Models\User;

// Temuan ronde cek visual 1 (Task 13).

test('avatar initials use at most two letters', function () {
    expect((new User(['name' => 'Guru Uji Visual']))->initials())->toBe('GU')
        ->and((new User(['name' => 'Siti']))->initials())->toBe('S');
});

test('pagination labels are Indonesian', function () {
    expect(__('pagination.previous'))->toContain('Sebelumnya')
        ->and(__('pagination.next'))->toContain('Berikutnya');
});

/** Whether the first rule for $selector sits at the top level of the stylesheet, outside any @layer/@media block. */
function guruRuleIsTopLevel(string $css, string $selector): bool
{
    $offset = strpos($css, $selector);
    if ($offset === false) {
        return false;
    }
    $before = substr($css, 0, $offset);

    return substr_count($before, '{') === substr_count($before, '}');
}

function guruCss(): string
{
    return file_get_contents(resource_path('css/guru.css'));
}

test('beranda date line lets a long office name wrap below the date', function () {
    expect(guruCss())->toMatch('/\.g-dateline\s*\{[^}]*flex-wrap:\s*wrap/');
});

test('neutral status chips stay visible on the page ground', function () {
    expect(guruCss())->toMatch('/\.g-status\s+\.g-chip--neutral\s*\{[^}]*background:\s*var\(--g-surface\)/');
});

test('pagination styling is unlayered so framework dark utilities cannot override it', function () {
    expect(guruRuleIsTopLevel(guruCss(), '.guru .g-pager nav[role="navigation"] a'))->toBeTrue();
});

test('bottom camera brackets sit above the face prompt', function () {
    $role = App\Models\Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $teacher = User::factory()->create(['role_id' => $role->id]);

    $html = $this->actingAs($teacher)->get(route('attendance.selfie'))->assertOk()->getContent();

    expect(preg_match_all('/g-camera__bracket bottom-\[76px\]/', $html))->toBe(2);
});
