<?php

function adminStyles(): string
{
    $styles = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

    if (! is_string($styles)) {
        throw new RuntimeException('Unable to read the admin stylesheet.');
    }

    $styles = preg_replace('/\/\*.*?\*\//s', '', $styles);

    if (! is_string($styles)) {
        throw new RuntimeException('Unable to strip stylesheet comments.');
    }

    return $styles;
}

function adminRule(string $selector): string
{
    $matched = preg_match(
        '/'.preg_quote($selector, '/').'\s*\{([^{}]*)\}/s',
        adminStyles(),
        $matches,
    );

    expect($matched, "Missing admin rule: {$selector}")->toBe(1);

    return $matches[1];
}

test('admin stylesheet defines the complete semantic glass system', function () {
    expect(adminStyles())->toContain(
        '.admin-shell',
        '.admin-glass-panel',
        '.admin-page-header',
        '.admin-field',
        '.admin-button-primary',
        '.admin-button-secondary',
        '.admin-button-success',
        '.admin-button-danger',
        '.admin-status-success',
        '.admin-status-warning',
        '.admin-status-info',
        '.admin-status-danger',
        '.admin-status-neutral',
        '.admin-table',
        '.admin-glass-popover',
        '.admin-glass-modal',
        '.admin-modal-overlay',
        '.admin-alert-success',
        '.admin-alert-danger',
    );
});

test('admin stylesheet includes accessibility and compatibility rules', function () {
    expect(adminStyles())->toContain(
        '@supports not ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px)))',
        '@media (prefers-reduced-motion: reduce)',
        ':focus-visible',
        'min-height: 44px',
    );
});

test('admin rules do not use the old unscoped main overrides', function () {
    expect(adminStyles())->not->toContain(
        'main .bg-white.rounded-2xl',
        'main table th',
        'main input[type="text"]',
    );
});

test('admin semantic overrides retain the required cascade strength', function () {
    expect(adminStyles())->toContain(
        'border: 1px solid var(--admin-border) !important;',
        'background: linear-gradient(145deg, var(--admin-glass-strong), var(--admin-glass)) !important;',
        'box-shadow: var(--admin-shadow) !important;',
        'border-radius: 1.5rem !important;',
        'border-radius: 1rem !important;',
        'border-radius: 1.75rem !important;',
        'color: var(--admin-text) !important;',
        'color: var(--admin-muted) !important;',
        'border: 1px solid var(--admin-border-soft) !important;',
        'border-radius: .875rem !important;',
        'background: var(--admin-glass-soft) !important;',
        'border-color: var(--admin-primary) !important;',
        'box-shadow: var(--admin-focus) !important;',
        'background: color-mix(in srgb, currentColor 12%, transparent) !important;',
        'background: color-mix(in srgb, var(--admin-primary) 6%, transparent) !important;',
    );
});

test('admin canvas uses the exact static liquid glass gradients', function () {
    expect(adminStyles())->toContain(
        'background-color: var(--admin-canvas) !important;',
        'radial-gradient(circle at 8% 4%, rgba(99, 102, 241, 0.18), transparent 34rem),',
        'radial-gradient(circle at 94% 92%, rgba(56, 189, 248, 0.14), transparent 31rem) !important;',
    );
});

test('admin table and success alert use exact semantic values', function () {
    expect(adminStyles())->toContain(
        'font: 700 0.72rem/1.2 "Outfit", sans-serif !important;',
        'letter-spacing: .07em !important;',
        'transition: background-color 180ms ease;',
        'border: 1px solid color-mix(in srgb, var(--admin-success) 30%, transparent);',
        'background: color-mix(in srgb, var(--admin-success) 10%, var(--admin-glass)) !important;',
    );
});

test('admin modal overlay uses the exact scoped dimming and blur values', function () {
    expect(adminRule('.admin-shell .admin-modal-overlay'))->toContain(
        'background: rgba(4, 8, 20, .58) !important;',
        '-webkit-backdrop-filter: blur(10px);',
        'backdrop-filter: blur(10px);',
    );
});

