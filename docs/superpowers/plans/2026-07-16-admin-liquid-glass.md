# Admin Liquid Glass Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle every interactive admin page with a restrained, accessible liquid-glass system in light and dark modes without changing layout or application behavior.

**Architecture:** Add an `admin-shell` route scope to the existing shared Blade layout, define all glass tokens and semantic components in `resources/css/app.css`, and adopt those semantic classes across admin views. Feature and source-contract tests protect route scoping, complete view coverage, employee isolation, and the presence of accessibility/fallback rules.

**Tech Stack:** Laravel 12, Blade, Pest/PHPUnit, Tailwind CSS 4, Alpine.js, Vite 6.

---

## File Structure

**Create**

- `tests/Feature/AdminLiquidGlassTest.php` — route-scoping and admin-view coverage contracts.
- `tests/Unit/AdminLiquidGlassStylesTest.php` — CSS token, fallback, focus, and reduced-motion contracts.

**Modify: shared system**

- `resources/css/app.css` — replace broad generic admin overrides with admin-scoped tokens and semantic component classes.
- `resources/views/components/layouts/app.blade.php` — conditionally expose `admin-shell`, scope the main canvas, and restyle the shared session alert.
- `resources/views/components/layouts/app/header.blade.php` — add admin-aware glass header and profile popover classes.
- `resources/views/components/layouts/app/sidebar.blade.php` — add admin-aware glass sidebar class.
- `resources/views/components/layouts/sidebar-link.blade.php` — semantic admin navigation states.
- `resources/views/components/layouts/sidebar-two-level-link-parent.blade.php` — semantic report-parent navigation states.
- `resources/views/components/layouts/sidebar-two-level-link.blade.php` — semantic report-child navigation states.

**Modify: dashboard and index/table screens**

- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/academic-years/index.blade.php`
- `resources/views/admin/announcements/index.blade.php`
- `resources/views/admin/attendances/index.blade.php`
- `resources/views/admin/leaves/index.blade.php`
- `resources/views/admin/offices/index.blade.php`
- `resources/views/admin/reports/daily.blade.php`
- `resources/views/admin/reports/monthly.blade.php`
- `resources/views/admin/roles/index.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/work-schedules/index.blade.php`

**Modify: create/edit forms**

- `resources/views/admin/academic-years/create.blade.php`
- `resources/views/admin/academic-years/edit.blade.php`
- `resources/views/admin/announcements/_form.blade.php`
- `resources/views/admin/announcements/create.blade.php`
- `resources/views/admin/announcements/edit.blade.php`
- `resources/views/admin/offices/create.blade.php`
- `resources/views/admin/offices/edit.blade.php`
- `resources/views/admin/roles/create.blade.php`
- `resources/views/admin/roles/edit.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`
- `resources/views/admin/work-schedules/edit.blade.php`

**Modify: detail/approval surfaces**

- `resources/views/admin/attendances/show.blade.php`
- `resources/views/admin/leaves/show.blade.php`

**Explicitly do not modify**

- `resources/views/admin/reports/daily-pdf.blade.php`
- `resources/views/admin/reports/monthly-pdf.blade.php`
- Controllers, models, migrations, routes, and employee/auth/settings views.

### Task 1: Lock the Admin Scope Contract

**Files:**

- Create: `tests/Feature/AdminLiquidGlassTest.php`
- Modify: `resources/views/components/layouts/app.blade.php:110-123`

- [ ] **Step 1: Write the failing route-scope tests**

Create `tests/Feature/AdminLiquidGlassTest.php` with:

```php
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
    $this->actingAs(liquidGlassUser(admin: true))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('admin-shell', escape: false)
        ->assertSee('admin-main', escape: false);
});

test('shared non-admin pages do not expose the admin shell', function () {
    $this->actingAs(liquidGlassUser(admin: false))
        ->get(route('settings.profile.edit'))
        ->assertOk()
        ->assertDontSee('admin-shell', escape: false)
        ->assertDontSee('admin-main', escape: false);
});
```

- [ ] **Step 2: Run the tests and verify the contract fails**

Run:

```bash
php artisan test tests/Feature/AdminLiquidGlassTest.php
```

Expected: the admin test fails because `admin-shell` and `admin-main` are absent; the non-admin assertion passes.

- [ ] **Step 3: Add a route-scoped class without changing layout**

In `resources/views/components/layouts/app.blade.php`, add a local route flag immediately before `<body>` and conditionally add the semantic classes:

```blade
@php($isAdminRoute = request()->routeIs('admin.*'))

