<?php

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;

// Item 11: high-refresh (120 Hz) and iPhone behaviour for the teacher PWA.

function mobileCss(): string
{
    return file_get_contents(resource_path('css/guru.css'));
}

/** Whether $offset sits inside a still-open block that starts with $opener. */
function mobileInsideBlock(string $css, int $offset, string $opener): bool
{
    $start = strrpos(substr($css, 0, $offset), $opener);
    if ($start === false) {
        return false;
    }
    $between = substr($css, $start, $offset - $start);

    return substr_count($between, '{') > substr_count($between, '}');
}

function mobileTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('the shell tells the browser about the keyboard and both colour schemes', function () {
    $this->actingAs(mobileTeacher())->get(route('attendance.index'))
        ->assertSee('content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content"', false)
        ->assertSee('<meta name="color-scheme" content="light dark">', false);
});

test('every hover rule is gated to devices that can hover', function () {
    $css = mobileCss();
    preg_match_all('/:hover/', $css, $matches, PREG_OFFSET_CAPTURE);

    expect($matches[0])->not->toBeEmpty();
    foreach ($matches[0] as [, $offset]) {
        expect(mobileInsideBlock($css, $offset, '@media (hover: hover) and (pointer: fine)'))->toBeTrue();
    }
});

test('no transition animates a layout property', function () {
    preg_match_all('/transition:[^;]*;/', mobileCss(), $transitions);

    foreach ($transitions[0] as $transition) {
        expect($transition)->not->toMatch('/\b(width|height|left|right|top|bottom|margin|padding)\b/');
    }
});

test('touch baseline: no tap delay, no text inflation, controls not selectable', function () {
    $css = mobileCss();

    expect($css)
        ->toMatch('/-webkit-text-size-adjust:\s*100%/')
        ->toMatch('/\.guru a,\s*\.guru button,\s*\.guru label[^{]*\{[^}]*touch-action:\s*manipulation/')
        ->toMatch('/\.guru \.g-btn[^{]*\{[^}]*user-select:\s*none[^}]*-webkit-touch-callout:\s*none/');
});

test('touch press feedback exists for rows, cards, nav and choices', function () {
    expect(mobileCss())
        ->toMatch('/\.guru a\.g-list__item:active[^{]*\{/')
        ->toMatch('/\.guru \.g-nav__item:active \.g-nav__pill[^{]*\{[^}]*transform:\s*scale/')
        ->toMatch('/\.guru \.g-choice:active[^{]*\{/');
});

// The progress bar is server-rendered only, so it needs no transition at all;
// the layout-property test above keeps width/left animations out.
test('carousel scroll stays inside it and the scan line runs on the compositor', function () {
    expect(mobileCss())
        ->toMatch('/\.g-carousel\s*\{[^}]*overscroll-behavior-x:\s*contain/')
        ->toMatch('/\.g-camera__scan\s*\{[^}]*will-change:\s*transform/');
});

test('announcement images decode off the main thread', function () {
    Announcement::create(['title' => 'Rapat', 'body' => 'isi', 'is_active' => true, 'image_path' => 'announcements/x.jpg']);

    $this->actingAs(mobileTeacher())->get(route('attendance.dashboard'))
        ->assertSee('loading="lazy" decoding="async"', false);
});

// iOS 26+ lays a Liquid Glass blur over the top of an installed web app unless a solid
// fixed box covers the top edge (taller than 10px, at least 90% wide); then it uses that colour.
test('a solid fixed strip covers the status bar so iOS does not blur the header', function () {
    $this->actingAs(mobileTeacher())->get(route('attendance.index'))
        ->assertSee('<div class="g-statusbar" aria-hidden="true"></div>', false);

    expect(mobileCss())->toMatch('/\.g-statusbar\s*\{[^}]*position:\s*fixed;[^}]*top:\s*0;[^}]*inset-inline:\s*0;[^}]*height:\s*max\(12px,\s*env\(safe-area-inset-top\)\);[^}]*background:\s*var\(--g-ground\)/');
});

test('the header keeps a gap below the status bar', function () {
    expect(mobileCss())->toMatch('/\.g-header\s*\{[^}]*padding:\s*calc\(max\(4px,\s*env\(safe-area-inset-top\)\)\s*\+\s*12px\)/');
});

// iOS 26+ reports the installed app's viewport short by the top inset, so the fixed nav stops
// above the screen edge; the strip below it shows the canvas, so paint the canvas in the nav colour.
test('in standalone mode the canvas under a nav matches the nav', function () {
    expect(mobileCss())->toMatch('/@media \(display-mode: standalone\)\s*\{\s*\.guru:has\(\.g-nav\)\s*\{\s*background:\s*var\(--g-surface\);/');
});
