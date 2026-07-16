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