<body @class([
    'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased',
    'admin-shell' => $isAdminRoute,
]) x-data="{
```

Change only the `<main>` class expression:

```blade
<main @class([
    'flex-1 overflow-auto bg-gray-100 dark:bg-gray-900 content-transition',
    'admin-main' => $isAdminRoute,
])>
```

- [ ] **Step 4: Run the scope tests**

Run:

```bash
php artisan test tests/Feature/AdminLiquidGlassTest.php
```

Expected: 2 tests pass.

- [ ] **Step 5: Commit the scope contract**

```bash
git add tests/Feature/AdminLiquidGlassTest.php resources/views/components/layouts/app.blade.php
git commit -m "test: scope liquid glass to admin routes"
```

### Task 2: Build the Liquid Glass Token and Component System

**Files:**

- Create: `tests/Unit/AdminLiquidGlassStylesTest.php`
- Modify: `resources/css/app.css:132-343`

- [ ] **Step 1: Write the failing stylesheet contract**

Create `tests/Unit/AdminLiquidGlassStylesTest.php`:

```php
<?php

function adminStyles(): string
{
    return file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');
}

test('admin stylesheet defines the complete semantic glass system', function () {
    expect(adminStyles())
        ->toContain('.admin-shell')
        ->toContain('.admin-glass-panel')
        ->toContain('.admin-page-header')
        ->toContain('.admin-field')
        ->toContain('.admin-button-primary')
        ->toContain('.admin-button-secondary')
        ->toContain('.admin-button-success')
        ->toContain('.admin-button-danger')
        ->toContain('.admin-status-success')
        ->toContain('.admin-status-warning')
        ->toContain('.admin-status-info')
        ->toContain('.admin-status-danger')
        ->toContain('.admin-status-neutral')
        ->toContain('.admin-table')
        ->toContain('.admin-glass-popover')
        ->toContain('.admin-glass-modal')
        ->toContain('.admin-alert-success');
});

test('admin stylesheet includes accessibility and compatibility rules', function () {
    expect(adminStyles())
        ->toContain('@supports not ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px)))')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->toContain(':focus-visible')
        ->toContain('min-height: 44px');
});