test('admin danger alert uses semantic color with static glass fallbacks', function () {
    expect(adminRule('.admin-shell .admin-alert-danger'))->toContain(
        'border: 1px solid var(--admin-danger-border-soft);',
        'border: 1px solid color-mix(in srgb, var(--admin-danger) 30%, transparent);',
        'background: var(--admin-danger-alert-soft) !important;',
        'background: color-mix(in srgb, var(--admin-danger) 10%, var(--admin-glass)) !important;',
        'color: var(--admin-danger);',
        '-webkit-backdrop-filter: blur(16px);',
        'backdrop-filter: blur(16px);',
    );
});

test('admin table muted cells override the base table cell color', function () {
    expect(adminRule('.admin-shell .admin-table td.admin-muted'))->toContain(
        'color: var(--admin-muted) !important;',
    );
});

test('admin icon and scrollbar helpers use their semantic tokens', function () {
    expect(adminStyles())->toContain(
        'background: color-mix(in srgb, var(--admin-primary) 10%, var(--admin-glass-soft)) !important;',
        'background-color: color-mix(in srgb, var(--admin-muted) 42%, transparent) !important;',
    );
});

test('admin shell tokens and compatibility rules stay in their scoped blocks', function () {
    expect(adminRule('.admin-shell'))->toContain(
        '--admin-canvas: #eef2ff;',
        '--admin-nav-active: #4f46e5;',
        '--admin-primary-soft:',
        '--admin-success-soft:',
        '--admin-warning-soft:',
        '--admin-danger-soft:',
        '--admin-info-soft:',
        '--admin-neutral-soft:',
        '--admin-success-border-soft:',
        '--admin-success-alert-soft:',
        '--admin-scrollbar-thumb:',
    );

    expect(adminRule('.dark .admin-shell'))->toContain(
        '--admin-canvas: #080e1c;',
        '--admin-nav-active: #a5b4fc;',
        '--admin-primary-soft:',
        '--admin-success-soft:',
        '--admin-warning-soft:',
        '--admin-danger-soft:',
        '--admin-info-soft:',
        '--admin-neutral-soft:',
        '--admin-success-border-soft:',
        '--admin-success-alert-soft:',
        '--admin-scrollbar-thumb:',
    );

    expect(adminRule('.admin-shell .admin-nav-active'))->toContain(
        'background: var(--admin-primary-soft) !important;',
        'background: color-mix(in srgb, var(--admin-primary) 12%, transparent) !important;',
        'color: var(--admin-nav-active) !important;',
    );

    expect(adminStyles())
        ->toMatch('/@supports not \(\(-webkit-backdrop-filter: blur\(1px\)\) or \(backdrop-filter: blur\(1px\)\)\)\s*\{.*?\.admin-shell \.admin-header,.*?\.admin-shell \.admin-sidebar,.*?\.admin-shell \.admin-glass-panel,.*?background: var\(--admin-glass-strong\) !important;.*?\}/s')
        ->toMatch('/@media \(prefers-reduced-motion: reduce\)\s*\{.*?\.admin-shell,.*?transition-duration: \.01ms !important;.*?animation-iteration-count: 1 !important;.*?\}/s');
});

test('admin controls use scoped cascade-safe semantic tokens', function () {
    expect(adminRule('.admin-shell .custom-file-input::before'))->toContain(
        'border-color: var(--admin-border-soft) !important;',
        'background: var(--admin-glass-soft) !important;',
        'color: var(--admin-text) !important;',
    );

    expect(adminRule('.admin-shell .admin-icon'))->toContain(
        'border: 1px solid var(--admin-border-soft) !important;',
        'background: var(--admin-primary-soft) !important;',
        'background: color-mix(in srgb, var(--admin-primary) 10%, var(--admin-glass-soft)) !important;',
        'color: var(--admin-primary) !important;',
    );

    expect(adminRule('.admin-shell .admin-checkbox'))->toContain(
        'border-color: var(--admin-border-soft) !important;',
        'background-color: var(--admin-glass-soft) !important;',
        'color: var(--admin-primary) !important;',
        'accent-color: var(--admin-primary) !important;',
    );

    expect(adminRule('.admin-shell .custom-scrollbar::-webkit-scrollbar-thumb'))->toContain(
        'background-color: var(--admin-scrollbar-thumb) !important;',
        'background-color: color-mix(in srgb, var(--admin-muted) 42%, transparent) !important;',
    );
});

