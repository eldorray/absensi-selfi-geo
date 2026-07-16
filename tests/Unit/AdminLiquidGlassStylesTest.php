<?php

function adminStyles(): string
{
    return file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');
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
        '.admin-alert-success',
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

test('admin icon and scrollbar helpers use their semantic tokens', function () {
    expect(adminStyles())->toContain(
        'background: color-mix(in srgb, var(--admin-primary) 10%, var(--admin-glass-soft));',
        'background-color: color-mix(in srgb, var(--admin-muted) 42%, transparent);',
    );
});