test('admin rules do not use the old unscoped main overrides', function () {
    expect(adminStyles())
        ->not->toContain('main .bg-white.rounded-2xl')
        ->not->toContain('main table th')
        ->not->toContain('main input[type="text"]');
});
```

- [ ] **Step 2: Run the stylesheet tests and verify failure**

Run:

```bash
php artisan test tests/Unit/AdminLiquidGlassStylesTest.php
```

Expected: failures for missing semantic classes and remaining unscoped overrides.

- [ ] **Step 3: Replace the broad admin override block with scoped tokens**

Delete the current block from `/* Admin Redesign Styling */` through the old status helpers. Keep the global `[x-cloak]` rule. Insert an `@layer components` block with these exact responsibilities and values:

```css
@layer components {
    .admin-shell {
        --admin-canvas: #eef2ff;
        --admin-glass: rgba(255, 255, 255, 0.72);
        --admin-glass-strong: rgba(255, 255, 255, 0.84);
        --admin-glass-soft: rgba(255, 255, 255, 0.52);
        --admin-border: rgba(255, 255, 255, 0.78);
        --admin-border-soft: rgba(99, 102, 241, 0.13);
        --admin-text: #172033;
        --admin-muted: #596579;
        --admin-primary: #6366f1;
        --admin-primary-hover: #4f46e5;
        --admin-sky: #38bdf8;
        --admin-success: #047857;
        --admin-warning: #a16207;
        --admin-danger: #be123c;
        --admin-info: #0369a1;
        --admin-neutral: #526075;
        --admin-shadow: 0 24px 60px -34px rgba(44, 52, 92, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.86);
        --admin-focus: 0 0 0 3px rgba(99, 102, 241, 0.26);
        color: var(--admin-text);
        background: var(--admin-canvas);
    }

    .dark .admin-shell {
        --admin-canvas: #080e1c;
        --admin-glass: rgba(15, 23, 42, 0.64);
        --admin-glass-strong: rgba(15, 23, 42, 0.78);
        --admin-glass-soft: rgba(15, 23, 42, 0.48);
        --admin-border: rgba(255, 255, 255, 0.12);
        --admin-border-soft: rgba(129, 140, 248, 0.18);
        --admin-text: #e8eefa;
        --admin-muted: #a8b4c7;
        --admin-primary: #818cf8;
        --admin-primary-hover: #a5b4fc;
        --admin-sky: #38bdf8;
        --admin-success: #34d399;
        --admin-warning: #fbbf24;
        --admin-danger: #fb7185;
        --admin-info: #7dd3fc;
        --admin-neutral: #c1cad8;
        --admin-shadow: 0 28px 70px -34px rgba(0, 0, 0, 0.82), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        --admin-focus: 0 0 0 3px rgba(129, 140, 248, 0.34);
    }

    .admin-main {
        position: relative;
        isolation: isolate;
        color: var(--admin-text);
        background:
            radial-gradient(circle at 8% 4%, rgba(99, 102, 241, 0.18), transparent 34rem),
            radial-gradient(circle at 94% 92%, rgba(56, 189, 248, 0.14), transparent 31rem),
            var(--admin-canvas) !important;
    }

    .admin-glass-panel,
    .admin-glass-popover,
    .admin-glass-modal {
        border: 1px solid var(--admin-border) !important;
        background: linear-gradient(145deg, var(--admin-glass-strong), var(--admin-glass)) !important;
        box-shadow: var(--admin-shadow) !important;
        -webkit-backdrop-filter: blur(22px) saturate(135%);
        backdrop-filter: blur(22px) saturate(135%);
    }

    .admin-glass-panel { border-radius: 1.5rem !important; }
    .admin-glass-popover { border-radius: 1rem !important; }
    .admin-glass-modal { border-radius: 1.75rem !important; }

    .admin-page-header h1 {
        color: var(--admin-text) !important;
        font-family: "Bricolage Grotesque", sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .admin-page-header p,
    .admin-muted { color: var(--admin-muted) !important; }

    .admin-field {
        width: 100%;
        min-height: 44px;
        border: 1px solid var(--admin-border-soft) !important;
        border-radius: 0.875rem !important;
        color: var(--admin-text) !important;
        background: var(--admin-glass-soft) !important;
        transition: border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
    }

    .admin-field:focus {
        border-color: var(--admin-primary) !important;
        outline: none;
        box-shadow: var(--admin-focus) !important;
    }

    .admin-button-primary,
    .admin-button-secondary,
    .admin-button-success,
    .admin-button-danger {
        min-height: 44px;
        cursor: pointer;
        border-radius: 0.875rem !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: "Outfit", sans-serif;
        font-weight: 700;
        transition: transform 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
    }

    .admin-button-primary { color: #fff !important; background: linear-gradient(135deg, #6366f1, #4f46e5) !important; }
    .admin-button-secondary { color: var(--admin-text) !important; border: 1px solid var(--admin-border-soft); background: var(--admin-glass-soft) !important; }
    .admin-button-success { color: #fff !important; background: #059669 !important; }
    .admin-button-danger { color: #fff !important; background: #e11d48 !important; }
    .admin-button-primary:hover,
    .admin-button-secondary:hover,
    .admin-button-success:hover,
    .admin-button-danger:hover { transform: translateY(-1px); }

    .admin-status-success,
    .admin-status-warning,
    .admin-status-info,
    .admin-status-danger,
    .admin-status-neutral {
        border: 1px solid currentColor;
        border-radius: 999px;
        font-weight: 700;
    }
    .admin-status-success { color: var(--admin-success) !important; background: color-mix(in srgb, var(--admin-success) 12%, transparent) !important; }
    .admin-status-warning { color: var(--admin-warning) !important; background: color-mix(in srgb, var(--admin-warning) 12%, transparent) !important; }
    .admin-status-info { color: var(--admin-info) !important; background: color-mix(in srgb, var(--admin-info) 12%, transparent) !important; }
    .admin-status-danger { color: var(--admin-danger) !important; background: color-mix(in srgb, var(--admin-danger) 12%, transparent) !important; }
    .admin-status-neutral { color: var(--admin-neutral) !important; background: color-mix(in srgb, var(--admin-neutral) 12%, transparent) !important; }

    .admin-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .admin-table thead { background: var(--admin-glass-soft) !important; }
    .admin-table th { color: var(--admin-muted) !important; font: 700 0.72rem/1.2 "Outfit", sans-serif; letter-spacing: 0.07em; text-transform: uppercase; }
    .admin-table td { color: var(--admin-text); border-top: 1px solid var(--admin-border-soft); }
    .admin-table tbody tr { transition: background-color 180ms ease; }
    .admin-table tbody tr:hover { background: color-mix(in srgb, var(--admin-primary) 6%, transparent) !important; }

    .admin-alert-success { color: var(--admin-success); border: 1px solid color-mix(in srgb, var(--admin-success) 30%, transparent); background: color-mix(in srgb, var(--admin-success) 10%, var(--admin-glass)) !important; -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); }

    .admin-shell :is(a, button, input, select, textarea):focus-visible {
        outline: none;
        box-shadow: var(--admin-focus) !important;
    }
}

@supports not ((-webkit-backdrop-filter: blur(1px)) or (backdrop-filter: blur(1px))) {
    .admin-shell :is(.admin-glass-panel, .admin-glass-popover, .admin-glass-modal) {
        background: var(--admin-glass-strong) !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    .admin-shell *, .admin-shell *::before, .admin-shell *::after {
        scroll-behavior: auto !important;
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
    }
}
```

Add these scoped helpers directly after the component block. Navigation and modal rules are added in Tasks 3 and 6:

```css
.admin-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--admin-primary);
    border: 1px solid var(--admin-border-soft);
    background: color-mix(in srgb, var(--admin-primary) 10%, var(--admin-glass-soft));
}

.admin-shell .custom-file-input::before {
    color: var(--admin-text);
    border: 1px solid var(--admin-border-soft);
    background: var(--admin-glass-soft);
}

.admin-checkbox {
    color: var(--admin-primary) !important;
    border-color: var(--admin-border-soft) !important;
    background: var(--admin-glass-soft) !important;
}

.admin-toggle:checked + span {
    background: var(--admin-primary) !important;
}

.admin-shell :disabled,
.admin-shell [aria-disabled="true"] {
    cursor: not-allowed !important;
    opacity: 0.52;
}

.admin-shell .custom-scrollbar::-webkit-scrollbar-thumb {
    background: color-mix(in srgb, var(--admin-muted) 42%, transparent);
}
```

Do not add another unscoped `main`, `table`, `input`, or button selector.

- [ ] **Step 4: Run stylesheet tests and build assets**

Run:

```bash
php artisan test tests/Unit/AdminLiquidGlassStylesTest.php
npm run build
```

Expected: 3 tests pass; Vite exits 0 and emits compiled assets.

- [ ] **Step 5: Commit the design system**

```bash
git add tests/Unit/AdminLiquidGlassStylesTest.php resources/css/app.css
git commit -m "feat: add admin liquid glass design system"
```

### Task 3: Restyle Shared Admin Chrome

**Files:**

- Modify: `tests/Feature/AdminLiquidGlassTest.php`
- Modify: `resources/views/components/layouts/app.blade.php:156-212`
- Modify: `resources/views/components/layouts/app/header.blade.php:1-68`
- Modify: `resources/views/components/layouts/app/sidebar.blade.php:1-62`
- Modify: `resources/views/components/layouts/sidebar-link.blade.php:1-14`
- Modify: `resources/views/components/layouts/sidebar-two-level-link-parent.blade.php:1-31`
- Modify: `resources/views/components/layouts/sidebar-two-level-link.blade.php:1-13`

- [ ] **Step 1: Extend the failing shared-chrome test**

Add these assertions to the admin route test after `assertSee('admin-main', escape: false)`:

```php
->assertSee('admin-header', escape: false)
->assertSee('admin-sidebar', escape: false)
->assertSee('admin-nav-link', escape: false)
->assertSee('admin-glass-popover', escape: false);
```

Add the matching `assertDontSee` assertions to the non-admin test for `admin-header`, `admin-sidebar`, and `admin-nav-link`.

- [ ] **Step 2: Run the test and verify failure**

Run:

```bash
php artisan test tests/Feature/AdminLiquidGlassTest.php
```

Expected: failures for the new shared-chrome class assertions.

- [ ] **Step 3: Apply semantic classes conditionally**

Use `request()->routeIs('admin.*')` inside the shared partials so non-admin rendering stays unchanged. Apply these exact class additions while retaining every current utility class:

```blade
<header @class([
    'bg-white/85 dark:bg-gray-900/80 backdrop-blur-md z-20 border-b border-slate-100 dark:border-slate-800/80',
    'admin-header' => request()->routeIs('admin.*'),
])>

<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
    @class([
        'bg-white/70 dark:bg-gray-950/40 backdrop-blur-md border-r border-slate-100 dark:border-slate-900/80 sidebar-transition overflow-hidden',
        'admin-sidebar' => request()->routeIs('admin.*'),
    ])>

<a href="{{ $href }}" @class([
    'flex items-center text-xs rounded-xl px-4 py-2.5 justify-center transition-all duration-200 font-semibold',
    'admin-nav-link' => request()->routeIs('admin.*'),
    'admin-nav-active' => request()->routeIs('admin.*') && $active,
    'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold' => $active,
    'hover:bg-slate-500/5 hover:text-slate-800 dark:hover:text-slate-200 text-slate-600 dark:text-slate-400' => ! $active,
]) @if ($active) aria-current="page" @endif>

<div x-show="open" @click.away="open = false" :class="{ 'block': open, 'hidden': !open }"
    @class([
        'hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700',
        'admin-glass-popover' => request()->routeIs('admin.*'),
    ])>
```

For `sidebar-two-level-link-parent.blade.php`, add `admin-nav-link` and conditional `admin-nav-active` to the existing `<button>` class array. For `sidebar-two-level-link.blade.php`, add both classes and conditional `aria-current="page"` to the existing `<a>`. Preserve every Alpine directive, route, label, icon, width, height, and breakpoint class.

In `app.blade.php`, change the existing session status container to include `admin-alert-success` only for admin routes. Do not change the transition or dismiss behavior.

In `app.css`, implement:

```css
.admin-header,
.admin-sidebar {
    border-color: var(--admin-border-soft) !important;
    background: var(--admin-glass) !important;
    -webkit-backdrop-filter: blur(22px) saturate(135%);
    backdrop-filter: blur(22px) saturate(135%);
}
.admin-nav-link { min-height: 44px; color: var(--admin-muted) !important; }
.admin-nav-link:hover { color: var(--admin-text) !important; background: var(--admin-glass-soft) !important; }
.admin-nav-link[aria-current="page"],
.admin-nav-link.admin-nav-active { color: var(--admin-primary) !important; background: color-mix(in srgb, var(--admin-primary) 12%, transparent) !important; }
```

Add `aria-current="page"` on active simple and nested sidebar links; retain the current `$active` evaluation.

- [ ] **Step 4: Run focused tests and build**

Run:

```bash
php artisan test tests/Feature/AdminLiquidGlassTest.php tests/Unit/AdminLiquidGlassStylesTest.php
npm run build
```

Expected: 5 tests pass and Vite exits 0.

- [ ] **Step 5: Commit shared chrome**

```bash
git add resources/css/app.css resources/views/components/layouts tests/Feature/AdminLiquidGlassTest.php
git commit -m "feat: restyle admin navigation chrome"
```

### Task 4: Migrate Dashboard and Index/Table Pages

**Files:**

- Modify: `tests/Feature/AdminLiquidGlassTest.php`
- Modify: the 11 dashboard/index/report files listed in “dashboard and index/table screens.”

- [ ] **Step 1: Write the failing source-coverage test**

Append to `tests/Feature/AdminLiquidGlassTest.php`:

```php
dataset('admin index views', [
    'dashboard' => ['admin/dashboard.blade.php'],
    'academic years' => ['admin/academic-years/index.blade.php'],
    'announcements' => ['admin/announcements/index.blade.php'],
    'attendances' => ['admin/attendances/index.blade.php'],
    'leaves' => ['admin/leaves/index.blade.php'],
    'offices' => ['admin/offices/index.blade.php'],
    'daily report' => ['admin/reports/daily.blade.php'],
    'monthly report' => ['admin/reports/monthly.blade.php'],
    'roles' => ['admin/roles/index.blade.php'],
    'users' => ['admin/users/index.blade.php'],
    'work schedules' => ['admin/work-schedules/index.blade.php'],
]);

test('admin index views adopt semantic glass surfaces', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)
        ->toContain('admin-page-header')
        ->toContain('admin-glass-panel');

    if (str_contains($source, '<table')) {
        expect($source)->toContain('admin-table');
    }
})->with('admin index views');
```

- [ ] **Step 2: Run and verify the dataset fails**

Run:

```bash
php artisan test tests/Feature/AdminLiquidGlassTest.php --filter="admin index views"
```

Expected: all 11 dataset cases fail on missing semantic classes.

- [ ] **Step 3: Apply the deterministic class mapping to all 11 files**

Make only class-attribute changes. Use this mapping consistently:

| Existing purpose | Add semantic class |
|---|---|
| Top title/description/action wrapper | `admin-page-header` |
| White/dark card or filter wrapper | `admin-glass-panel` |
| Table element | `admin-table` |
| Text/date/time/number/select/textarea filter | `admin-field` |
| Indigo main action/filter button | `admin-button-primary` |
| Gray cancel/reset/back action | `admin-button-secondary` |
| Green approve/active action or badge | `admin-button-success` / `admin-status-success` |
| Amber pending/late badge | `admin-status-warning` |
| Blue informational badge | `admin-status-info` |
| Red delete/reject/PDF action or badge | `admin-button-danger` / `admin-status-danger` |
| Gray inactive/empty badge | `admin-status-neutral` |
| Muted descriptions/cell metadata | `admin-muted` |

Preserve spacing, grid, responsive, width, overflow, alignment, and visibility utilities. Remove obsolete surface utilities only when the semantic class replaces them: `bg-white`, `dark:bg-gray-800`, `shadow-lg`, `shadow-sm`, and surface border colors. Do not change text, conditions, route calls, loops, forms, or Alpine directives.

For dashboard metric and quick-link cards, add `admin-glass-panel`; retain the existing grid and hover scale. For nested work-schedule tables, add `admin-table` to both the outer and expanded-row table.

- [ ] **Step 4: Run coverage, existing report tests, and build**

Run:

```bash
php artisan test tests/Feature/AdminLiquidGlassTest.php tests/Feature/ReportFineTest.php
npm run build
```

Expected: all tests pass and Vite exits 0.

- [ ] **Step 5: Commit index-page migration**

```bash
git add tests/Feature/AdminLiquidGlassTest.php resources/views/admin/dashboard.blade.php resources/views/admin/academic-years/index.blade.php resources/views/admin/announcements/index.blade.php resources/views/admin/attendances/index.blade.php resources/views/admin/leaves/index.blade.php resources/views/admin/offices/index.blade.php resources/views/admin/reports/daily.blade.php resources/views/admin/reports/monthly.blade.php resources/views/admin/roles/index.blade.php resources/views/admin/users/index.blade.php resources/views/admin/work-schedules/index.blade.php
git commit -m "feat: apply glass surfaces to admin data pages"
```

### Task 5: Migrate Create and Edit Forms

**Files:**

- Modify: `tests/Feature/AdminLiquidGlassTest.php`
- Modify: the 12 form files listed in “create/edit forms.”

- [ ] **Step 1: Add the failing form coverage test**

Append:

```php
dataset('admin form views', [
    ['admin/academic-years/create.blade.php'],
    ['admin/academic-years/edit.blade.php'],
    ['admin/announcements/_form.blade.php'],
    ['admin/announcements/create.blade.php'],
    ['admin/announcements/edit.blade.php'],
    ['admin/offices/create.blade.php'],
    ['admin/offices/edit.blade.php'],
    ['admin/roles/create.blade.php'],
    ['admin/roles/edit.blade.php'],
    ['admin/users/create.blade.php'],
    ['admin/users/edit.blade.php'],
    ['admin/work-schedules/edit.blade.php'],
]);