test('admin field errors override the neutral border with semantic danger', function () {
    $styles = adminStyles();
    $errorRule = adminRule('.admin-shell .admin-field.border-red-500');

    expect($errorRule)->toContain('border-color: var(--admin-danger) !important;');
    expect(strpos($styles, '.admin-shell .admin-field.border-red-500'))
        ->toBeGreaterThan(strpos($styles, '.admin-shell .admin-field {'))
        ->toBeGreaterThan(strpos($styles, '.admin-shell .admin-field:focus'));
});

test('admin toggles style their visible tracks with semantic tokens', function () {
    expect(adminRule('.admin-shell .admin-toggle + .admin-toggle-track'))->toContain(
        'border: 1px solid var(--admin-border-soft) !important;',
        'background-color: var(--admin-glass-soft) !important;',
    );

    expect(adminRule('.admin-shell .admin-toggle:checked + .admin-toggle-track'))->toContain(
        'border-color: var(--admin-primary) !important;',
        'background-color: var(--admin-primary) !important;',
    );

    expect(adminRule('.admin-shell .admin-toggle:focus-visible + .admin-toggle-track'))->toContain(
        'box-shadow: var(--admin-focus) !important;',
    );
});

test('admin actions use contrast-safe fills and a visible focus indicator', function () {
    expect(adminRule('.admin-shell .admin-button-primary'))->toContain(
        'background: linear-gradient(135deg, #4f46e5, #4338ca) !important;',
        'color: #ffffff !important;',
    );

    expect(adminRule('.admin-shell .admin-button-success'))->toContain(
        'background: #047857 !important;',
        'color: #ffffff !important;',
    );

    expect(adminRule('.admin-shell :is(a, button, input, select, textarea):focus-visible'))->toContain(
        'outline: 2px solid var(--admin-primary) !important;',
        'outline-offset: 2px;',
        'box-shadow: var(--admin-focus) !important;',
    );
});

test('admin color mixes retain static semantic fallbacks', function () {
    expect(adminRule('.admin-shell .admin-status-success'))->toContain(
        'background: var(--admin-success-soft) !important;',
        'background: color-mix(in srgb, currentColor 12%, transparent) !important;',
    );

    expect(adminRule('.admin-shell .admin-status-warning'))->toContain(
        'background: var(--admin-warning-soft) !important;',
        'background: color-mix(in srgb, currentColor 12%, transparent) !important;',
    );

    expect(adminRule('.admin-shell .admin-table tbody tr:hover > *'))->toContain(
        'background: var(--admin-primary-soft) !important;',
        'background: color-mix(in srgb, var(--admin-primary) 6%, transparent) !important;',
    );

    expect(adminRule('.admin-shell .admin-alert-success'))->toContain(
        'border: 1px solid var(--admin-success-border-soft);',
        'border: 1px solid color-mix(in srgb, var(--admin-success) 30%, transparent);',
        'background: var(--admin-success-alert-soft) !important;',
        'background: color-mix(in srgb, var(--admin-success) 10%, var(--admin-glass)) !important;',
    );
});

test('admin main and accessibility selectors remain route scoped', function () {
    expect(adminRule('.admin-shell .admin-main'))->toContain(
        'position: relative;',
        'background-color: var(--admin-canvas) !important;',
    );

    expect(adminStyles())->not->toMatch('/(?:^|\})\s*(?:\.dark\s+)?main(?:\s|[.#:\[>+~])/m');
});
