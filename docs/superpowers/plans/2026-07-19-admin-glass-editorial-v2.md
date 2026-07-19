# Admin Glass Editorial v2 — Implementation Plan

**Goal:** execute the v2 redesign in `docs/superpowers/specs/2026-07-19-admin-glass-editorial-v2-design.md`. Presentation-only; all AdminLiquidGlass contracts stay green (edit a contract only when the design intentionally replaces it, in the same commit).

Verify after every task: `php artisan test tests/Feature/AdminLiquidGlassTest.php tests/Unit/AdminLiquidGlassStylesTest.php` and `npm run build`. Full `php artisan test` at the end.

## Task 1 — CSS system v2 + shared components

- `resources/css/app.css`: rework the `admin-*` layer per spec (keep every class name asserted by tests, add `admin-kicker`, `admin-label`, `admin-hint`, `admin-divider`, `admin-stat-card`, `admin-stat-icon`, `admin-tone-*`, `admin-meter`, `admin-chip`, `admin-avatar`, `admin-icon-action`, `admin-empty-state`, `admin-nav-section`, `admin-dl`, pagination polish; keep `@supports`, reduced-motion, focus-visible, 44px rules).
- Create `resources/views/components/admin/page-header.blade.php`, `stat-card.blade.php`, `empty-state.blade.php`.
- Commit: `feat: build admin glass editorial v2 system`

## Task 2 — Chrome

- `sidebar.blade.php`: grouped admin nav (Master Data / Kehadiran / Komunikasi kickers, Laporan parent flattened), employee branch untouched.
- `sidebar-link.blade.php`: refined states; keep Alpine + `aria-current`.
- `header.blade.php`: Bricolage brand + Admin chip + profile chip; popover behavior unchanged.
- `app.blade.php`: session alert restyle only.
- Commit: `feat: regroup admin navigation chrome`

## Task 3 — Dashboard

- Hero panel (greeting, date, presence meter from existing props), tone stat cards, quick-link tiles, recent table with avatars/time chips/empty state.
- Commit: `feat: redesign admin dashboard presence-first`

## Task 4 — Index pages (10)

academic-years, offices, users, roles, work-schedules, attendances, leaves, announcements, reports/daily, reports/monthly: page-header component, toolbar filters, refined tables, empty states, pagination. Keep alert containers, icon-action `size-11 p-0` contracts, compact text-action paddings, modal Alpine strings.
- Commits: `feat: redesign admin master data pages`, `feat: redesign admin attendance and report pages`

## Task 5 — Forms (11) + detail (2)

- Forms: sectioned glass panel, `admin-label`/`admin-hint`, action row; keep `admin-field`, error-border, toggle/checkbox and button contracts.
- Details: two-column evidence + `admin-dl`; keep pinned ternaries/confirm handlers/back-link classes.
- Commit: `feat: redesign admin forms and detail pages`

## Task 6 — Verify

- `npx prettier --write` touched Blade, `php artisan test`, `npm run build`, `php artisan view:clear && php artisan view:cache`, leakage greps from previous plan Task 7.
- Commit fixes if any: `fix: polish admin v2 verification findings`