test('admin form views adopt semantic glass controls', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)->toContain('admin-field');

    if (! str_ends_with($view, '_form.blade.php')) {
        expect($source)
            ->toContain('admin-page-header')
            ->toContain('admin-glass-panel');
    }
})->with('admin form views');
```

- [ ] **Step 2: Run and verify failure**

Run:

```bash
php artisan test tests/Feature/AdminLiquidGlassTest.php --filter="admin form views"
```

Expected: all 12 cases fail before migration.

- [ ] **Step 3: Apply semantic form classes**

For every input, select, and textarea, append `admin-field` while retaining layout sizing and validation-related classes. Apply `admin-glass-panel` to the existing form card and `admin-page-header` to the existing page heading wrapper. Map submit actions to `admin-button-primary`, cancel/back actions to `admin-button-secondary`, and destructive controls to `admin-button-danger`.

For checkboxes and work-schedule toggles, add `admin-checkbox` or `admin-toggle` and style both in `app.css` under `.admin-shell`. Preserve their checked bindings, names, values, and peer selectors. For file upload controls in announcement forms, scope the existing custom file-input appearance under `.admin-shell` and reuse `admin-field` for its container.

Do not move fields, change column grids, alter `old(...)` expressions, change `@error` blocks, or change submit routes/methods.

- [ ] **Step 4: Format Blade, run tests, and build**

Run:

```bash
npx prettier --write "resources/views/admin/{academic-years,announcements,offices,roles,users,work-schedules}/**/*.blade.php"
php artisan test tests/Feature/AdminLiquidGlassTest.php tests/Feature/AnnouncementTest.php
npm run build
```

Expected: Prettier exits 0, all focused tests pass, and Vite exits 0.

- [ ] **Step 5: Commit form migration**

```bash
git add resources/css/app.css tests/Feature/AdminLiquidGlassTest.php resources/views/admin/academic-years resources/views/admin/announcements resources/views/admin/offices resources/views/admin/roles resources/views/admin/users resources/views/admin/work-schedules/edit.blade.php
git commit -m "feat: restyle admin forms with liquid glass"
```

### Task 6: Migrate Detail, Approval, Modal, Alert, and Feedback States

**Files:**

- Modify: `tests/Feature/AdminLiquidGlassTest.php`
- Modify: `resources/views/admin/attendances/show.blade.php`
- Modify: `resources/views/admin/leaves/show.blade.php`
- Modify: `resources/views/admin/reports/daily.blade.php`
- Modify: `resources/views/components/layouts/app.blade.php`

- [ ] **Step 1: Add failing detail and PDF-isolation tests**

Append:

```php
dataset('admin detail views', [
    ['admin/attendances/show.blade.php'],
    ['admin/leaves/show.blade.php'],
]);

