# Admin Panel Redesign v2 — "Glass Editorial, Presence-first"

## Summary

Full redesign of every admin menu page. It evolves the existing liquid-glass system (kept: tokens, light/dark, accessibility contracts) but this time restructures the pages themselves: grouped sidebar navigation, a presence-first dashboard hero, one unified page-header pattern, toolbar-style filters, richer tables, real empty states, sectioned forms, and definition-list detail pages.

Employee pages, auth, settings, and PDF templates stay untouched.

## Approaches considered

1. **Token polish only** — too shallow for "redesign semua menu".
2. **Brand-new aesthetic (warm editorial / brutalist)** — discards the deliberate glass system and its dark-mode + a11y work; breaks every source contract test for zero functional gain.
3. **Glass Editorial v2 (chosen)** — keep the glass material and semantic class contracts, redesign structure and typography on top. Maximum visible change, minimum regression risk.

## Design language

- **Subject truth:** this is an attendance panel. Time, presence, and photo evidence are the content. The signature element is a **presence meter** on the dashboard hero (hadir/terlambat/belum absen segments computed from existing props) plus **tabular-numeric time chips** used consistently wherever a clock-in/out time appears.
- **Type roles:** Bricolage Grotesque = display (page titles, stat values), Outfit = utility (kickers, labels, table headers, buttons), Inter = body/data. No new font dependency.
- **Color:** existing token palette (indigo primary, sky support, emerald/amber/rose/sky semantics) in light `#EEF2FF` / dark `#080E1C` canvases. Tone tiles for stat/quick-link icons: indigo, sky, emerald, amber, rose, violet.
- **Structure devices:** every page opens with a kicker (section name, e.g. "Master Data") above the h1 — the kicker mirrors the sidebar group, so navigation and page header encode the same taxonomy.

## Navigation redesign (sidebar)

Admin nav becomes grouped sections with kicker labels (labels hidden when the rail is collapsed):

- Dashboard (ungrouped, top)
- **Master Data:** Tahun Ajaran, Kantor, User, Role, Jam Kerja
- **Kehadiran:** Rekap Harian, Rekap Bulanan, Detail Absensi, Perizinan
- **Komunikasi:** Informasi

The "Laporan" collapsible parent is flattened into the Kehadiran group — one click fewer, no hidden items. Employee menu branch unchanged. Alpine collapse/expand behavior of the rail unchanged.

Header: hamburger + app name (Bricolage) + "Admin" chip on admin routes; profile button becomes a glass chip; popover unchanged behaviorally.

## Page patterns

- **Page header:** kicker · h1 · description · optional count chip · actions right. Shared Blade component `<x-admin.page-header>`.
- **Filters:** one glass toolbar panel; `admin-label` labels, `admin-field` controls, primary submit.
- **Tables:** `admin-table` refined — Outfit uppercase headers, avatar cells (`admin-avatar`), status pills, time chips, right-aligned icon actions (`admin-icon-action` styling layered on the contracted `admin-button-* size-11 p-0` classes), styled pagination, `<x-admin.empty-state>` for empty rows.
- **Forms:** page header + back action; single glass panel; grouped fieldsets separated by `admin-divider`; labels above fields, hints/errors below; action row right-aligned (primary + secondary). All `admin-field` / error-border / button contracts preserved.
- **Detail pages:** two-column grid — evidence (photos/map) beside an `admin-dl` definition list; approval actions in their own panel. All pinned status ternaries and confirm() handlers preserved verbatim.
- **Dashboard:** hero panel (kicker, greeting, localized date, presence meter) → 4 tone stat cards → 3 quick-link tiles with chevrons → recent attendance table.
- **Reports:** toolbar filter, stat chip row, table with photo-thumb buttons; photo modal keeps its exact Alpine contract.

## New/changed assets

- `resources/css/app.css` — v2 of the `admin-*` component layer: existing class names kept (tests), new classes added: `admin-kicker`, `admin-label`, `admin-hint`, `admin-divider`, `admin-stat-card`, `admin-stat-icon`, `admin-tone-*`, `admin-meter`, `admin-chip`, `admin-avatar`, `admin-icon-action`, `admin-empty-state`, `admin-nav-section`, `admin-dl`, pagination polish. All `@supports`/reduced-motion/focus/44px rules retained.
- `resources/views/components/admin/{page-header,stat-card,empty-state}.blade.php` — new anonymous components.
- All interactive Blade views under `resources/views/admin/**` rewritten to the new patterns (legacy `bg-white dark:bg-gray-800`-era utility clutter and dead classes like `font-display`, `dark:border-slate-855`, `theme-status-*` removed).
- Shared chrome: `sidebar.blade.php` (groups), `sidebar-link.blade.php`, `header.blade.php`, `app.blade.php` (status alert only).

## Non-goals / unchanged

- No controller, model, route, migration, or behavior change; same Inertia-free Blade + Alpine flow.
- PDF templates untouched.
- Existing test contracts stay green; a contract is edited only where the new design intentionally replaces it, in the same commit.

## Verification

`php artisan test`, `npm run build`, prettier on touched Blade, `view:cache` compile check, no `admin-*` leakage into employee/PDF views.
