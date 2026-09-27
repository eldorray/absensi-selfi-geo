<?php

function adminCss(): string
{
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/admin.css');

    if (! is_string($css)) {
        throw new RuntimeException('Unable to read the admin stylesheet.');
    }

    return (string) preg_replace('/\/\*.*?\*\//s', '', $css);
}

/**
 * Token values declared in the first rule whose selector is exactly $selector.
 *
 * @return array<string, string>
 */
function adminTokens(string $selector): array
{
    $matched = preg_match('/(?:^|[{}])\s*'.preg_quote($selector, '/').'\s*\{([^{}]*)\}/s', adminCss(), $m);
    expect($matched, "Missing rule {$selector}")->toBe(1);

    preg_match_all('/--admin-([a-z0-9-]+):\s*(#[0-9A-Fa-f]{6})\s*;/', $m[1], $pairs, PREG_SET_ORDER);

    return collect($pairs)->mapWithKeys(fn (array $p): array => [$p[1] => $p[2]])->all();
}

function adminContrast(string $a, string $b): float
{
    $luminance = function (string $hex): float {
        $channels = array_map(
            fn (string $c): float => ($v = hexdec($c) / 255) <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4,
            str_split(ltrim($hex, '#'), 2),
        );

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    };

    [$hi, $lo] = [max($luminance($a), $luminance($b)), min($luminance($a), $luminance($b))];

    return ($hi + 0.05) / ($lo + 0.05);
}

test('the admin stylesheet is imported and scoped to .admin-shell', function () {
    $app = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

    expect($app)->toContain('@import "./admin.css";')
        ->not->toContain('.admin-shell');

    preg_match_all('/^\s*([^@\s{}][^{}]*)\{/m', adminCss(), $selectors);
    foreach ($selectors[1] as $selector) {
        // Split on top-level commas only, not the ones inside :is(...).
        foreach (preg_split('/,(?![^(]*\))/', $selector) as $part) {
            $part = trim($part);
            if ($part === '' || str_starts_with($part, 'from') || preg_match('/^\d/', $part)) {
                continue;
            }
            expect($part)->toMatch('/\.admin-(shell|linked-account)/');
        }
    }
});

test('the admin panel uses the teacher PWA type pairing', function () {
    expect(adminCss())
        ->toContain('--admin-font: "Plus Jakarta Sans"')
        ->toContain('--admin-display: "Fraunces"')
        ->not->toContain('"Inter"');
});

test('every semantic admin class the views rely on is defined', function (string $class) {
    expect(adminCss())->toContain(".admin-shell .{$class}");
})->with([
    'admin-glass-panel', 'admin-glass-popover', 'admin-glass-modal', 'admin-modal-overlay',
    'admin-page-header', 'admin-kicker', 'admin-label', 'admin-hint', 'admin-muted',
    'admin-field', 'admin-button-primary', 'admin-button-secondary', 'admin-button-success', 'admin-button-danger',
    'admin-status-success', 'admin-status-warning', 'admin-status-info', 'admin-status-danger', 'admin-status-neutral',
    'admin-table', 'admin-alert-success', 'admin-alert-danger', 'admin-row-action', 'admin-chip', 'admin-avatar',
    'admin-empty-state', 'admin-dl', 'admin-toggle', 'admin-checkbox', 'admin-nav-link',
    'admin-sidebar', 'admin-header', 'admin-badge', 'admin-meter', 'admin-segmented', 'admin-pick-item',
]);

test('light and dark tokens keep text readable', function (string $selector) {
    $t = adminTokens($selector);

    $pairs = [
        ['text', 'canvas'], ['text', 'surface'], ['muted', 'surface'], ['muted', 'canvas'], ['muted', 'surface-2'],
        ['on-primary', 'primary'], ['primary', 'primary-soft'], ['warning', 'warning-soft'], ['info', 'info-soft'],
        ['danger', 'danger-soft'], ['on-danger', 'danger-strong'], ['neutral', 'neutral-soft'],
        ['side-ink', 'side'], ['side-muted', 'side'], ['side-active-ink', 'side-active-bg'], ['badge-ink', 'badge-bg'],
    ];

    foreach ($pairs as [$fg, $bg]) {
        $fgHex = $t[$fg] ?? adminTokens('.admin-shell')[$fg];
        $bgHex = $t[$bg] ?? adminTokens('.admin-shell')[$bg];
        expect(adminContrast($fgHex, $bgHex))->toBeGreaterThanOrEqual(4.5, "{$selector}: {$fg} on {$bg}");
    }
})->with(['.admin-shell', '.dark .admin-shell']);

test('field errors win over the neutral and focus borders', function () {
    $css = adminCss();

    expect(strpos($css, '.admin-shell .admin-field.border-red-500'))
        ->toBeGreaterThan(strpos($css, '.admin-shell .admin-field:focus'));
});

test('focus stays visible and motion respects the user setting', function () {
    expect(adminCss())
        ->toMatch('/:is\(a, button, input, select, textarea, summary\):focus-visible\s*\{[^}]*outline: 2px solid var\(--admin-primary\) !important;/s')
        ->toMatch('/@media \(prefers-reduced-motion: reduce\)\s*\{.*?transition-duration: 0\.01ms !important;/s');
});

test('hover styles only apply to precise pointers', function () {
    $css = adminCss();
    $hoverBlock = strpos($css, '@media (hover: hover) and (pointer: fine)');

    expect($hoverBlock)->not->toBeFalse();

    preg_match_all('/:hover/', substr($css, 0, (int) $hoverBlock), $early);
    expect($early[0])->toBeEmpty();
});