test('admin detail views adopt semantic glass surfaces', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect($source)
        ->toContain('admin-page-header')
        ->toContain('admin-glass-panel');
})->with('admin detail views');

test('daily report uses the semantic glass photo modal', function () {
    expect(file_get_contents(resource_path('views/admin/reports/daily.blade.php')))
        ->toContain('admin-modal-overlay')
        ->toContain('admin-glass-modal');
});

test('print templates remain outside the admin glass system', function () {
    foreach (['daily-pdf.blade.php', 'monthly-pdf.blade.php'] as $view) {
        expect(file_get_contents(resource_path("views/admin/reports/{$view}")))
            ->not->toContain('admin-glass')
            ->not->toContain('admin-field')
            ->not->toContain('admin-table');
    }
});
```

- [ ] **Step 2: Run and verify the new tests fail only for interactive views**

Run:

```bash
php artisan test tests/Feature/AdminLiquidGlassTest.php --filter="detail views|photo modal|print templates"
```

Expected: detail and modal tests fail; PDF isolation passes.

- [ ] **Step 3: Apply semantic detail and feedback classes**

Add `admin-page-header` to existing detail headings and `admin-glass-panel` to every existing detail/approval card without changing grid placement. Map approval/rejection forms to success/danger button classes and all status badges to the five semantic badge classes.

In `daily.blade.php`, retain the entire Alpine `x-data`, event, `x-show`, Escape, click-away, image URL, and title behavior. Add `admin-modal-overlay` to the fixed backdrop and `admin-glass-modal` to the existing modal panel.

Add to `app.css`:

```css
.admin-modal-overlay {
    background: rgba(4, 8, 20, 0.58) !important;
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
}
.admin-shell :disabled,
.admin-shell [aria-disabled="true"] {
    cursor: not-allowed !important;
    opacity: 0.52;
}
```

Keep the session alert dismissal and form validation rendering unchanged.

- [ ] **Step 4: Format and run focused regression checks**

Run:

```bash
npx prettier --write resources/views/admin/attendances/show.blade.php resources/views/admin/leaves/show.blade.php resources/views/admin/reports/daily.blade.php resources/views/components/layouts/app.blade.php
php artisan test tests/Feature/AdminLiquidGlassTest.php tests/Feature/ReportFineTest.php
npm run build
```

Expected: focused tests pass and Vite exits 0.

- [ ] **Step 5: Commit detail and modal migration**

```bash
git add resources/css/app.css tests/Feature/AdminLiquidGlassTest.php resources/views/admin/attendances/show.blade.php resources/views/admin/leaves/show.blade.php resources/views/admin/reports/daily.blade.php resources/views/components/layouts/app.blade.php
git commit -m "feat: finish admin glass states and modals"
```

### Task 7: Verify Coverage, Behavior, Accessibility, and Responsive Rendering

**Files:**

- Modify only files that fail verification; do not expand scope.

- [ ] **Step 1: Run the complete automated suite**

Run:

```bash
php artisan test
npm run build
```

Expected: all PHP tests pass and the Vite build exits 0.

- [ ] **Step 2: Verify routes and Blade compilation**

Run:

```bash
php artisan route:list --name=admin
php artisan view:clear
php artisan view:cache
```

Expected: the existing admin routes are listed; view cache completes without Blade errors.

- [ ] **Step 3: Verify complete view coverage and exclusions**

Run:

```bash
rg -L "admin-(page-header|glass-panel|field|table)" resources/views/admin -g '*.blade.php' -g '!reports/*-pdf.blade.php'
rg -n "admin-(glass|field|table)" resources/views/admin/reports/*-pdf.blade.php
rg -n "main \.bg-white|main table|main input" resources/css/app.css
```

Expected: the first command returns no interactive admin view except a partial whose semantic class is supplied by its parent; the second and third commands return no matches.

- [ ] **Step 4: Start the app for visual verification**

Run in separate terminals:

```bash
php artisan serve
npm run dev
```

Expected: Laravel is available at `http://127.0.0.1:8000` and Vite connects without console errors.

- [ ] **Step 5: Check representative screens in both appearances**

At 375px, 768px, 1024px, and 1440px, inspect:

- Admin dashboard: metric cards, quick links, recent table, sidebar open/collapsed.
- Users index: filters, table horizontal overflow, status/actions, pagination.
- User create/edit: fields, validation, select, buttons, keyboard focus.
- Leave detail: metadata cards, status badge, approve/reject actions.
- Daily report: filter, statistic cards, table, photo modal, Escape/click-away close.
- Header profile dropdown: focus order, click-away close, readable glass surface.

For every screen, toggle light/dark through the existing appearance setting. Confirm there is no layout reordering, no clipped dropdown/modal, no horizontal page overflow outside intended table scrollers, body text remains readable, and focus is visible.

- [ ] **Step 6: Check employee and print isolation**

Open one employee page using the shared layout, one settings page, and both PDF export routes. Confirm none receive admin glass surfaces and both PDFs retain their print styling.

- [ ] **Step 7: Run final diff and regression checks**

Run:

```bash
git diff --check
git diff --stat
php artisan test
npm run build
```

Expected: no whitespace errors, changes remain limited to the declared UI/test files, all tests pass, and Vite exits 0.

- [ ] **Step 8: Commit verification fixes if any exist**

If Step 5 or Step 6 required scoped visual fixes, stage only those files and commit:

```bash
git add resources/css/app.css resources/views/components/layouts resources/views/admin tests/Feature/AdminLiquidGlassTest.php tests/Unit/AdminLiquidGlassStylesTest.php
git commit -m "fix: polish admin glass responsiveness"
```

If verification required no changes, do not create an empty commit.
