# Redesign PWA Guru — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengganti tampilan seluruh halaman PWA guru dengan bahasa visual file desain "AbsenKU Guru — Alternatif PWA – Beranda" (krem hangat + hijau tua, Fraunces + Plus Jakarta Sans, tema terang & gelap) tanpa mengubah fungsi.

**Architecture:** Satu stylesheet ter-scope `resources/css/guru.css` (token + komponen di `@layer components`, selector di bawah `.guru`) dan komponen Blade anonim `x-guru.*`. Layout `x-layouts.mobile` ditulis ulang; setiap view guru ditulis ulang di atas komponen itu. Logika keadaan kartu hero dipindah ke presenter `App\Services\TodayPresence`; angka rekap baru ditambahkan ke `EmployeeDashboardService`. Skin lama `partials/pwa-material3` dan layout duplikat dashboard dihapus.

**Tech Stack:** Laravel 12 (PHP 8.2+), Blade, Alpine.js 3.14, Tailwind CSS v4 (Vite), Pest 4. Tanpa dependensi baru.

**Spec:** `docs/superpowers/specs/2026-09-27-redesign-pwa-guru-design.md`

## Global Constraints

- Branch kerja: `feat/redesign-guru-pwa`. Commit per task, dengan baris `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>` di akhir pesan commit.
- Tanpa dependensi npm/composer baru. Font di-self-host dari bundel desain (subset latin).
- Semua gaya guru di-scope ke `.guru` (kelas di `<body>` layout guru). Admin, auth, dan welcome tidak boleh berubah.
- File PHP baru: `declare(strict_types=1);`, tipe lengkap. Jalankan `./vendor/bin/pint --dirty`. PHPStan: tidak boleh ada error baru (baseline 132: `./vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw | grep -c .` harus tetap 132).
- Copy Bahasa Indonesia. Format jam `H.i` (contoh `06.52`); jam kosong ditampilkan `––.––`.
- Aksesibilitas:
  - teks minimal 12px, input 16px, target sentuh minimal 44px;
  - tanpa `user-scalable=no` / `maximum-scale`;
  - `prefers-reduced-motion` mematikan semua transisi/animasi guru;
  - ikon SVG `aria-hidden`; tombol ikon punya `aria-label`.
- Logika JavaScript di `attendance/selfie.blade.php` dan `attendance/checkout.blade.php` (`function attendanceForm()` / `function checkoutForm()`) **tidak diubah sedikit pun**.
- Nama route, nama field form, dan otorisasi tidak diubah.
- Warna mengikuti tabel token §2.2 spec persis.
- `php artisan test` harus hijau di akhir setiap task.

## Review Focus

1. **Bulan tanpa hari kerja berjadwal** (`monthlyWorkDays = 0`, misalnya awal tahun ajaran belum ada jadwal): kartu rekap tetap tampil tanpa pembagian nol dan menulis "Belum ada hari kerja bulan ini". Test di Task 5.
2. **Izin yang melewati batas bulan atau tumpang-tindih** (mulai bulan lalu, berakhir besok, atau dua izin di tanggal sama): hanya tanggal unik di bulan ini sampai hari ini yang dihitung. Test di Task 4.
3. **Tidak ada tahun ajaran aktif atau tidak ada jadwal hari ini**: chip "Libur", tidak ada tombol absen, halaman tetap 200. Test di Task 4 (presenter) dan Task 5 (halaman).
4. **Jendela absen masuk sudah ditutup tanpa absen**: chip "Absen ditutup", tombol Absen Masuk tidak ditampilkan. Test di Task 4.
5. **Preferensi tema tersimpan dengan key lama `welcome-theme`, atau tidak ada sama sekali**: tema diterapkan sebelum paint, dan "Ikut sistem" mengikuti OS. Test di Task 3.

---

## File Structure

| Berkas | Tanggung jawab | Task |
|---|---|---|
| `resources/fonts/fraunces-latin.woff2`, `resources/fonts/plus-jakarta-sans-latin.woff2` | Font variable dari bundel desain | 1 |
| `resources/css/guru.css` (baru), `resources/css/app.css` (import) | Token, dasar, semua kelas komponen `g-*` | 1 |
| `resources/views/components/guru/{icon,card,chip,section,list,list-item,stat,button,field,empty,notice}.blade.php` | Komponen Blade anonim | 2 |
| `resources/views/components/layouts/mobile.blade.php` | Shell guru: head, tema, header default, nav bawah | 3 |
| `app/Services/EmployeeDashboardData.php`, `app/Services/EmployeeDashboardService.php` | Angka rekap baru | 4 |
| `app/Services/TodayPresence.php` (baru) | Presenter keadaan kartu hero | 4 |
| `app/Http/Controllers/Employee/DashboardController.php` | Meneruskan `presence` | 5 |
| `resources/views/attendance/dashboard.blade.php` | Beranda | 5 |
| `resources/views/partials/pwa-material3.blade.php` | **Dihapus** | 5 |
| `resources/views/attendance/partials/absen-form.blade.php` (baru), `selfie.blade.php`, `checkout.blade.php` | Absen masuk/pulang | 6 |
| `resources/views/attendance/index.blade.php` | Riwayat | 7 |
| `resources/views/attendance/leaves/{index,create,show,approval-index,approval-show}.blade.php` | Izin dan persetujuan | 8 |
| `resources/views/attendance/{profile,password}.blade.php`, `attendance/information/show.blade.php` | Profil (+tema, keluar), password, informasi | 9 |
| `resources/views/attendance/my-class/{index,show,violation}.blade.php` | Kelas wali | 10 |
| `resources/views/attendance/bk/{index,form,show}.blade.php`, `bk/partials/*`; `bk/create.blade.php` **dihapus** | BK | 11 |
| `resources/views/attendance/kesiswaan/{index,show,my-referrals,referral-queue}.blade.php` | Kesiswaan dan rujukan | 12 |
| `public/offline.html`, `storage/app/visual/*` (tidak di-commit), `docs/screenshots/redesign-guru/*` | Halaman offline, alat screenshot, bukti visual | 13 |

Test baru dan yang diubah dicantumkan di masing-masing task.

---

### Task 1: Font dan stylesheet `guru.css`

**Files:**
- Create: `resources/fonts/fraunces-latin.woff2`, `resources/fonts/plus-jakarta-sans-latin.woff2`
- Create: `resources/css/guru.css`
- Modify: `resources/css/app.css` (baris 1–2)
- Test: `tests/Feature/Guru/GuruStylesTest.php`

**Interfaces:**
- Produces: kelas CSS `.guru` (scope) dan komponen `g-*` yang dipakai semua task berikutnya:
  - **Shell:** `g-app`, `g-header`, `g-header__title`, `g-header__actions`, `g-iconbtn`, `g-iconbtn__dot`, `g-main`, `g-main--bare`, `g-nav`, `g-nav__item`, `g-nav__pill`.
  - **Tipografi dan kartu:** `g-display`, `g-num`, `g-h2`, `g-caps`, `g-muted`, `g-card`, `g-card--hero`, `g-card--flush`, `g-card__caps`, `g-card__title`.
  - **Kartu hero:** `g-hero__label`, `g-hero__times`, `g-hero__time`, `g-hero__time-label`, `g-hero__time-value`, `g-hero__loc`, `g-progress`, `g-progress__fill`, `g-progress__knob`, `g-progress__labels`.
  - **Rekap:** `g-stats`, `g-stat`, `g-stat__value`, `g-stat__label`, `g-stackbar`.
  - **Chip:** `g-chip` dengan `--neutral`, `--ok`, `--late`, `--pending`, `--izin`, `--attn`, `--hero`, `--hero-late`.
  - **Daftar:** `g-list`, `g-list__item`, `g-list__icon` (`--primary`, `--attn`, `--izin`, `--neutral`, `--late`), `g-list__body`, `g-list__title`, `g-list__desc`, `g-list__chev`, `g-section`.
  - **Tombol:** `g-btn` dengan `--primary`, `--hero`, `--pulang`, `--secondary`, `--danger`, `--block`, `--sm`.
  - **Form:** `g-field`, `g-label`, `g-req`, `g-input`, `g-hint`, `g-error`, `g-choices`, `g-choice`, `g-choice__check`, `g-drop`, `g-drop__preview`, `g-seg`, `g-search`.
  - **Umpan balik:** `g-notice` (`--warn`, `--error`, `--ok`), `g-notice__icon`, `g-empty`, `g-empty__icon`.
  - **Lain-lain:** `g-dl`, `g-avatar` (`--sm`, `--xl`), `g-greet`, `g-dateline`, `g-carousel`, `g-info`, `g-info__media`, `g-info__tag`, `g-info__body`, `g-menu`, `g-menu__item`, `g-dialog`, `g-install`, `g-pager`, `g-history__date`, `g-history__times`.
  - **Kamera:** `g-camera`, `g-camera__overlay`, `g-camera__prompt`, `g-camera__guide`, `g-camera__scan`, `g-camera__bracket`, `g-camera__flash`, `g-camera__badge`, `g-status`.
  - **Utility Tailwind:** `bg-guru-*`, `text-guru-*`, `border-guru-*`, `divide-guru-*` untuk setiap token (lihat blok `@theme inline`).

- [ ] **Step 1: Tulis test yang gagal**

```php
<?php

// tests/Feature/Guru/GuruStylesTest.php

test('the teacher stylesheet is imported and scoped to .guru', function () {
    $app = file_get_contents(resource_path('css/app.css'));
    $guru = file_get_contents(resource_path('css/guru.css'));

    expect($app)->toContain('@import "./guru.css";')
        ->and($guru)
        ->toContain('.guru {')
        ->toContain('.dark .guru {')
        ->toContain('font-family: "Fraunces";')
        ->toContain('font-family: "Plus Jakarta Sans";')
        ->toContain('--g-primary: #1E5A48;')
        ->toContain('--g-ground: #F4F1EA;')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('the teacher fonts are self-hosted', function () {
    expect(filesize(resource_path('fonts/fraunces-latin.woff2')))->toBeGreaterThan(10_000)
        ->and(filesize(resource_path('fonts/plus-jakarta-sans-latin.woff2')))->toBeGreaterThan(10_000);
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Guru/GuruStylesTest.php`
Expected: FAIL (`resources/css/guru.css` belum ada).

- [ ] **Step 3: Ambil font dari bundel desain**

Bundel `AbsenKU Guru — Alternatif PWA – Beranda.html` ada di root repo (salinannya di `~/Downloads`). Jalankan dari root repo:

```bash
python3 - <<'PY'
import re, json, base64, gzip
src = "AbsenKU Guru — Alternatif PWA – Beranda.html"
s = open(src, encoding="utf-8").read()
manifest = json.loads(re.search(r'<script type="__bundler/manifest">(.*?)</script>', s, re.S).group(1))
wanted = {
    "179983a6-f89b-479c-92ce-9d2d9d12d948": "resources/fonts/fraunces-latin.woff2",
    "39033314-39da-49ed-b256-ef3c45cf9af1": "resources/fonts/plus-jakarta-sans-latin.woff2",
}
for uuid, dest in wanted.items():
    entry = manifest[uuid]
    data = base64.b64decode(entry["data"])
    if str(entry.get("compressed")) == "True":
        data = gzip.decompress(data)
    open(dest, "wb").write(data)
    print(dest, len(data))
PY
```

Expected: `resources/fonts/fraunces-latin.woff2 67388` dan `resources/fonts/plus-jakarta-sans-latin.woff2 27272`.

- [ ] **Step 4: Buat `resources/css/guru.css`**

```css
/*
 * Halaman PWA guru — sistem visual "AbsenKU Guru".
 * Sumber: file desain "AbsenKU Guru — Alternatif PWA – Beranda.html".
 * Krem hangat + hijau tua; Fraunces untuk tanggal, jam, dan angka;
 * Plus Jakarta Sans untuk UI. Semua selector di-scope ke .guru.
 */

/* Fraunces (SIL OFL 1.1): variable, sumbu opsz 9–144 dan wght 100–900, subset latin. */
@font-face {
    font-family: "Fraunces";
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url("../fonts/fraunces-latin.woff2") format("woff2");
}

/* Plus Jakarta Sans (SIL OFL 1.1): variable, wght 200–800, subset latin, punya tnum. */
@font-face {
    font-family: "Plus Jakarta Sans";
    font-style: normal;
    font-weight: 200 800;
    font-display: swap;
    src: url("../fonts/plus-jakarta-sans-latin.woff2") format("woff2");
}

@theme inline {
    --font-guru: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
    --font-guru-display: "Fraunces", Georgia, serif;
    --color-guru-ground: var(--g-ground);
    --color-guru-surface: var(--g-surface);
    --color-guru-surface-2: var(--g-surface-2);
    --color-guru-ink: var(--g-ink);
    --color-guru-muted: var(--g-muted);
    --color-guru-border: var(--g-border);
    --color-guru-divider: var(--g-divider);
    --color-guru-primary: var(--g-primary);
    --color-guru-primary-soft: var(--g-primary-soft);
    --color-guru-late: var(--g-late);
    --color-guru-izin: var(--g-izin);
    --color-guru-attn: var(--g-attn);
}

/* ── Token ─────────────────────────────────────────────────────────── */

.guru {
    --g-ground: #F4F1EA;
    --g-surface: #FFFFFF;
    --g-surface-2: #F4F1EA;
    --g-ink: #1B2420;
    --g-muted: #5E665F;
    --g-border: #E3DDD0;
    --g-divider: #EFEAE0;
    --g-field: #8C8579;
    --g-primary: #1E5A48;
    --g-primary-hover: #153F33;
    --g-primary-soft: #DDE9E2;
    --g-late: #8A5A00;
    --g-late-bar: #D9A24A;
    --g-pending-bg: #F7EBCF;
    --g-pending-ink: #6E4700;
    --g-izin: #4A5A8C;
    --g-izin-bar: #7E8FC2;
    --g-izin-soft: #E3E7F3;
    --g-attn: #A8462A;
    --g-attn-soft: #F5E1D7;
    --g-attn-ink: #8A361E;
    --g-empty-bar: #ECE7DC;
    --g-info-media: #E9E1CF;
    --g-focus: #1E5A48;

    /* Kartu hero dan tombol pulang sama di kedua tema. */
    --g-hero: #1E5A48;
    --g-hero-2: #2F7560;
    --g-hero-line: #3C7E69;
    --g-hero-muted: #CFE3D8;
    --g-hero-dim: #8FB8A8;
    --g-hero-soft-ink: #E6F1EB;
    --g-accent: #F2C879;
    --g-pulang: #A8462A;

    --g-shadow: 0 1px 2px rgb(27 36 32 / 0.05), 0 8px 24px rgb(27 36 32 / 0.06);
    --g-ease: cubic-bezier(0.2, 0.8, 0.2, 1);

    color-scheme: light;
}

.dark .guru {
    --g-ground: #111512;
    --g-surface: #1A201C;
    --g-surface-2: #222924;
    --g-ink: #ECE8DF;
    --g-muted: #A3ABA4;
    --g-border: #2E3631;
    --g-divider: #262D28;
    --g-field: #6F786F;
    --g-primary: #8FD1B5;
    --g-primary-hover: #A9DEC6;
    --g-primary-soft: #22382F;
    --g-late: #E8B85C;
    --g-pending-bg: #3A3020;
    --g-pending-ink: #F2C879;
    --g-izin: #A9B6E6;
    --g-izin-soft: #262C40;
    --g-attn: #F0A58B;
    --g-attn-soft: #3A241C;
    --g-attn-ink: #F0A58B;
    --g-empty-bar: #2A312C;
    --g-info-media: #2A2F2A;
    --g-focus: #8FD1B5;
    --g-shadow: 0 1px 2px rgb(0 0 0 / 0.4);

    color-scheme: dark;
}

/* ── Dasar ─────────────────────────────────────────────────────────── */

@layer base {
    .guru {
        margin: 0;
        background: var(--g-ground);
        color: var(--g-ink);
        font-family: var(--font-guru);
        font-size: 15px;
        line-height: 1.5;
        -webkit-tap-highlight-color: transparent;
        text-rendering: optimizeLegibility;
    }

    .guru a {
        color: var(--g-primary);
        text-decoration: none;
    }

    .guru a:hover {
        color: var(--g-primary-hover);
    }

    .guru ::selection {
        background: var(--g-accent);
        color: #1B2420;
    }

    .guru :focus-visible {
        outline: 3px solid var(--g-focus);
        outline-offset: 2px;
    }

    .guru h1,
    .guru h2,
    .guru h3 {
        margin: 0;
        text-wrap: balance;
    }

    .guru [x-cloak] {
        display: none !important;
    }
}

/* ── Komponen ──────────────────────────────────────────────────────── */

@layer components {
    /* Shell */
    .g-app {
        min-height: 100svh;
        max-width: 30rem;
        margin-inline: auto;
        display: flex;
        flex-direction: column;
        background: var(--g-ground);
    }

    .g-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: max(16px, env(safe-area-inset-top)) 20px 0;
    }

    .g-header__title {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 18px;
        font-weight: 800;
    }

    .g-header__actions {
        display: flex;
        gap: 8px;
    }

    .g-iconbtn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: none;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        border: 1px solid var(--g-border);
        background: var(--g-surface);
        color: var(--g-ink);
        cursor: pointer;
        transition: background-color 160ms var(--g-ease), transform 150ms var(--g-ease);
    }

    .g-iconbtn:hover {
        background: var(--g-surface-2);
        color: var(--g-ink);
    }

    .g-iconbtn:active {
        transform: scale(0.96);
    }

    .g-iconbtn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .g-iconbtn__dot {
        position: absolute;
        top: 9px;
        right: 10px;
        width: 8px;
        height: 8px;
        border-radius: 4px;
        background: var(--g-pulang);
        border: 2px solid var(--g-surface);
    }

    .g-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding: 24px 20px calc(108px + env(safe-area-inset-bottom));
    }

    .g-main--bare {
        padding-bottom: calc(28px + env(safe-area-inset-bottom));
    }

    .g-nav {
        position: fixed;
        inset-inline: 0;
        bottom: 0;
        z-index: 40;
        max-width: 30rem;
        margin-inline: auto;
        display: flex;
        justify-content: space-around;
        padding: 8px 8px max(22px, env(safe-area-inset-bottom));
        background: var(--g-surface);
        border-top: 1px solid var(--g-border);
    }

    .g-nav__item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        min-width: 72px;
        min-height: 52px;
        color: var(--g-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .guru .g-nav__item {
        color: var(--g-muted);
    }

    .guru .g-nav__item[aria-current="page"] {
        color: var(--g-primary);
        font-weight: 800;
    }

    .g-nav__pill {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 32px;
        border-radius: 16px;
        transition: background-color 160ms var(--g-ease);
    }

    .g-nav__item[aria-current="page"] .g-nav__pill {
        background: var(--g-primary-soft);
    }

    /* Tipografi */
    .g-display {
        font-family: var(--font-guru-display);
        font-optical-sizing: auto;
    }

    .g-num {
        font-variant-numeric: tabular-nums;
    }

    .g-h2 {
        font-size: 15px;
        font-weight: 800;
    }

    .g-caps {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--g-muted);
    }

    .g-muted {
        color: var(--g-muted);
    }

    /* Header Beranda */
    .g-greet {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 48px;
        min-width: 0;
    }

    .guru .g-greet {
        color: var(--g-ink);
    }

    .g-greet span:last-child {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .g-greet small {
        font-size: 13px;
        color: var(--g-muted);
    }

    .g-greet strong {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 17px;
        font-weight: 700;
    }

    .g-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: none;
        width: 44px;
        height: 44px;
        overflow: hidden;
        border-radius: 22px;
        background: var(--g-primary-soft);
        color: var(--g-primary);
        font-size: 15px;
        font-weight: 800;
    }

    .g-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .g-avatar--sm {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        font-size: 13px;
    }

    .g-avatar--xl {
        width: 88px;
        height: 88px;
        border-radius: 44px;
        font-size: 28px;
    }

    .g-dateline {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
    }

    .g-dateline h1,
    .g-dateline p:first-child {
        font-family: var(--font-guru-display);
        font-size: 26px;
        font-weight: 600;
        letter-spacing: -0.01em;
        line-height: 1.2;
    }

    .g-dateline > :last-child {
        flex: none;
        font-size: 13px;
        font-weight: 600;
        color: var(--g-muted);
    }

    /* Kartu */
    .g-card {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 20px;
        border-radius: 24px;
        background: var(--g-surface);
        border: 1px solid var(--g-border);
    }

    .guru a.g-card {
        color: var(--g-ink);
        transition: background-color 160ms var(--g-ease);
    }

    .guru a.g-card:hover {
        background: var(--g-surface-2);
    }

    .g-card--flush {
        padding: 0;
        gap: 0;
        overflow: hidden;
    }

    .g-card__caps {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--g-muted);
    }

    .g-card__title {
        font-family: var(--font-guru-display);
        font-size: 26px;
        font-weight: 600;
        line-height: 1.15;
    }

    /* Kartu hero "Presensi hari ini" */
    .g-card--hero {
        gap: 18px;
        border: 0;
        background: var(--g-hero);
        color: #FFFFFF;
    }

    .g-hero__label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--g-hero-muted);
    }

    .g-hero__times {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .g-hero__time {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .g-hero__time + .g-hero__time {
        padding-left: 16px;
        border-left: 1px solid var(--g-hero-line);
    }

    .g-hero__time-label {
        font-size: 13px;
        color: var(--g-hero-muted);
    }

    .g-hero__time-value {
        font-family: var(--font-guru-display);
        font-size: 44px;
        font-weight: 500;
        line-height: 1;
    }

    .g-hero__time-value.is-empty {
        color: var(--g-hero-dim);
    }

    .g-hero__loc {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--g-hero-soft-ink);
    }

    .g-progress {
        position: relative;
        height: 8px;
        border-radius: 4px;
        background: var(--g-hero-2);
    }

    .g-progress__fill {
        position: absolute;
        inset-block: 0;
        left: 0;
        width: var(--p, 0%);
        border-radius: 4px;
        background: var(--g-accent);
        transition: width 400ms var(--g-ease);
    }

    .g-progress__knob {
        position: absolute;
        top: -4px;
        left: var(--p, 0%);
        width: 16px;
        height: 16px;
        margin-left: -8px;
        border-radius: 8px;
        background: #FFFFFF;
        border: 3px solid var(--g-accent);
        transition: left 400ms var(--g-ease);
    }

    .g-progress__labels {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: var(--g-hero-muted);
    }

    .g-progress__labels span:nth-child(2) {
        text-align: center;
    }

    /* Rekap */
    .g-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .g-stat {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .g-stat__value {
        font-family: var(--font-guru-display);
        font-size: 34px;
        font-weight: 600;
        line-height: 1.1;
        color: var(--g-ink);
    }

    .g-stat__value--primary { color: var(--g-primary); }
    .g-stat__value--late { color: var(--g-late); }
    .g-stat__value--izin { color: var(--g-izin); }

    .g-stat__label {
        font-size: 12px;
        font-weight: 600;
        color: var(--g-muted);
    }

    .g-stackbar {
        display: flex;
        gap: 3px;
        height: 10px;
    }

    .g-stackbar > span {
        min-width: 0;
    }

    .g-stackbar > span:first-child { border-radius: 5px 0 0 5px; }
    .g-stackbar > span:last-child { border-radius: 0 5px 5px 0; }
    .g-stackbar > span:only-child { border-radius: 5px; }
    .g-stackbar .is-ok { background: var(--g-hero); }
    .g-stackbar .is-late { background: var(--g-late-bar); }
    .g-stackbar .is-izin { background: var(--g-izin-bar); }
    .g-stackbar .is-rest { background: var(--g-empty-bar); }

    /* Chip */
    .g-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        white-space: nowrap;
        background: var(--g-surface-2);
        color: var(--g-ink);
    }

    .g-chip--ok { background: var(--g-primary-soft); color: var(--g-primary); }
    .g-chip--late,
    .g-chip--pending { background: var(--g-pending-bg); color: var(--g-pending-ink); }
    .g-chip--izin { background: var(--g-izin-soft); color: var(--g-izin); }
    .g-chip--attn { background: var(--g-attn-soft); color: var(--g-attn-ink); }
    .g-chip--hero { background: var(--g-hero-2); color: #FFFFFF; }
    .g-chip--hero-late { background: var(--g-accent); color: #1B2420; }

    /* Section dan daftar */
    .g-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: -10px;
    }

    .g-section h2 {
        font-size: 15px;
        font-weight: 800;
    }

    .g-section a {
        display: flex;
        align-items: center;
        min-height: 44px;
        font-size: 13px;
        font-weight: 700;
    }

    .g-list {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 24px;
        background: var(--g-surface);
        border: 1px solid var(--g-border);
    }

    .g-list__item {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 64px;
        padding: 10px 16px;
        color: var(--g-ink);
    }

    .guru .g-list__item {
        color: var(--g-ink);
    }

    .g-list > .g-list__item + .g-list__item {
        border-top: 1px solid var(--g-divider);
    }

    .guru a.g-list__item {
        transition: background-color 160ms var(--g-ease);
    }

    .guru a.g-list__item:hover {
        background: var(--g-surface-2);
        color: var(--g-ink);
    }

    .g-list__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: none;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--g-primary-soft);
        color: var(--g-primary);
    }

    .g-list__icon--attn { background: var(--g-attn-soft); color: var(--g-attn); }
    .g-list__icon--izin { background: var(--g-izin-soft); color: var(--g-izin); }
    .g-list__icon--neutral { background: var(--g-surface-2); color: var(--g-ink); }
    .g-list__icon--late { background: var(--g-pending-bg); color: var(--g-late); }

    .g-list__body {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .g-list__title {
        font-size: 15px;
        font-weight: 700;
    }

    .g-list__desc {
        font-size: 12px;
        color: var(--g-muted);
    }

    .g-list__chev {
        flex: none;
        color: var(--g-muted);
    }

    /* Tombol */
    .g-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 56px;
        padding: 0 20px;
        border-radius: 16px;
        border: 1px solid transparent;
        font: inherit;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        transition: transform 150ms var(--g-ease), background-color 160ms var(--g-ease), opacity 160ms var(--g-ease);
    }

    .g-btn:active {
        transform: scale(0.98);
    }

    .g-btn:disabled,
    .g-btn[aria-disabled="true"],
    .g-btn[aria-busy="true"] {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }

    .guru .g-btn--primary { background: #1E5A48; color: #FFFFFF; }
    .guru .g-btn--primary:hover { background: #153F33; color: #FFFFFF; }
    .guru .g-btn--hero { background: #FFFFFF; color: #1E5A48; }
    .guru .g-btn--hero:hover { background: #F4F1EA; color: #153F33; }
    .guru .g-btn--hero[aria-disabled="true"] { background: rgb(255 255 255 / 0.14); color: var(--g-hero-muted); opacity: 1; }
    .guru .g-btn--pulang { background: var(--g-pulang); color: #FFFFFF; }
    .guru .g-btn--pulang:hover { background: #8A361E; color: #FFFFFF; }
    .guru .g-btn--secondary { background: var(--g-surface); color: var(--g-ink); border-color: var(--g-border); }
    .guru .g-btn--secondary:hover { background: var(--g-surface-2); color: var(--g-ink); }
    .guru .g-btn--danger { background: var(--g-pulang); color: #FFFFFF; }

    .g-btn--block {
        width: 100%;
    }

    .g-btn--sm {
        min-height: 44px;
        padding: 0 14px;
        border-radius: 14px;
        font-size: 14px;
    }

    /* Form */
    .g-field {
        display: flex;
        flex-direction: column;
    }

    .g-label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 700;
        color: var(--g-ink);
    }

    .g-req {
        color: var(--g-attn);
    }

    .g-input {
        display: block;
        width: 100%;
        min-height: 52px;
        padding: 12px 14px;
        border-radius: 14px;
        border: 1.5px solid var(--g-field);
        background: var(--g-surface);
        color: var(--g-ink);
        font: inherit;
        font-size: 16px;
        transition: border-color 160ms var(--g-ease), box-shadow 160ms var(--g-ease);
    }

    .g-input::placeholder {
        color: var(--g-muted);
        opacity: 1;
    }

    .g-input:focus {
        outline: none;
        border-color: var(--g-primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--g-primary) 22%, transparent);
    }

    .g-input[aria-invalid="true"] {
        border-color: var(--g-attn);
    }

    .g-input:disabled {
        background: var(--g-surface-2);
        color: var(--g-muted);
        cursor: not-allowed;
    }

    textarea.g-input {
        min-height: 112px;
        resize: vertical;
    }

    select.g-input {
        appearance: none;
        padding-right: 44px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%235E665F' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 18px;
    }

    .g-hint {
        margin-top: 6px;
        font-size: 12px;
        color: var(--g-muted);
    }

    .g-error {
        margin-top: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--g-attn);
    }

    .g-search {
        position: relative;
    }

    .g-search > svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--g-muted);
        pointer-events: none;
    }

    .g-search .g-input {
        padding-left: 44px;
    }

    .g-choices {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .g-choice {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 84px;
        padding: 10px;
        border-radius: 16px;
        border: 1.5px solid var(--g-border);
        background: var(--g-surface);
        color: var(--g-ink);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: border-color 160ms var(--g-ease), background-color 160ms var(--g-ease);
    }

    .g-choice input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .g-choice:has(input:checked) {
        border-color: var(--g-primary);
        background: var(--g-primary-soft);
        color: var(--g-primary);
    }

    .g-choice:has(input:focus-visible) {
        outline: 3px solid var(--g-focus);
        outline-offset: 2px;
    }

    .g-choice__check {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 10px;
        background: var(--g-primary);
        color: var(--g-surface);
        opacity: 0;
        transform: scale(0.6);
        transition: opacity 160ms var(--g-ease), transform 160ms var(--g-ease);
    }

    .g-choice:has(input:checked) .g-choice__check {
        opacity: 1;
        transform: scale(1);
    }

    .g-drop {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 18px;
        border-radius: 16px;
        border: 1.5px dashed var(--g-field);
        background: var(--g-surface);
        text-align: center;
        cursor: pointer;
    }

    .g-drop:has(input:focus-visible) {
        outline: 3px solid var(--g-focus);
        outline-offset: 2px;
    }

    .g-drop__preview {
        max-height: 160px;
        border-radius: 12px;
        object-fit: contain;
    }

    .g-seg {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
        padding: 4px;
        border-radius: 16px;
        background: var(--g-surface-2);
    }

    .g-seg label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 60px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        color: var(--g-muted);
        cursor: pointer;
    }

    .g-seg input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .g-seg label:has(input:checked) {
        background: var(--g-surface);
        color: var(--g-primary);
        box-shadow: var(--g-shadow);
    }

    .g-seg label:has(input:focus-visible) {
        outline: 3px solid var(--g-focus);
        outline-offset: 2px;
    }

    /* Pesan dan status kosong */
    .g-notice {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px;
        border-radius: 16px;
        background: var(--g-surface-2);
        font-size: 14px;
        color: var(--g-ink);
    }

    .g-notice strong {
        display: block;
        font-size: 15px;
        font-weight: 700;
    }

    .g-notice__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: none;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: var(--g-surface);
        color: var(--g-ink);
    }

    .g-notice--warn { background: var(--g-pending-bg); }
    .g-notice--warn .g-notice__icon { color: var(--g-late); }
    .g-notice--error { background: var(--g-attn-soft); }
    .g-notice--error .g-notice__icon { color: var(--g-attn); }
    .g-notice--ok { background: var(--g-primary-soft); }
    .g-notice--ok .g-notice__icon { color: var(--g-primary); }

    .g-empty {
        align-items: center;
        gap: 10px;
        padding: 32px 20px;
        text-align: center;
    }

    .g-empty__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: var(--g-surface-2);
        color: var(--g-muted);
    }

    .g-empty h2 {
        font-size: 16px;
        font-weight: 800;
    }

    .g-empty p {
        max-width: 32ch;
        font-size: 13px;
        color: var(--g-muted);
    }

    /* Definisi (label : nilai) */
    .g-dl > div {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 0;
        font-size: 14px;
    }

    .g-dl > div + div {
        border-top: 1px solid var(--g-divider);
    }

    .g-dl dt {
        color: var(--g-muted);
    }

    .g-dl dd {
        margin: 0;
        max-width: 62%;
        text-align: right;
        font-weight: 600;
    }

    /* Informasi (carousel) */
    .g-carousel {
        display: flex;
        gap: 12px;
        margin-right: -20px;
        padding-right: 20px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
    }

    .g-carousel::-webkit-scrollbar {
        display: none;
    }

    .g-info {
        flex: none;
        width: 280px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 20px;
        background: var(--g-surface);
        border: 1px solid var(--g-border);
        scroll-snap-align: start;
    }

    .guru .g-info {
        color: var(--g-ink);
    }

    .g-info__media {
        position: relative;
        height: 96px;
        display: flex;
        align-items: flex-end;
        padding: 12px;
        background: var(--g-info-media);
    }

    .g-info__media img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .g-info__tag {
        position: relative;
        padding: 4px 8px;
        border-radius: 8px;
        background: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
        color: #1E5A48;
    }

    .g-info__body {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 14px;
    }

    /* Menu dan dialog */
    .g-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        z-index: 50;
        width: min(18rem, calc(100vw - 40px));
        padding: 6px;
        border-radius: 16px;
        background: var(--g-surface);
        border: 1px solid var(--g-border);
        box-shadow: 0 12px 32px rgb(27 36 32 / 0.16);
    }

    .g-menu__item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-height: 56px;
        padding: 8px 10px;
        border-radius: 12px;
        background: transparent;
        color: var(--g-ink);
        font: inherit;
        text-align: left;
        cursor: pointer;
    }

    .g-menu__item:hover {
        background: var(--g-surface-2);
    }

    .g-dialog {
        width: min(100% - 40px, 24rem);
        padding: 20px;
        border: 0;
        border-radius: 24px;
        background: var(--g-surface);
        color: var(--g-ink);
    }

    .g-dialog::backdrop {
        background: rgb(17 21 18 / 0.5);
    }

    /* Banner pasang PWA */
    .g-install {
        position: fixed;
        inset-inline: 16px;
        bottom: calc(96px + env(safe-area-inset-bottom));
        z-index: 45;
        max-width: 28rem;
        margin-inline: auto;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 20px;
        background: var(--g-hero);
        color: #FFFFFF;
        box-shadow: 0 12px 32px rgb(17 21 18 / 0.25);
    }

    .g-install p {
        flex: 1;
        font-size: 13px;
        color: var(--g-hero-muted);
    }

    .g-install strong {
        display: block;
        font-size: 15px;
        color: #FFFFFF;
    }

    /* Riwayat */
    .g-history__date {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: none;
        width: 44px;
        line-height: 1;
    }

    .g-history__date span:first-child {
        font-family: var(--font-guru-display);
        font-size: 26px;
        font-weight: 600;
    }

    .g-history__date span:last-child {
        margin-top: 4px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--g-muted);
    }

    .g-history__times {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 14px;
        font-size: 14px;
        color: var(--g-muted);
    }

    .g-history__times b {
        color: var(--g-ink);
        font-weight: 700;
    }

    /* Paginasi Laravel */
    .g-pager nav[role="navigation"] a,
    .g-pager nav[role="navigation"] span {
        display: inline-flex;
        align-items: center;
        min-height: 44px;
        padding: 0 16px;
        border-radius: 14px;
        border: 1px solid var(--g-border);
        background: var(--g-surface);
        color: var(--g-ink);
        font-size: 14px;
        font-weight: 700;
    }

    /* Kamera */
    .g-camera {
        position: relative;
        aspect-ratio: 3 / 4;
        overflow: hidden;
        border-radius: 24px;
        background: #0B0F0C;
    }

    .g-camera video,
    .g-camera canvas {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .g-camera__overlay {
        position: absolute;
        inset: 0;
        z-index: 20;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 24px;
        text-align: center;
        background: rgb(11 15 12 / 0.92);
        color: #FFFFFF;
    }

    .g-camera__overlay strong {
        font-size: 16px;
        font-weight: 800;
    }

    .g-camera__overlay p {
        max-width: 30ch;
        font-size: 14px;
        color: #CFE3D8;
    }

    .g-camera__prompt {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 12px;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 14px;
        background: rgb(11 15 12 / 0.8);
        color: #FFFFFF;
        font-size: 15px;
        font-weight: 700;
    }

    .g-camera__guide {
        position: absolute;
        inset: 12% 14%;
        z-index: 10;
        border: 2px dashed rgb(255 255 255 / 0.35);
        border-radius: 50%;
        pointer-events: none;
    }

    .g-camera__scan {
        position: absolute;
        inset-inline: 12px;
        top: 0;
        z-index: 10;
        height: 100%;
        pointer-events: none;
        animation: g-scan 3.2s ease-in-out infinite;
    }

    .g-camera__scan::before {
        content: "";
        display: block;
        height: 2px;
        border-radius: 1px;
        background: var(--g-accent);
        opacity: 0.85;
    }

    @keyframes g-scan {
        0%, 100% { transform: translateY(10%); }
        50% { transform: translateY(88%); }
    }

    .g-camera__bracket {
        position: absolute;
        z-index: 10;
        width: 26px;
        height: 26px;
        border-color: #FFFFFF;
        opacity: 0.85;
        pointer-events: none;
    }

    .g-camera__flash {
        position: absolute;
        inset: 0;
        z-index: 30;
        background: #FFFFFF;
        pointer-events: none;
    }

    .g-camera__badge {
        position: absolute;
        left: 50%;
        top: 16px;
        z-index: 10;
        transform: translateX(-50%);
    }

    .g-status {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .guru *,
    .guru *::before,
    .guru *::after {
        transition-duration: 0ms !important;
        transition-delay: 0ms !important;
        animation-duration: 0ms !important;
        animation-iteration-count: 1 !important;
    }

    ::view-transition-group(*),
    ::view-transition-old(*),
    ::view-transition-new(*) {
        animation: none !important;
    }
}
```

- [ ] **Step 5: Import di `app.css`**

Di `resources/css/app.css`, ganti baris 1:

```css
@import "tailwindcss";
```

dengan:

```css
@import "tailwindcss";
@import "./guru.css";
```

- [ ] **Step 6: Jalankan test dan build**

Run: `php artisan test tests/Feature/Guru/GuruStylesTest.php && npm run build 2>&1 | grep -E "built|fraunces|jakarta|rror"`
Expected: 2 test PASS; build menampilkan `fraunces-latin-*.woff2`, `plus-jakarta-sans-latin-*.woff2`, dan `✓ built`.

- [ ] **Step 7: Commit**

```bash
git add resources/fonts/fraunces-latin.woff2 resources/fonts/plus-jakarta-sans-latin.woff2 resources/css/guru.css resources/css/app.css tests/Feature/Guru/GuruStylesTest.php public/build
git commit -m "feat(guru): sistem visual dan font untuk PWA guru

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 2: Komponen Blade `x-guru.*`

**Files:**
- Create: `resources/views/components/guru/icon.blade.php`, `card.blade.php`, `chip.blade.php`, `section.blade.php`, `list.blade.php`, `list-item.blade.php`, `stat.blade.php`, `button.blade.php`, `field.blade.php`, `empty.blade.php`, `notice.blade.php`
- Test: `tests/Feature/Guru/GuruComponentsTest.php`

**Interfaces:**
- Consumes: kelas CSS dari Task 1.
- Produces (props persis):
  - `<x-guru.icon name="…" :size="20" />`. Nama: home, calendar, doc, users, swap, bell, pin, login, logout, chevron, back, send, chat, lock, camera, clock, check, x, alert, info, refresh, search, eye, eye-off, user, plus, image, sun, moon, monitor, clipboard, inbox, school, megaphone, phone, briefcase, medical. Nama tak dikenal menghasilkan `<svg>` kosong.
  - `<x-guru.card as="section|div|a" variant="hero|flush|null">`
  - `<x-guru.chip tone="neutral|ok|late|pending|izin|attn|hero|hero-late">`
  - `<x-guru.section title="…" :href="…" link="…" />`
  - `<x-guru.list>` membungkus `<x-guru.list-item :href icon tone title desc>`, dengan slot `end` untuk elemen kanan. Judul dirender persis `<span class="g-list__title">{{ $title }}</span>`.
  - `<x-guru.stat :value label tone="ink|primary|late|izin" />`
  - `<x-guru.button :href variant="primary|hero|pulang|secondary|danger" type="button|submit" icon="…">`. Tag `<a>` bila `href` diisi, selain itu `<button>`.
  - `<x-guru.field label for :error="'name'" hint :required="bool">` dengan slot kontrol. Error dirender sebagai `<p id="{for}-error" class="g-error">`.
  - `<x-guru.empty icon title>` dengan slot teks dan slot `action`.
  - `<x-guru.notice tone="info|warn|error|ok" title icon>` dengan slot isi.

- [ ] **Step 1: Tulis test yang gagal**

```php
<?php

// tests/Feature/Guru/GuruComponentsTest.php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

test('list-item renders a link with a plain title node and a chevron', function () {
    $html = Blade::render('<x-guru.list-item href="/izin" icon="doc" title="Kesiswaan" desc="Direktori siswa" />');

    expect($html)
        ->toContain('href="/izin"')
        ->toContain('<span class="g-list__title">Kesiswaan</span>')
        ->toContain('class="g-list__desc">Direktori siswa</span>')
        ->toContain('g-list__chev')
        ->toContain('aria-hidden="true"');
});

test('list-item without href renders a div without chevron', function () {
    $html = Blade::render('<x-guru.list-item title="Statis" />');

    expect($html)->toContain('<div class="g-list__item"')->not->toContain('g-list__chev');
});

test('button renders an anchor with href and a button otherwise', function () {
    expect(Blade::render('<x-guru.button href="/x" variant="hero">Absen</x-guru.button>'))
        ->toContain('<a href="/x"')->toContain('g-btn g-btn--hero');
    expect(Blade::render('<x-guru.button type="submit">Kirim</x-guru.button>'))
        ->toContain('<button type="submit"')->toContain('g-btn--primary');
});

test('chip, stat and card apply their modifier classes', function () {
    expect(Blade::render('<x-guru.chip tone="attn">3</x-guru.chip>'))->toContain('g-chip g-chip--attn');
    expect(Blade::render('<x-guru.stat value="17" label="Tepat waktu" tone="primary" />'))
        ->toContain('g-stat__value g-stat__value--primary')->toContain('>17<')->toContain('Tepat waktu');
    expect(Blade::render('<x-guru.card variant="hero" aria-label="Presensi">x</x-guru.card>'))
        ->toContain('<section class="g-card g-card--hero" aria-label="Presensi">');
});

test('field wires label, required marker and validation error', function () {
    $errors = (new ViewErrorBag)->put('default', new MessageBag(['name' => ['Nama wajib diisi.']]));

    $html = Blade::render(
        '<x-guru.field label="Nama" for="name" error="name" :required="true"><input id="name"></x-guru.field>',
        ['errors' => $errors],
    );

    expect($html)
        ->toContain('<label for="name" class="g-label">')
        ->toContain('class="g-req" aria-hidden="true">*</span>')
        ->toContain('<p id="name-error" class="g-error">Nama wajib diisi.</p>');
});

test('icon renders known paths and an empty svg for unknown names', function () {
    expect(Blade::render('<x-guru.icon name="bell" />'))->toContain('viewBox="0 0 24 24"')->toContain('<path');
    expect(Blade::render('<x-guru.icon name="nope" />'))->toContain('<svg')->not->toContain('<path');
});

test('empty and notice render their slots', function () {
    expect(Blade::render('<x-guru.empty icon="inbox" title="Belum ada">Isi nanti.</x-guru.empty>'))
        ->toContain('<h2>Belum ada</h2>')->toContain('Isi nanti.');
    expect(Blade::render('<x-guru.notice tone="error" title="Gagal">Coba lagi.</x-guru.notice>'))
        ->toContain('g-notice g-notice--error')->toContain('<strong>Gagal</strong>');
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Guru/GuruComponentsTest.php`
Expected: FAIL ("Unable to locate a class or view for component [guru.list-item]").

- [ ] **Step 3: Buat `resources/views/components/guru/icon.blade.php`**

```blade
{{-- Set ikon garis gaya desain (stroke 1.8). Semua path statis dan tepercaya. --}}
@props([
    'name',
    'size' => 20,
])

@php
    $paths = [
        'home' => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'calendar' => '<rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/>',
        'doc' => '<path d="M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
        'swap' => '<path d="M7 16V4m0 0L3 8m4-4 4 4M17 8v12m0 0 4-4m-4 4-4-4"/>',
        'bell' => '<path d="M6 16v-5a6 6 0 1 1 12 0v5l1.5 2h-15z"/><path d="M10 20.5a2 2 0 0 0 4 0"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'login' => '<path d="M13 4h6a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-6"/><path d="M4 12h11m0 0-4-4m4 4-4 4"/>',
        'logout' => '<path d="M11 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h6"/><path d="M9 12h11m0 0-4-4m4 4-4 4"/>',
        'chevron' => '<path d="m9 6 6 6-6 6"/>',
        'back' => '<path d="m15 6-6 6 6 6"/>',
        'send' => '<path d="M4 4l16 8-16 8 3-8z"/><path d="M7 12h13"/>',
        'chat' => '<path d="M4 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H9l-5 4z"/><path d="M8 8.5h8M8 12h5"/>',
        'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'camera' => '<path d="M4 8a2 2 0 0 1 2-2h2l1.5-2h5L16 6h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><circle cx="12" cy="13" r="3.5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check' => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'alert' => '<path d="M12 3.5 2.5 20h19z"/><path d="M12 10v4.5M12 17.5v.01"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8v.01"/>',
        'refresh' => '<path d="M20 11a8 8 0 0 0-14.3-4.9L4 8"/><path d="M4 4v4h4M4 13a8 8 0 0 0 14.3 4.9L20 16"/><path d="M20 20v-4h-4"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'eye' => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M3 3l18 18"/><path d="M10.6 5.6A9.7 9.7 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a17 17 0 0 1-3 3.8M6.2 6.9A16.6 16.6 0 0 0 2.5 12S6 18.5 12 18.5a9.4 9.4 0 0 0 4.4-1.1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20.5a7.5 7.5 0 0 1 15 0"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="9.5" r="1.8"/><path d="m21 16-5-5-9 9"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M2.5 12h2M19.5 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/>',
        'moon' => '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"/>',
        'monitor' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
        'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 12l2 2 4-4"/>',
        'inbox' => '<path d="M3 13h5l1.5 3h5L16 13h5"/><path d="M5 5h14l2 8v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-6z"/>',
        'school' => '<path d="M3 9.5 12 5l9 4.5-9 4.5z"/><path d="M7 11.5V16c0 1.4 2.2 2.5 5 2.5s5-1.1 5-2.5v-4.5"/>',
        'megaphone' => '<path d="M4 10v4h3l7 4V6L7 10z"/><path d="M17.5 9a4 4 0 0 1 0 6"/>',
        'phone' => '<rect x="7" y="2.5" width="10" height="19" rx="2"/><path d="M11 18.5h2"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M3 12.5h18"/>',
        'medical' => '<rect x="3.5" y="3.5" width="17" height="17" rx="4"/><path d="M12 8v8M8 12h8"/>',
    ];
@endphp

<svg {{ $attributes->merge([
    'width' => $size,
    'height' => $size,
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-width' => '1.8',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'aria-hidden' => 'true',
    'focusable' => 'false',
]) }}>{!! $paths[$name] ?? '' !!}</svg>
```

- [ ] **Step 4: Buat komponen lainnya**

`resources/views/components/guru/card.blade.php`:

```blade
@props([
    'as' => 'section',
    'variant' => null,
])

<{{ $as }} {{ $attributes->class(['g-card', 'g-card--'.$variant => $variant]) }}>{{ $slot }}</{{ $as }}>
```

`resources/views/components/guru/chip.blade.php`:

```blade
@props(['tone' => 'neutral'])

<span {{ $attributes->class(['g-chip', 'g-chip--'.$tone]) }}>{{ $slot }}</span>
```

`resources/views/components/guru/section.blade.php`:

```blade
@props([
    'title',
    'href' => null,
    'link' => null,
])

<div {{ $attributes->class(['g-section']) }}>
    <h2>{{ $title }}</h2>
    @if ($href && $link)
        <a href="{{ $href }}">{{ $link }}</a>
    @endif
</div>
```

`resources/views/components/guru/list.blade.php`:

```blade
<div {{ $attributes->class(['g-list']) }}>{{ $slot }}</div>
```

`resources/views/components/guru/list-item.blade.php`:

```blade
{{-- Baris daftar 64px: ikon bertone, judul, keterangan, slot `end`, chevron bila tautan. --}}
@props([
    'href' => null,
    'icon' => null,
    'tone' => 'primary',
    'title',
    'desc' => null,
])

@php($tag = $href ? 'a' : 'div')

<{{ $tag }} {{ $attributes->class(['g-list__item'])->merge($href ? ['href' => $href] : []) }}>
    @if ($icon)
        <span class="g-list__icon g-list__icon--{{ $tone }}"><x-guru.icon :name="$icon" /></span>
    @endif
    <span class="g-list__body">
        <span class="g-list__title">{{ $title }}</span>
        @if ($desc)
            <span class="g-list__desc">{{ $desc }}</span>
        @endif
        {{ $slot }}
    </span>
    {{ $end ?? '' }}
    @if ($href)
        <x-guru.icon name="chevron" :size="18" class="g-list__chev" />
    @endif
</{{ $tag }}>
```

`resources/views/components/guru/stat.blade.php`:

```blade
@props([
    'value',
    'label',
    'tone' => 'ink',
])

<div {{ $attributes->class(['g-stat']) }}>
    <span class="g-stat__value g-stat__value--{{ $tone }}">{{ $value }}</span>
    <span class="g-stat__label">{{ $label }}</span>
</div>
```

`resources/views/components/guru/button.blade.php`:

```blade
@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'icon' => null,
])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['g-btn', 'g-btn--'.$variant]) }}>
        @if ($icon)<x-guru.icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['g-btn', 'g-btn--'.$variant]) }}>
        @if ($icon)<x-guru.icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
```

`resources/views/components/guru/field.blade.php`:

```blade
{{-- Kontrol di slot wajib memakai id="{{ $for }}" dan, saat error,
     aria-invalid="true" + aria-describedby="{{ $for }}-error". --}}
@props([
    'label',
    'for',
    'error' => null,
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->class(['g-field']) }}>
    <label for="{{ $for }}" class="g-label">{{ $label }}@if ($required) <span class="g-req" aria-hidden="true">*</span>@endif</label>
    {{ $slot }}
    @if ($hint)
        <p id="{{ $for }}-hint" class="g-hint">{{ $hint }}</p>
    @endif
    @if ($error)
        @error($error)
            <p id="{{ $for }}-error" class="g-error">{{ $message }}</p>
        @enderror
    @endif
</div>
```

`resources/views/components/guru/empty.blade.php`:

```blade
@props([
    'icon' => 'inbox',
    'title',
])

<div {{ $attributes->class(['g-card', 'g-empty']) }}>
    <span class="g-empty__icon"><x-guru.icon :name="$icon" :size="24" /></span>
    <h2>{{ $title }}</h2>
    <p>{{ $slot }}</p>
    {{ $action ?? '' }}
</div>
```

`resources/views/components/guru/notice.blade.php`:

```blade
{{-- tone: info | warn | error | ok. Tambahkan role="alert"/"status" bila pesan mengumumkan perubahan. --}}
@props([
    'tone' => 'info',
    'title' => null,
    'icon' => null,
])

@php
    $icon ??= match ($tone) {
        'warn' => 'alert',
        'error' => 'alert',
        'ok' => 'check',
        default => 'info',
    };
@endphp

<div {{ $attributes->class(['g-notice', 'g-notice--'.$tone => $tone !== 'info']) }}>
    <span class="g-notice__icon"><x-guru.icon :name="$icon" :size="18" /></span>
    <div class="min-w-0">
        @if ($title)
            <strong>{{ $title }}</strong>
        @endif
        {{ $slot }}
    </div>
</div>
```

- [ ] **Step 5: Jalankan test, pastikan lulus**

Run: `php artisan test tests/Feature/Guru/GuruComponentsTest.php`
Expected: PASS (8 test).

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/guru tests/Feature/Guru/GuruComponentsTest.php
git commit -m "feat(guru): komponen Blade x-guru untuk halaman guru

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 3: Shell `x-layouts.mobile` dan nav bawah

**Files:**
- Modify (tulis ulang): `resources/views/components/layouts/mobile.blade.php`
- Modify (tulis ulang): `tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php`

**Interfaces:**
- Consumes: `x-guru.icon` (Task 2), kelas `g-app`, `g-header`, `g-iconbtn`, `g-main`, `g-nav*` (Task 1).
- Produces:
  - **Props layout:** `title`, `backUrl`, `activeTab` (`beranda|riwayat|izin|kelas`, nilai lain boleh), `showNav` (bool atau string `"true"`/`"false"`), `isSheet` (diabaikan).
  - **Slot:** `header` menggantikan header default (dipakai Beranda), `headerAction` untuk aksi kanan di header default, `scripts` untuk script sebelum `</body>`.
  - **Fungsi JS global:** `window.guruApplyTheme(mode)`, `window.guruThemeMode()` yang mengembalikan `'light'|'dark'|'system'`, dan `window.guruSetTheme(mode)`.
  - **Penanda:** `<body class="guru" data-teacher-ui="absenku-guru">`, `data-region="top-app-bar|content|navigation-bar"`.

- [ ] **Step 1: Tulis ulang test shell (gagal lebih dulu)**

Ganti seluruh isi `tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php` dengan:

```php
<?php

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;

function teacherForVisualSystem(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

function homeroomForVisualSystem(User $teacher): void
{
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $class = SchoolClass::create(['school_level' => 'mi', 'name' => 'Kelas 4B', 'normalized_name' => SchoolClass::normalizeName('Kelas 4B'), 'grade_level' => 4, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);
}

it('renders teacher pages inside the AbsenKU Guru shell', function (string $routeName, string $expectedContent) {
    $this->actingAs(teacherForVisualSystem())
        ->get(route($routeName))
        ->assertSuccessful()
        ->assertSee('<body class="guru" data-teacher-ui="absenku-guru">', false)
        ->assertSee('data-region="content"', false)
        ->assertSee('aria-label="Navigasi utama"', false)
        ->assertSee('@view-transition', false)
        ->assertDontSee('maximum-scale=1', false)
        ->assertDontSee('user-scalable=no', false)
        ->assertSee($expectedContent);
})->with([
    'check-in camera' => ['attendance.selfie', 'Absensi Masuk'],
    'check-out flow' => ['attendance.checkout', 'Absensi Pulang'],
    'attendance history' => ['attendance.index', 'Riwayat Absen'],
    'profile form' => ['attendance.profile', 'Profil Saya'],
    'password form' => ['attendance.password', 'Ganti Password'],
    'leave list' => ['attendance.leaves.index', 'Perizinan Saya'],
    'leave create form' => ['attendance.leaves.create', 'Jenis Perizinan'],
]);

it('shows three tabs for a regular teacher and marks the current one', function () {
    $html = $this->actingAs(teacherForVisualSystem())
        ->get(route('attendance.index'))
        ->assertSuccessful()
        ->getContent();

    expect(substr_count($html, 'class="g-nav__item"'))->toBe(3)
        ->and($html)->toContain('href="'.route('attendance.index').'" class="g-nav__item" aria-current="page"')
        ->not->toContain('href="'.route('attendance.my-class.index').'" class="g-nav__item"');
});

it('adds the Kelas tab for a homeroom teacher', function () {
    $teacher = teacherForVisualSystem();
    homeroomForVisualSystem($teacher);

    $html = $this->actingAs($teacher)->get(route('attendance.index'))->assertSuccessful()->getContent();

    expect(substr_count($html, 'class="g-nav__item"'))->toBe(4)
        ->and($html)->toContain('href="'.route('attendance.my-class.index').'" class="g-nav__item"');
});

it('applies the saved or system theme before first paint', function () {
    $html = $this->actingAs(teacherForVisualSystem())->get(route('attendance.index'))->getContent();

    expect($html)
        ->toContain("localStorage.getItem('appearance') ?? localStorage.getItem('welcome-theme')")
        ->toContain("matchMedia('(prefers-color-scheme: dark)')")
        ->toContain("document.documentElement.classList.toggle('dark', dark)")
        ->and(strpos($html, 'window.guruApplyTheme'))->toBeLessThan(strpos($html, '<body'));
});

it('downscales browser selfie captures to a maximum dimension of 1024 pixels', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/{$view}.blade.php"));

    expect($template)
        ->toContain('const maxPhotoDimension = 1024;')
        ->toContain('Math.min(1, maxPhotoDimension / Math.max(video.videoWidth, video.videoHeight))')
        ->toContain('Math.round(video.videoWidth * photoScale)')
        ->toContain('Math.round(video.videoHeight * photoScale)')
        ->not->toContain('canvas.width = video.videoWidth;')
        ->not->toContain('canvas.height = video.videoHeight;');
})->with([
    'check-in camera' => ['selfie'],
    'check-out camera' => ['checkout'],
    'legacy attendance camera' => ['create'],
]);

it('redirects guests away from teacher attendance pages', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with([
    'dashboard' => ['attendance.dashboard'],
    'check-in' => ['attendance.selfie'],
    'check-out' => ['attendance.checkout'],
    'history' => ['attendance.index'],
    'profile' => ['attendance.profile'],
    'password' => ['attendance.password'],
    'leaves' => ['attendance.leaves.index'],
]);
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php`
Expected: FAIL. Test shell menemukan `data-attendance-ui="material-3"` (layout lama), bukan `data-teacher-ui="absenku-guru"`.

- [ ] **Step 3: Tulis ulang `resources/views/components/layouts/mobile.blade.php`**

```blade
@props([
    'title' => 'Absensi',
    'backUrl' => null,
    'activeTab' => null,
    'showNav' => true,
    // Diterima demi kompatibilitas pemanggil lama; shell baru tidak punya mode sheet.
    'isSheet' => false,
])

@php
    $branding = \App\Models\ApplicationSetting::current();
    $showNav = filter_var($showNav, FILTER_VALIDATE_BOOL);
    $tabs = [
        ['key' => 'beranda', 'label' => 'Beranda', 'icon' => 'home', 'route' => 'attendance.dashboard'],
        ['key' => 'riwayat', 'label' => 'Riwayat', 'icon' => 'calendar', 'route' => 'attendance.index'],
        ['key' => 'izin', 'label' => 'Izin', 'icon' => 'doc', 'route' => 'attendance.leaves.index'],
    ];
    if ($showNav && auth()->user()?->activeHomeroomAssignment()) {
        $tabs[] = ['key' => 'kelas', 'label' => 'Kelas', 'icon' => 'users', 'route' => 'attendance.my-class.index'];
    }
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#F4F1EA" data-guru-theme-color>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="absenKU — absensi guru dengan selfie dan GPS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="absenKU">
    <link rel="manifest" href="{{ route('manifest') }}">
    <link rel="icon" href="{{ $branding->iconUrl() }}">
    <link rel="apple-touch-icon" href="{{ $branding->iconUrl() }}">
    <title>{{ $title }} · absenKU</title>

    <script>
        // Key tema bersama admin & auth: 'light' | 'dark'; kosong = ikut OS. 'welcome-theme' = key lama.
        window.guruThemeMode = function () {
            try {
                return localStorage.getItem('appearance') ?? localStorage.getItem('welcome-theme') ?? 'system';
            } catch (e) {
                return 'system';
            }
        };
        window.guruApplyTheme = function (mode) {
            const dark = mode === 'dark' || (mode !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.querySelector('meta[data-guru-theme-color]')?.setAttribute('content', dark ? '#111512' : '#F4F1EA');
        };
        window.guruSetTheme = function (mode) {
            try {
                if (mode === 'system') {
                    localStorage.removeItem('appearance');
                    localStorage.removeItem('welcome-theme');
                } else {
                    localStorage.setItem('appearance', mode);
                }
            } catch (e) {}
            window.guruApplyTheme(mode);
        };
        window.guruApplyTheme(window.guruThemeMode());
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => window.guruApplyTheme(window.guruThemeMode()));
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @view-transition { navigation: auto; }
    </style>
</head>

<body class="guru" data-teacher-ui="absenku-guru">
    <div class="g-app">
        @isset($header)
            {{ $header }}
        @else
            <header class="g-header" data-region="top-app-bar">
                <a href="{{ $backUrl ?? route('attendance.dashboard') }}" class="g-iconbtn" aria-label="{{ $backUrl ? 'Kembali' : 'Ke beranda' }}">
                    <x-guru.icon :name="$backUrl ? 'back' : 'home'" />
                </a>
                <h1 class="g-header__title">{{ $title }}</h1>
                @isset($headerAction)
                    <div class="g-header__actions">{{ $headerAction }}</div>
                @endisset
            </header>
        @endisset

        <main id="konten" class="g-main{{ $showNav ? '' : ' g-main--bare' }}" data-region="content">
            {{ $slot }}
        </main>

        @if ($showNav)
            <nav class="g-nav" aria-label="Navigasi utama" data-region="navigation-bar">
                @foreach ($tabs as $tab)
                    <a href="{{ route($tab['route']) }}" class="g-nav__item"@if ($activeTab === $tab['key']) aria-current="page"@endif>
                        <span class="g-nav__pill"><x-guru.icon :name="$tab['icon']" :size="22" /></span>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </nav>
        @endif
    </div>

    {{ $scripts ?? '' }}

    @include('partials.pwa-update')
</body>

</html>
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `php artisan test tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php`
Expected: PASS. Isi halaman di dalam shell masih markup lama (tanpa gaya) sampai task masing-masing; itu disengaja.

- [ ] **Step 5: Jalankan seluruh suite**

Run: `php artisan test 2>&1 | tail -5`
Expected: PASS. Dashboard masih memakai layout lamanya sendiri sampai Task 5. Kalau ada test lain yang gagal karena markup shell lama, catat nama test beserta assertion-nya dan perbaiki di task yang memiliki halaman itu (daftar di File Structure). Jangan melonggarkan assertion fungsional.

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/layouts/mobile.blade.php tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php
git commit -m "feat(guru): shell baru dan nav Beranda/Riwayat/Izin/Kelas

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 4: Angka rekap Beranda dan presenter `TodayPresence`

**Files:**
- Modify: `app/Services/EmployeeDashboardData.php`
- Modify: `app/Services/EmployeeDashboardService.php`
- Create: `app/Services/TodayPresence.php`
- Test: `tests/Feature/Employee/DashboardDataTest.php`, `tests/Feature/Employee/TodayPresenceTest.php`

**Interfaces:**
- Produces:
  - `EmployeeDashboardData`: properti baru `int $monthlyLeaveDays = 0`, `int $monthlyWorkDays = 0`, `int $pendingLeaves = 0`, `int $unreadNotifications = 0`, serta method `monthlyOnTime(): int` dan `monthlyRecorded(): int`. Konstruktor lama tetap valid karena semua parameter baru punya default.
  - `TodayPresence::for(EmployeeDashboardData $data, WorkSetting $settings, Carbon $now): TodayPresence`, dengan properti publik:
    - `string $status`: `'Tepat waktu'|'Terlambat'|'Belum absen'|'Absen ditutup'|'Libur'`
    - `bool $late`
    - `?string $checkIn`, `?string $checkOut`: format `H.i`
    - `?string $scheduleStart`, `?string $scheduleEnd`
    - `int $progress`: 0–100
    - `string $progressText`
    - `string $locationText`
    - `?array $action`: `{label: string, href: ?string, icon: string, disabled: bool}`

- [ ] **Step 1: Tulis test data yang gagal**

```php
<?php

// tests/Feature/Employee/DashboardDataTest.php

use App\Models\AcademicYear;
use App\Models\Leave;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\EmployeeDashboardData;
use App\Services\EmployeeDashboardService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

beforeEach(fn () => Carbon::setTestNow(Carbon::parse('2026-07-20 09:00:00'))); // Senin
afterEach(fn () => Carbon::setTestNow());

function dataTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

function dataLeave(User $user, string $from, string $to, string $status): Leave
{
    return Leave::create(['user_id' => $user->id, 'type' => 'izin', 'start_date' => $from, 'end_date' => $to, 'reason' => 'Keperluan keluarga', 'status' => $status]);
}

test('approved leave days count distinct dates this month up to today', function () {
    $user = dataTeacher();
    dataLeave($user, '2026-06-29', '2026-07-02', 'approved'); // 1–2 Juli
    dataLeave($user, '2026-07-02', '2026-07-03', 'approved'); // 2 Juli tumpang tindih, +3 Juli
    dataLeave($user, '2026-07-19', '2026-07-25', 'approved'); // 19–20 Juli (sampai hari ini)
    dataLeave($user, '2026-07-10', '2026-07-10', 'pending');
    dataLeave($user, '2026-07-11', '2026-07-11', 'rejected');

    $data = app(EmployeeDashboardService::class)->for($user);

    expect($data->monthlyLeaveDays)->toBe(5)
        ->and($data->pendingLeaves)->toBe(1);
});

test('work days count scheduled weekdays this month up to today', function () {
    $user = dataTeacher();
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat'] as $day) {
        WorkSchedule::create(['user_id' => $user->id, 'academic_year_id' => $year->id, 'day' => $day, 'check_in_time' => '07:00:00', 'check_out_time' => '14:00:00', 'is_active' => true]);
    }
    WorkSchedule::create(['user_id' => $user->id, 'academic_year_id' => $year->id, 'day' => 'sabtu', 'check_in_time' => '07:00:00', 'check_out_time' => '12:00:00', 'is_active' => false]);

    // Juli 2026 dimulai Rabu: 1–3, 6–10, 13–17, dan 20 = 14 hari kerja.
    expect(app(EmployeeDashboardService::class)->for($user)->monthlyWorkDays)->toBe(14);
});

test('work days are zero without an active academic year', function () {
    expect(app(EmployeeDashboardService::class)->for(dataTeacher())->monthlyWorkDays)->toBe(0);
});

test('unread notifications only count student referral notifications', function () {
    $user = dataTeacher();
    $make = fn (string $type, ?string $readAt) => $user->notifications()->create([
        'id' => (string) Str::uuid(), 'type' => $type, 'data' => ['ok' => true], 'read_at' => $readAt,
    ]);
    $make('App\Notifications\StudentReferralCreated', null);
    $make('App\Notifications\StudentReferralStatusChanged', null);
    $make('App\Notifications\StudentReferralCreated', '2026-07-19 10:00:00');
    $make('App\Notifications\SomethingElse', null);

    expect(app(EmployeeDashboardService::class)->for($user)->unreadNotifications)->toBe(2);
});

test('on-time and recorded figures derive from the monthly counts', function () {
    $data = new EmployeeDashboardData(
        todayAttendance: null, todaySchedule: null, checkoutOpensAt: now(), checkoutTimeReached: false,
        monthlyPresent: 12, monthlyLate: 2, announcements: new Collection,
        monthlyLeaveDays: 3, monthlyWorkDays: 14,
    );

    expect($data->monthlyOnTime())->toBe(10)
        ->and($data->monthlyRecorded())->toBe(14);
});
```

- [ ] **Step 2: Tulis test presenter yang gagal**

```php
<?php

// tests/Feature/Employee/TodayPresenceTest.php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use App\Services\EmployeeDashboardData;
use App\Services\TodayPresence;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

afterEach(fn () => Carbon::setTestNow());

function presence(string $now, ?Attendance $attendance = null, bool $schedule = true, bool $checkoutOpen = false): TodayPresence
{
    Carbon::setTestNow(Carbon::parse($now));

    $data = new EmployeeDashboardData(
        todayAttendance: $attendance,
        todaySchedule: $schedule ? new WorkSchedule(['check_in_time' => '07:00:00', 'check_out_time' => '14:00:00']) : null,
        checkoutOpensAt: Carbon::parse('2026-07-20 13:30:00'),
        checkoutTimeReached: $checkoutOpen,
        monthlyPresent: 0,
        monthlyLate: 0,
        announcements: new Collection,
    );

    $settings = new WorkSetting(['before_check_in' => 60, 'after_check_in' => 10, 'late_limit' => 120, 'before_check_out' => 30]);

    return TodayPresence::for($data, $settings, now());
}

function checkIn(string $at, AttendanceStatus $status = AttendanceStatus::Present, ?string $out = null, ?bool $liveness = null): Attendance
{
    $attendance = new Attendance(['status' => $status, 'distance_meters' => 38, 'check_out_at' => $out, 'liveness_verified' => $liveness]);
    $attendance->created_at = Carbon::parse($at);

    return $attendance;
}

test('before the check-in window opens', function () {
    $p = presence('2026-07-20 05:30:00');

    expect($p->status)->toBe('Belum absen')
        ->and($p->progressText)->toBe('Absen masuk dibuka 06.00')
        ->and($p->checkIn)->toBeNull()
        ->and($p->scheduleStart)->toBe('07.00')
        ->and($p->scheduleEnd)->toBe('14.00')
        ->and($p->action['label'])->toBe('Absen Masuk')
        ->and($p->action['href'])->toBe(route('attendance.selfie'))
        ->and($p->locationText)->toBe('Lokasi dicek saat absen');
});

test('inside the on-time and late windows', function () {
    expect(presence('2026-07-20 07:05:00')->progressText)->toBe('Tepat waktu s.d. 07.10')
        ->and(presence('2026-07-20 07:30:00')->progressText)->toBe('Terlambat · tutup 09.00');
});

test('after check-in closes without attendance there is no check-in action', function () {
    $p = presence('2026-07-20 09:30:00');

    expect($p->status)->toBe('Absen ditutup')->and($p->action)->toBeNull();
});

test('checked in and waiting for the check-out window', function () {
    $p = presence('2026-07-20 09:48:00', checkIn('2026-07-20 06:52:00'));

    expect($p->status)->toBe('Tepat waktu')
        ->and($p->late)->toBeFalse()
        ->and($p->checkIn)->toBe('06.52')
        ->and($p->checkOut)->toBeNull()
        ->and($p->progress)->toBe(40)
        ->and($p->progressText)->toBe('Pulang dalam 3 j 42 m')
        ->and($p->action)->toBe(['label' => 'Pulang dibuka 13.30', 'href' => null, 'icon' => 'clock', 'disabled' => true])
        ->and($p->locationText)->toBe('Dalam area sekolah · 38 m dari titik absen');
});

test('check-out window open offers Absen Pulang', function () {
    $p = presence('2026-07-20 13:40:00', checkIn('2026-07-20 06:52:00'), checkoutOpen: true);

    expect($p->progressText)->toBe('Absen pulang sudah dibuka')
        ->and($p->action['label'])->toBe('Absen Pulang')
        ->and($p->action['href'])->toBe(route('attendance.checkout'));
});

test('checked out links to the history', function () {
    $p = presence('2026-07-20 14:10:00', checkIn('2026-07-20 06:52:00', out: '2026-07-20 13:45:00'), checkoutOpen: true);

    expect($p->checkOut)->toBe('13.45')
        ->and($p->progress)->toBe(100)
        ->and($p->progressText)->toBe('Selesai hari ini')
        ->and($p->action['label'])->toBe('Lihat riwayat');
});

test('a day without a schedule is a holiday without actions', function () {
    $p = presence('2026-07-20 08:00:00', schedule: false);

    expect($p->status)->toBe('Libur')->and($p->action)->toBeNull()->and($p->scheduleStart)->toBeNull();
});

test('late check-in with a manual photo is flagged', function () {
    $p = presence('2026-07-20 09:00:00', checkIn('2026-07-20 07:25:00', AttendanceStatus::Late, liveness: false));

    expect($p->status)->toBe('Terlambat')
        ->and($p->late)->toBeTrue()
        ->and($p->locationText)->toContain('foto manual, menunggu pemeriksaan');
});
```

- [ ] **Step 3: Jalankan kedua test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/DashboardDataTest.php tests/Feature/Employee/TodayPresenceTest.php`
Expected: FAIL (`Unknown named parameter $monthlyLeaveDays`, `Class "App\Services\TodayPresence" not found`).

- [ ] **Step 4: Perluas `app/Services/EmployeeDashboardData.php`**

Ganti konstruktor dan method `monthlyTotal()` dengan:

```php
    /**
     * @param  Collection<int, Announcement>  $announcements
     */
    public function __construct(
        public ?Attendance $todayAttendance,
        public ?WorkSchedule $todaySchedule,
        public Carbon $checkoutOpensAt,
        public bool $checkoutTimeReached,
        public int $monthlyPresent,
        public int $monthlyLate,
        public Collection $announcements,
        public int $monthlyLeaveDays = 0,
        public int $monthlyWorkDays = 0,
        public int $pendingLeaves = 0,
        public int $unreadNotifications = 0,
    ) {}

    /**
     * Days attended this month. "Hadir" counts on-time and late alike, so the
     * total matches the present figure.
     */
    public function monthlyTotal(): int
    {
        return $this->monthlyPresent;
    }

    public function monthlyOnTime(): int
    {
        return max(0, $this->monthlyPresent - $this->monthlyLate);
    }

    /**
     * Work days this month that have either an attendance or an approved leave.
     */
    public function monthlyRecorded(): int
    {
        return min($this->monthlyPresent + $this->monthlyLeaveDays, $this->monthlyWorkDays);
    }
```

- [ ] **Step 5: Perluas `app/Services/EmployeeDashboardService.php`**

Tambahkan import `use App\Models\AcademicYear;` dan `use App\Models\Leave;`. Di `return new EmployeeDashboardData(...)`, setelah argumen `announcements: ...`, tambahkan:

```php
            monthlyLeaveDays: $this->approvedLeaveDaysThisMonth($user),
            monthlyWorkDays: $this->scheduledDaysThisMonth($user),
            pendingLeaves: Leave::query()->where('user_id', $user->id)->pending()->count(),
            unreadNotifications: $user->unreadNotifications()->where('type', 'like', '%StudentReferral%')->count(),
```

Lalu tambahkan dua method privat di akhir kelas:

```php
    /**
     * Distinct dates this month, up to today, covered by the user's approved leaves.
     */
    private function approvedLeaveDaysThisMonth(User $user): int
    {
        $from = now()->startOfMonth();
        $to = now()->startOfDay();
        $dates = [];

        Leave::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->get(['start_date', 'end_date'])
            ->each(function (Leave $leave) use ($from, $to, &$dates): void {
                $day = $leave->start_date->copy()->max($from)->copy()->startOfDay();
                $last = $leave->end_date->copy()->min($to)->copy();

                for (; $day->lte($last); $day->addDay()) {
                    $dates[$day->toDateString()] = true;
                }
            });

        return count($dates);
    }

    /**
     * Dates this month, up to today, whose weekday has an active schedule in the
     * active academic year.
     */
    private function scheduledDaysThisMonth(User $user): int
    {
        $yearId = AcademicYear::getActive()?->id;

        if ($yearId === null) {
            return 0;
        }

        $days = WorkSchedule::query()
            ->where('user_id', $user->id)
            ->where('academic_year_id', $yearId)
            ->where('is_active', true)
            ->pluck('day')
            ->all();

        if ($days === []) {
            return 0;
        }

        $count = 0;

        for ($day = now()->startOfMonth(); $day->lte(now()->startOfDay()); $day->addDay()) {
            if (in_array(strtolower($day->locale('id')->dayName), $days, true)) {
                $count++;
            }
        }

        return $count;
    }
```

- [ ] **Step 6: Buat `app/Services/TodayPresence.php`**

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\WorkSetting;
use Carbon\Carbon;

/**
 * State of the "Presensi hari ini" hero card on the teacher beranda.
 */
final readonly class TodayPresence
{
    /**
     * @param  array{label: string, href: ?string, icon: string, disabled: bool}|null  $action
     */
    public function __construct(
        public string $status,
        public bool $late,
        public ?string $checkIn,
        public ?string $checkOut,
        public ?string $scheduleStart,
        public ?string $scheduleEnd,
        public int $progress,
        public string $progressText,
        public string $locationText,
        public ?array $action,
    ) {}

    public static function for(EmployeeDashboardData $data, WorkSetting $settings, Carbon $now): self
    {
        $attendance = $data->todayAttendance;
        $schedule = $data->todaySchedule;
        $late = $attendance?->status === AttendanceStatus::Late;

        if ($schedule === null) {
            return new self(
                status: $attendance ? ($late ? 'Terlambat' : 'Tepat waktu') : 'Libur',
                late: $late,
                checkIn: $attendance?->created_at?->format('H.i'),
                checkOut: $attendance?->check_out_at?->format('H.i'),
                scheduleStart: null,
                scheduleEnd: null,
                progress: 0,
                progressText: 'Tidak ada jadwal kerja hari ini',
                locationText: self::location($attendance),
                action: null,
            );
        }

        $start = Carbon::parse($schedule->check_in_time)->setDateFrom($now);
        $end = Carbon::parse($schedule->check_out_time)->setDateFrom($now);
        $span = max(1, $end->getTimestamp() - $start->getTimestamp());
        $progress = (int) round(min(100, max(0, ($now->getTimestamp() - $start->getTimestamp()) / $span * 100)));

        if ($attendance === null) {
            $opens = $start->copy()->subMinutes((int) $settings->before_check_in);
            $lateAfter = $start->copy()->addMinutes((int) $settings->after_check_in);
            $closes = $start->copy()->addMinutes((int) $settings->late_limit);

            [$status, $text] = match (true) {
                $now->lt($opens) => ['Belum absen', 'Absen masuk dibuka '.$opens->format('H.i')],
                $now->lte($lateAfter) => ['Belum absen', 'Tepat waktu s.d. '.$lateAfter->format('H.i')],
                $now->lte($closes) => ['Belum absen', 'Terlambat · tutup '.$closes->format('H.i')],
                default => ['Absen ditutup', 'Absen masuk ditutup '.$closes->format('H.i')],
            };

            return new self(
                status: $status,
                late: false,
                checkIn: null,
                checkOut: null,
                scheduleStart: $start->format('H.i'),
                scheduleEnd: $end->format('H.i'),
                progress: $progress,
                progressText: $text,
                locationText: 'Lokasi dicek saat absen',
                action: $now->gt($closes)
                    ? null
                    : ['label' => 'Absen Masuk', 'href' => route('attendance.selfie'), 'icon' => 'login', 'disabled' => false],
            );
        }

        $status = $late ? 'Terlambat' : 'Tepat waktu';
        $checkIn = $attendance->created_at?->format('H.i');

        if ($attendance->check_out_at !== null) {
            return new self(
                status: $status,
                late: $late,
                checkIn: $checkIn,
                checkOut: $attendance->check_out_at->format('H.i'),
                scheduleStart: $start->format('H.i'),
                scheduleEnd: $end->format('H.i'),
                progress: 100,
                progressText: 'Selesai hari ini',
                locationText: self::location($attendance),
                action: ['label' => 'Lihat riwayat', 'href' => route('attendance.index'), 'icon' => 'calendar', 'disabled' => false],
            );
        }

        if ($data->checkoutTimeReached) {
            return new self(
                status: $status,
                late: $late,
                checkIn: $checkIn,
                checkOut: null,
                scheduleStart: $start->format('H.i'),
                scheduleEnd: $end->format('H.i'),
                progress: $progress,
                progressText: 'Absen pulang sudah dibuka',
                locationText: self::location($attendance),
                action: ['label' => 'Absen Pulang', 'href' => route('attendance.checkout'), 'icon' => 'logout', 'disabled' => false],
            );
        }

        $minutes = (int) ceil(max(0, $data->checkoutOpensAt->getTimestamp() - $now->getTimestamp()) / 60);

        return new self(
            status: $status,
            late: $late,
            checkIn: $checkIn,
            checkOut: null,
            scheduleStart: $start->format('H.i'),
            scheduleEnd: $end->format('H.i'),
            progress: $progress,
            progressText: 'Pulang dalam '.self::duration($minutes),
            locationText: self::location($attendance),
            action: ['label' => 'Pulang dibuka '.$data->checkoutOpensAt->format('H.i'), 'href' => null, 'icon' => 'clock', 'disabled' => true],
        );
    }

    private static function duration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $hours > 0 ? "{$hours} j {$rest} m" : "{$rest} m";
    }

    private static function location(?Attendance $attendance): string
    {
        if ($attendance === null) {
            return 'Lokasi dicek saat absen';
        }

        $text = 'Dalam area sekolah · '.number_format((float) $attendance->distance_meters, 0, ',', '.').' m dari titik absen';

        return $attendance->liveness_verified === false ? $text.' · foto manual, menunggu pemeriksaan' : $text;
    }
}
```

- [ ] **Step 7: Jalankan test, pastikan lulus**

Run: `php artisan test tests/Feature/Employee/DashboardDataTest.php tests/Feature/Employee/TodayPresenceTest.php tests/Feature/Api`
Expected: PASS. Test API memastikan resource dashboard mobile tidak rusak.

- [ ] **Step 8: Pint, PHPStan, commit**

```bash
./vendor/bin/pint --dirty
./vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw | grep -c .   # harus 132
git add app/Services tests/Feature/Employee/DashboardDataTest.php tests/Feature/Employee/TodayPresenceTest.php
git commit -m "feat(guru): rekap izin/hari kerja dan presenter kartu presensi

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 5: Beranda

**Files:**
- Modify: `app/Http/Controllers/Employee/DashboardController.php`
- Modify (tulis ulang): `resources/views/attendance/dashboard.blade.php`
- Delete: `resources/views/partials/pwa-material3.blade.php`
- Delete: `tests/Feature/Employee/StatusBannerAutoHideTest.php`
- Modify: `tests/Feature/Employee/CheckoutTimeWindowTest.php` (tiga test dashboard di baris 99–144), `tests/Feature/Feature/BkAccessTest.php` (baris 37–43)
- Modify: `docs/superpowers/specs/2026-09-27-redesign-pwa-guru-design.md` (§4.2, cara label diperbarui)
- Test: `tests/Feature/Employee/BerandaTest.php`

**Interfaces:**
- Consumes: `EmployeeDashboardData` beserta method barunya, `TodayPresence` (Task 4); komponen dari Task 2; slot `header` dan `scripts` di layout (Task 3).
- Produces: view `attendance.dashboard` dengan variabel `data`, `presence`, `linkedAccounts`, `homeroomAssignment`, `homeroomStudentCount`, `homeroomViolationCount`. Kartu hero diberi atribut `data-hero="today"`.

- [ ] **Step 1: Tulis test Beranda yang gagal**

```php
<?php

// tests/Feature/Employee/BerandaTest.php

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\HomeroomAssignment;
use App\Models\Leave;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use Carbon\Carbon;
use Illuminate\Support\Str;

afterEach(fn () => Carbon::setTestNow());

function berandaTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id, 'name' => 'Siti Rahmawati']);
}

function berandaSchedule(User $user): void
{
    $year = AcademicYear::firstOrCreate(['name' => '2026/2027'], ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    WorkSchedule::create(['user_id' => $user->id, 'academic_year_id' => $year->id, 'day' => 'senin', 'check_in_time' => '07:00:00', 'check_out_time' => '14:00:00', 'is_active' => true]);
    WorkSetting::current()->update(['before_check_in' => 60, 'after_check_in' => 10, 'late_limit' => 120, 'before_check_out' => 30]);
}

function berandaCheckIn(User $user, string $at, AttendanceStatus $status = AttendanceStatus::Present): Attendance
{
    $attendance = Attendance::create(['user_id' => $user->id, 'status' => $status, 'image_path' => 'x.jpg', 'check_in_lat' => -6.2, 'check_in_long' => 106.8, 'distance_meters' => 38]);
    $attendance->created_at = Carbon::parse($at);
    $attendance->save();

    return $attendance;
}

test('beranda renders the header, date line and hero inside the guru shell', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 07:05:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSuccessful()
        ->assertSee('<body class="guru" data-teacher-ui="absenku-guru">', false)
        ->assertSee('data-profile-link="teacher-identity"', false)
        ->assertSee('aria-label="Buka profil Siti Rahmawati"', false)
        ->assertSee('Selamat pagi,')
        ->assertSee('Senin, 20 Juli')
        ->assertSee('data-hero="today"', false)
        ->assertSee('Tepat waktu s.d. 07.10')
        ->assertSee('href="'.route('attendance.selfie').'"', false)
        ->assertSee('Absen Masuk');
});

test('after check-in the hero waits for the check-out window', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 09:48:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);
    berandaCheckIn($teacher, '2026-07-20 06:52:00');

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('06.52')
        ->assertSee('aria-disabled="true"', false)
        ->assertSee('Pulang dibuka 13.30')
        ->assertSee('38 m dari titik absen')
        ->assertDontSee('href="'.route('attendance.checkout').'"', false);
});

test('the check-out window opens Absen Pulang', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 13:40:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);
    berandaCheckIn($teacher, '2026-07-20 06:52:00');

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('href="'.route('attendance.checkout').'"', false)
        ->assertSee('Absen Pulang');
});

test('a late check-in shows the late chip', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 09:00:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);
    berandaCheckIn($teacher, '2026-07-20 07:25:00', AttendanceStatus::Late);

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('g-chip g-chip--hero-late', false)
        ->assertSee('Terlambat');
});

test('a day without a schedule shows Libur and no attendance action', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 08:00:00'));

    $this->actingAs(berandaTeacher())->get(route('attendance.dashboard'))
        ->assertSuccessful()
        ->assertSee('Libur')
        ->assertSee('Belum ada hari kerja bulan ini')
        ->assertDontSee('href="'.route('attendance.selfie').'"', false);
});

test('rekap shows recorded work days', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);
    berandaCheckIn($teacher, '2026-07-13 06:55:00');
    berandaCheckIn($teacher, '2026-07-20 06:55:00');

    // Hanya Senin terjadwal: 6, 13, 20 Juli = 3 hari kerja; 2 hadir tercatat.
    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('Rekap Juli')
        ->assertSee('2 dari 3 hari kerja tercatat');
});

test('pending leaves and unread referral notifications surface on beranda', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = berandaTeacher();
    Leave::create(['user_id' => $teacher->id, 'type' => 'sakit', 'start_date' => '2026-07-21', 'end_date' => '2026-07-21', 'reason' => 'Demam', 'status' => 'pending']);
    $teacher->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'App\Notifications\StudentReferralCreated', 'data' => ['ok' => true]]);

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('1 menunggu')
        ->assertSee('aria-label="Notifikasi, 1 belum dibaca"', false)
        ->assertSee('g-iconbtn__dot', false);
});

test('homeroom teachers see their class card and the Kelas tab', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = berandaTeacher();
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $class = SchoolClass::create(['school_level' => 'mi', 'name' => 'VIII-A', 'normalized_name' => SchoolClass::normalizeName('VIII-A'), 'grade_level' => 8, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    $html = $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('Kelas wali · 2026/2027')
        ->assertSee('VIII-A')
        ->assertSee('Rujukan Saya')
        ->getContent();

    expect(substr_count($html, 'class="g-nav__item"'))->toBe(4);
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/BerandaTest.php`
Expected: FAIL (dashboard masih layout lama; tidak ada `data-teacher-ui`).

- [ ] **Step 3: Ubah `DashboardController::index`**

Tambahkan `use App\Models\WorkSetting;` dan `use App\Services\TodayPresence;`, lalu ganti `return view(...)` menjadi:

```php
        return view('attendance.dashboard', [
            'data' => $data,
            'presence' => TodayPresence::for($data, WorkSetting::current(), now()),
            'linkedAccounts' => $user->linkedAccounts()->with('office')->orderBy('name')->get(),
            'homeroomAssignment' => $homeroomAssignment,
            'homeroomStudentCount' => $homeroomAssignment?->schoolClass->students()->where('status', 'Aktif')->count() ?? 0,
            'homeroomViolationCount' => $homeroomAssignment?->schoolClass->students()->whereHas('bkRecords', fn ($query) => $query->where('record_type', 'violation')->whereNull('archived_at'))->count() ?? 0,
        ]);
```

- [ ] **Step 4: Tulis ulang `resources/views/attendance/dashboard.blade.php`**

```blade
@php
    $user = auth()->user();
    $hour = (int) now()->format('G');
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi,',
        $hour < 15 => 'Selamat siang,',
        $hour < 18 => 'Selamat sore,',
        default => 'Selamat malam,',
    };
    $workDays = $data->monthlyWorkDays;
    $recorded = $data->monthlyRecorded();
    $bar = [
        ['is-ok', $data->monthlyOnTime()],
        ['is-late', $data->monthlyLate],
        ['is-izin', $data->monthlyLeaveDays],
        ['is-rest', max(0, $workDays - $recorded)],
    ];
    $isHeadmaster = $user->role?->slug === 'kepala-sekolah';
    $canBk = $user->canAccessBk() && ! $user->isAdmin();
    $isAffairs = $user->is_student_affairs_officer && in_array($user->office?->school_level, ['mi', 'smp'], true);
@endphp

<x-layouts.mobile title="Beranda" activeTab="beranda">
    <x-slot:header>
        <header class="g-header" data-region="top-app-bar">
            <a href="{{ route('attendance.profile') }}" class="g-greet" data-profile-link="teacher-identity" aria-label="Buka profil {{ $user->name }}">
                <span class="g-avatar">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="">
                    @else
                        {{ $user->initials() }}
                    @endif
                </span>
                <span>
                    <small>{{ $greeting }}</small>
                    <strong>{{ $user->name }}</strong>
                </span>
            </a>

            <div class="g-header__actions">
                @if ($linkedAccounts->isNotEmpty())
                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button" class="g-iconbtn" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="menu" aria-controls="menu-ganti-akun" aria-label="Ganti Akun">
                            <x-guru.icon name="swap" />
                        </button>
                        <div id="menu-ganti-akun" class="g-menu" role="menu" x-show="open" x-cloak @click.outside="open = false">
                            <p class="g-caps px-2.5 pb-1 pt-2">Ganti Akun</p>
                            @foreach ($linkedAccounts as $account)
                                <button type="button" role="menuitem" class="g-menu__item"
                                    @click="open = false; $refs.switchName.textContent = @js($account->name); $refs.switchTarget.value = @js($account->id); $refs.switchDialog.showModal()">
                                    <span class="g-avatar g-avatar--sm">{{ $account->initials() }}</span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-bold">{{ $account->name }}</span>
                                        <span class="block truncate text-xs g-muted">{{ $account->office?->name ?? 'Tanpa kantor' }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <dialog x-ref="switchDialog" class="g-dialog" aria-labelledby="judul-ganti-akun">
                            <h2 id="judul-ganti-akun" class="g-display text-2xl font-semibold">Ganti akun?</h2>
                            <p class="mt-2 g-muted">Berpindah ke akun <strong class="text-guru-ink" x-ref="switchName"></strong>. Sesi akun ini akan diganti.</p>
                            <form method="POST" action="{{ route('account.switch') }}" class="mt-5 grid grid-cols-2 gap-2">
                                @csrf
                                <input type="hidden" name="target_id" x-ref="switchTarget">
                                <button type="button" class="g-btn g-btn--secondary" @click="$refs.switchDialog.close()">Batal</button>
                                <button type="submit" class="g-btn g-btn--primary">Ganti</button>
                            </form>
                        </dialog>
                    </div>
                @endif

                <a href="{{ route('attendance.kesiswaan.notifications.index') }}" class="g-iconbtn" aria-label="Notifikasi, {{ $data->unreadNotifications }} belum dibaca">
                    <x-guru.icon name="bell" />
                    @if ($data->unreadNotifications > 0)
                        <span class="g-iconbtn__dot"></span>
                    @endif
                </a>
            </div>
        </header>
    </x-slot:header>

    <div class="g-dateline">
        <h1>{{ now()->locale('id')->isoFormat('dddd, D MMMM') }}</h1>
        <p>{{ $user->office?->name ?? 'Sekolah' }}</p>
    </div>

    {{-- Kartu hero: status dan satu aksi yang relevan sekarang --}}
    <x-guru.card variant="hero" aria-label="Presensi hari ini" data-hero="today">
        <div class="flex items-center justify-between gap-3">
            <span class="g-hero__label">Presensi hari ini</span>
            <x-guru.chip :tone="$presence->late ? 'hero-late' : 'hero'">{{ $presence->status }}</x-guru.chip>
        </div>

        <div class="g-hero__times">
            <div class="g-hero__time">
                <span class="g-hero__time-label">Masuk</span>
                <span @class(['g-hero__time-value', 'is-empty' => ! $presence->checkIn])>{{ $presence->checkIn ?? '––.––' }}</span>
            </div>
            <div class="g-hero__time">
                <span class="g-hero__time-label">Pulang</span>
                <span @class(['g-hero__time-value', 'is-empty' => ! $presence->checkOut])>{{ $presence->checkOut ?? '––.––' }}</span>
            </div>
        </div>

        @if ($presence->scheduleStart)
            <div class="flex flex-col gap-2">
                <div class="g-progress" style="--p: {{ $presence->progress }}%" role="progressbar" aria-label="Waktu kerja hari ini"
                    aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $presence->progress }}">
                    <div class="g-progress__fill"></div>
                    <div class="g-progress__knob"></div>
                </div>
                <div class="g-progress__labels">
                    <span>{{ $presence->scheduleStart }}</span>
                    <span>{{ $presence->progressText }}</span>
                    <span>{{ $presence->scheduleEnd }}</span>
                </div>
            </div>
        @else
            <p class="g-hero__loc">{{ $presence->progressText }}</p>
        @endif

        <p class="g-hero__loc"><x-guru.icon name="pin" :size="18" />{{ $presence->locationText }}</p>

        @if ($presence->action)
            @if ($presence->action['disabled'])
                <span class="g-btn g-btn--hero" aria-disabled="true">
                    <x-guru.icon :name="$presence->action['icon']" />{{ $presence->action['label'] }}
                </span>
            @else
                <x-guru.button variant="hero" :href="$presence->action['href']" :icon="$presence->action['icon']">{{ $presence->action['label'] }}</x-guru.button>
            @endif
        @endif
    </x-guru.card>

    {{-- Rekap bulan ini --}}
    <x-guru.card aria-labelledby="judul-rekap">
        <div class="flex items-center justify-between gap-3">
            <h2 id="judul-rekap" class="g-h2">Rekap {{ now()->locale('id')->isoFormat('MMMM') }}</h2>
            <a href="{{ route('attendance.index') }}" class="flex min-h-11 items-center text-[13px] font-bold">Lihat riwayat</a>
        </div>
        <div class="g-stats">
            <x-guru.stat :value="$data->monthlyOnTime()" label="Tepat waktu" tone="primary" />
            <x-guru.stat :value="$data->monthlyLate" label="Terlambat" tone="late" />
            <x-guru.stat :value="$data->monthlyLeaveDays" label="Izin" tone="izin" />
        </div>
        @if ($workDays > 0)
            <div class="g-stackbar" aria-hidden="true">
                @foreach ($bar as [$segment, $count])
                    @if ($count > 0)
                        <span class="{{ $segment }}" style="flex-grow: {{ $count }}"></span>
                    @endif
                @endforeach
            </div>
            <p class="text-xs g-muted">{{ $recorded }} dari {{ $workDays }} hari kerja tercatat</p>
        @else
            <p class="text-xs g-muted">Belum ada hari kerja bulan ini</p>
        @endif
    </x-guru.card>

    {{-- Kelas wali --}}
    @if ($homeroomAssignment)
        <a href="{{ route('attendance.my-class.index') }}" class="g-card">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 flex-col gap-1">
                    <span class="g-card__caps">Kelas wali · {{ $homeroomAssignment->academicYear->name }}</span>
                    <span class="g-card__title">{{ $homeroomAssignment->schoolClass->name }}</span>
                </div>
                <span class="g-list__icon size-11 rounded-[14px]"><x-guru.icon name="users" :size="22" /></span>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-guru.chip>{{ $homeroomStudentCount }} siswa aktif</x-guru.chip>
                @if ($homeroomViolationCount > 0)
                    <x-guru.chip tone="attn">{{ $homeroomViolationCount }} perlu perhatian</x-guru.chip>
                @endif
            </div>
        </a>
    @endif

    {{-- Layanan --}}
    <section aria-labelledby="judul-layanan" class="flex flex-col gap-2.5">
        <h2 id="judul-layanan" class="g-h2">Layanan</h2>
        <x-guru.list>
            @if ($isHeadmaster)
                <x-guru.list-item :href="route('approval.leaves.index')" icon="clipboard" title="Persetujuan Izin" desc="Setujui atau tolak pengajuan guru" />
            @endif
            <x-guru.list-item :href="route('attendance.leaves.index')" icon="doc" title="Pengajuan Izin" desc="Sakit, izin, cuti">
                @if ($data->pendingLeaves > 0)
                    <x-slot:end><x-guru.chip tone="pending">{{ $data->pendingLeaves }} menunggu</x-guru.chip></x-slot:end>
                @endif
            </x-guru.list-item>
            @if ($homeroomAssignment)
                <x-guru.list-item :href="route('attendance.referrals.mine')" icon="send" tone="attn" title="Rujukan Saya" desc="Rujukan siswa ke BK" />
            @endif
            @if ($canBk)
                <x-guru.list-item :href="route('attendance.bk.index')" icon="chat" tone="izin" title="Bimbingan Konseling" desc="Catatan dan tindak lanjut" />
                <x-guru.list-item :href="route('attendance.referrals.queue')" icon="inbox" tone="izin" title="Antrean Rujukan" desc="Rujukan yang menunggu ditangani" />
            @endif
            @if ($isAffairs)
                <x-guru.list-item :href="route('attendance.kesiswaan.index')" icon="school" title="Kesiswaan" desc="Direktori siswa" />
            @endif
            <x-guru.list-item :href="route('attendance.profile')" icon="lock" tone="neutral" title="Profil & Kata Sandi" desc="Foto, data diri, keamanan" />
        </x-guru.list>
    </section>

    {{-- Informasi --}}
    @if ($data->announcements->isNotEmpty())
        <section aria-labelledby="judul-informasi" class="flex flex-col gap-2.5">
            <h2 id="judul-informasi" class="g-h2">Informasi</h2>
            <div class="g-carousel">
                @foreach ($data->announcements as $info)
                    <a href="{{ route('attendance.information.show', $info) }}" class="g-info">
                        <div class="g-info__media">
                            @if ($info->image_url)
                                <img src="{{ $info->image_url }}" alt="" loading="lazy">
                            @endif
                            <span class="g-info__tag">Pengumuman</span>
                        </div>
                        <div class="g-info__body">
                            <span class="text-sm font-bold">{{ $info->title }}</span>
                            <span class="text-xs g-muted">{{ $info->summary ? $info->summary.' · ' : '' }}{{ $info->created_at->locale('id')->diffForHumans() }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Pasang PWA: muncul hanya bila browser menawarkan --}}
    <div id="pwa-install-banner" class="g-install" hidden>
        <x-guru.icon name="phone" :size="22" />
        <p><strong>Pasang absenKU</strong>Buka lebih cepat dari layar utama.</p>
        <button type="button" onclick="installPWA()" class="g-btn g-btn--hero g-btn--sm">Pasang</button>
        <button type="button" onclick="dismissInstallBanner()" class="g-iconbtn border-0 bg-transparent text-white" aria-label="Tutup banner instal">
            <x-guru.icon name="x" />
        </button>
    </div>

    <x-slot:scripts>
        <script>
            (function () {
                let deferredPrompt = null;
                const banner = () => document.getElementById('pwa-install-banner');
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    deferredPrompt = e;
                    let dismissed = false;
                    try { dismissed = localStorage.getItem('pwaInstallDismissed') === 'true'; } catch (err) {}
                    if (! dismissed) banner().hidden = false;
                });
                window.addEventListener('appinstalled', () => { banner().hidden = true; deferredPrompt = null; });
                window.installPWA = async function () {
                    if (! deferredPrompt) return;
                    deferredPrompt.prompt();
                    await deferredPrompt.userChoice;
                    deferredPrompt = null;
                    banner().hidden = true;
                };
                window.dismissInstallBanner = function () {
                    banner().hidden = true;
                    try { localStorage.setItem('pwaInstallDismissed', 'true'); } catch (err) {}
                };

                // Kartu presensi dihitung di server; muat ulang saat PWA dibuka lagi setelah 5 menit.
                const loadedAt = Date.now();
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible' && Date.now() - loadedAt > 5 * 60 * 1000) {
                        location.reload();
                    }
                });
            })();
        </script>
    </x-slot:scripts>
</x-layouts.mobile>
```

- [ ] **Step 5: Hapus skin lama dan test yang mengunci desain lama**

```bash
git rm resources/views/partials/pwa-material3.blade.php tests/Feature/Employee/StatusBannerAutoHideTest.php
grep -rn "pwa-material3" resources tests   # harus kosong
```

- [ ] **Step 6: Sesuaikan test lama**

Di `tests/Feature/Employee/CheckoutTimeWindowTest.php`, hapus komentar (baris 99–101) dan tiga test `dashboard shows the check-out button in its static colour before the window opens`, `… once the window opens`, dan `dashboard shows the check-in button in its static colour after checking in`. Ganti dengan:

```php
// Absen masuk/pulang kini lewat tombol kartu presensi Beranda (lihat BerandaTest),
// bukan tombol berwarna di nav bawah.
test('dashboard navigation no longer carries attendance gate buttons', function () {
    $today = Carbon::parse('2026-07-20');
    $user = checkedInEmployee($today);

    Carbon::setTestNow($today->copy()->setTime(15, 29));

    $this->actingAs($user)
        ->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('data-hero="today"', false)
        ->assertDontSee('nav-fab');
});
```

Di `tests/Feature/Feature/BkAccessTest.php`, ganti isi test `bk dashboard shortcut only appears for enabled counselor` menjadi:

```php
    $regular = bkTeacher(false);
    $bk = bkTeacher(true);
    $link = 'href="'.route('attendance.bk.index').'"';

    $this->actingAs($regular)->get(route('attendance.dashboard'))->assertDontSee($link, false);
    $this->actingAs($bk)->get(route('attendance.dashboard'))->assertSee($link, false)->assertSee('Bimbingan Konseling');
```

- [ ] **Step 7: Perbarui spec §4.2**

Di `docs/superpowers/specs/2026-09-27-redesign-pwa-guru-design.md`, ganti kalimat "Label diperbarui tiap 60 detik lewat Alpine." dengan:

"Label dihitung di server saat halaman dimuat. Beranda memuat ulang dirinya ketika PWA dibuka kembali setelah lebih dari 5 menit (`visibilitychange`), karena itu lebih cocok untuk PWA yang sering di-background daripada timer."

Di §2.3, ganti "Knob progres hanya berpindah saat jam diperbarui (tiap 60 detik), tanpa animasi berkelanjutan." dengan "Knob progres tidak dianimasikan terus-menerus; posisinya dihitung saat halaman dimuat."

- [ ] **Step 8: Jalankan test**

Run: `php artisan test tests/Feature/Employee tests/Feature/Feature/BkAccessTest.php tests/Feature/Kesiswaan tests/Feature/DashboardTest.php`
Expected: PASS. Test yang wajib tetap hijau: `StudentAffairsAccessTest` (`Rujukan Saya`, `>Kesiswaan<`, `Antrean Rujukan`) dan `AccountSwitchTest` (`Ganti Akun`, `Guru SMP`).

- [ ] **Step 9: Build, jalankan seluruh suite, commit**

```bash
npm run build 2>&1 | grep -E "built|rror"
php artisan test 2>&1 | tail -3
git add -A app/Http/Controllers/Employee/DashboardController.php resources/views/attendance/dashboard.blade.php resources/views/partials tests docs/superpowers/specs public/build
git commit -m "feat(guru): Beranda baru sesuai desain AbsenKU Guru

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 6: Absen Masuk dan Absen Pulang

**Files:**
- Create: `resources/views/attendance/partials/absen-form.blade.php`
- Modify: `resources/views/attendance/selfie.blade.php` (baris 1–363, yaitu semua baris sebelum `    <!-- Alpine.js logic controller -->`)
- Modify: `resources/views/attendance/checkout.blade.php` (baris 1–405, semua baris sebelum `    <!-- Alpine.js logic controller -->`)
- Test: `tests/Feature/Employee/AbsenPageTest.php`

**Interfaces:**
- Consumes:
  - State Alpine yang sudah ada di script kedua halaman: `cameraLoading`, `cameraError`, `livenessLoading`, `livenessError`, `photoTaken`, `captureFlash`, `faceDetected`, `manualAllowed`, `manualOnly`, `livenessVerified`, `officeId`, `locationLoading`, `locationFetched`, `locationError`, `latitude`, `longitude`, `accuracy`, `distanceWarning`, `distanceOk`, `currentDistance`, `maxDistance`, `isSubmitting`, `canSubmit`, `statusMessage`.
  - Method Alpine: `startLiveness()`, `useManualCapture()`, `takeManualPhoto()`, `retakePhoto()`, `calculateDistance()`, `fetchLocation()`, `submitForm()`; ref `video`, `canvas`.
  - Komponen Task 2.
- Produces: partial `attendance.partials.absen-form` dengan variabel `$mode` (`'masuk'|'pulang'`) dan `$action`. `$offices` dan `$user` diwarisi dari view induk.

- [ ] **Step 1: Tulis test yang gagal**

```php
<?php

// tests/Feature/Employee/AbsenPageTest.php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

afterEach(fn () => Carbon::setTestNow());

function absenTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create(['name' => 'Kantor MI', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);

    return User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
}

function absenCheckedIn(User $user, ?string $out = null): void
{
    Attendance::create(['user_id' => $user->id, 'status' => AttendanceStatus::Present, 'image_path' => 'x.jpg', 'check_in_lat' => -6.2, 'check_in_long' => 106.8, 'distance_meters' => 12, 'check_out_at' => $out]);
}

test('check-in renders camera, status chips, office and submit', function () {
    $this->actingAs(absenTeacher())->get(route('attendance.selfie'))
        ->assertSuccessful()
        ->assertSee('x-data="attendanceForm()"', false)
        ->assertSee('class="g-camera"', false)
        ->assertSee('aria-live="polite"', false)
        ->assertSee('Kantor tujuan')
        ->assertSee('Terkunci oleh admin')
        ->assertSee('Ambil Foto Manual')
        ->assertSee('Kirim Absen Masuk');
});

test('check-out explains each unavailable state', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = absenTeacher();

    $this->actingAs($teacher)->get(route('attendance.checkout'))
        ->assertSee('Belum absen masuk')
        ->assertSee('href="'.route('attendance.selfie').'"', false);

    absenCheckedIn($teacher);
    $this->actingAs($teacher)->get(route('attendance.checkout'))
        ->assertSee('Belum waktunya pulang')
        ->assertSee('Absen pulang dibuka pukul 15.30');

    Attendance::where('user_id', $teacher->id)->update(['check_out_at' => '2026-07-20 09:55:00']);
    $this->actingAs($teacher)->get(route('attendance.checkout'))
        ->assertSee('Absen pulang tercatat')
        ->assertSee('09.55');
});

test('check-out form shows once the window is open', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 16:00:00'));
    $teacher = absenTeacher();
    absenCheckedIn($teacher);

    $this->actingAs($teacher)->get(route('attendance.checkout'))
        ->assertSee('x-data="checkoutForm()"', false)
        ->assertSee('Kirim Absen Pulang');
});

test('attendance templates keep their Alpine logic and drop the legacy classes', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/{$view}.blade.php"));
    $partial = file_get_contents(resource_path('views/attendance/partials/absen-form.blade.php'));

    expect($template)
        ->toContain('submitErrorMessage(response, data)')
        ->toContain('takeManualPhoto() {')
        ->toContain("formData.append('liveness_verified'")
        ->not->toContain('glass-card')
        ->not->toContain('theme-')
        ->and($partial)->not->toContain('glass-card')->not->toContain('theme-');
})->with(['selfie', 'checkout']);
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/AbsenPageTest.php`
Expected: FAIL (`class="g-camera"` tidak ditemukan).

- [ ] **Step 3: Buat `resources/views/attendance/partials/absen-form.blade.php`**

```blade
{{-- Badan halaman Absen Masuk/Pulang: kamera, status, kantor, lokasi, dan kirim.
     Berada di dalam komponen Alpine attendanceForm()/checkoutForm() milik halaman.
     Butuh $mode ('masuk'|'pulang') dan $action (URL form); $offices dan $user dari view induk. --}}
@php($isPulang = $mode === 'pulang')

<div class="sr-only" role="status" aria-live="polite" x-text="statusMessage"></div>
<div class="sr-only" role="alert" x-text="cameraError || livenessError || locationError || (distanceWarning ? 'Anda berada di luar radius kantor.' : '')"></div>

<section class="g-camera" aria-label="Kamera selfie">
    <video x-ref="video" x-show="!photoTaken" autoplay playsinline class="-scale-x-100"></video>
    {{-- Pratinjau dicerminkan seperti kaca; foto yang disimpan tetap tidak dicerminkan. --}}
    <canvas x-ref="canvas" x-show="photoTaken" class="-scale-x-100"></canvas>

    <div x-show="cameraLoading" class="g-camera__overlay">
        <x-guru.icon name="camera" :size="28" class="animate-pulse" />
        <strong>Menghubungkan kamera…</strong>
    </div>

    <div x-show="cameraError" x-cloak class="g-camera__overlay">
        <x-guru.icon name="alert" :size="28" />
        <strong>Akses kamera gagal</strong>
        <p x-text="cameraError"></p>
    </div>

    <div x-show="livenessLoading && !cameraError && !photoTaken" x-cloak class="g-camera__overlay">
        <x-guru.icon name="eye" :size="28" class="animate-pulse" />
        <strong>Memuat deteksi wajah…</strong>
        <p>Pertama kali bisa agak lama (±15 MB).</p>
    </div>

    <div x-show="livenessError && !photoTaken" x-cloak class="g-camera__overlay">
        <x-guru.icon name="alert" :size="28" />
        <strong>Deteksi wajah gagal dimuat</strong>
        <p x-text="livenessError"></p>
        <div class="mt-2 flex flex-wrap justify-center gap-2">
            <button type="button" @click="startLiveness()" class="g-btn g-btn--hero g-btn--sm">Coba Lagi</button>
            <button type="button" @click="useManualCapture()" class="g-btn g-btn--sm border-white/40 text-white">Foto Manual</button>
        </div>
    </div>

    <div x-show="captureFlash" x-cloak x-transition.opacity.duration.150ms class="g-camera__flash"></div>

    <div x-show="!photoTaken && !cameraLoading && !cameraError" aria-hidden="true">
        <div class="g-camera__scan"></div>
        <div class="g-camera__guide"></div>
        <span class="g-camera__bracket left-4 top-4 rounded-tl-lg border-l-2 border-t-2"></span>
        <span class="g-camera__bracket right-4 top-4 rounded-tr-lg border-r-2 border-t-2"></span>
        <span class="g-camera__bracket bottom-4 left-4 rounded-bl-lg border-b-2 border-l-2"></span>
        <span class="g-camera__bracket bottom-4 right-4 rounded-br-lg border-b-2 border-r-2"></span>
    </div>

    <div x-show="!photoTaken && !cameraLoading && !cameraError && !livenessLoading && !livenessError && !manualOnly" class="g-camera__prompt">
        <x-guru.icon name="eye" :size="20" />
        <span x-text="faceDetected ? 'Kedipkan mata untuk mengambil foto' : 'Arahkan wajah ke dalam bingkai'">Arahkan wajah ke dalam bingkai</span>
    </div>

    <div x-show="photoTaken" x-cloak class="g-camera__badge">
        <x-guru.chip tone="ok"><x-guru.icon name="check" :size="14" /><span x-text="livenessVerified ? 'Foto terkunci' : 'Foto manual'">Foto terkunci</span></x-guru.chip>
    </div>
</section>

<div class="flex flex-col gap-2">
    <div x-show="!photoTaken && !cameraError && !livenessError && !manualAllowed"
        class="flex min-h-14 items-center justify-center gap-2 rounded-2xl border border-dashed border-guru-border px-4 text-sm font-semibold text-guru-muted">
        <x-guru.icon name="eye" :size="18" /> Kedip untuk ambil foto
    </div>
    <button type="button" x-show="!photoTaken && !cameraError && !livenessError && manualAllowed" x-cloak @click="takeManualPhoto()" class="g-btn g-btn--secondary g-btn--block">
        <x-guru.icon name="camera" /> Ambil Foto Manual
    </button>
    <p x-show="manualAllowed && !photoTaken && !cameraError && !livenessError" x-cloak class="text-center text-xs text-guru-muted">
        Kedip tidak terdeteksi? Foto manual tetap bisa dipakai, tapi akan ditandai untuk diperiksa admin.
    </p>
    <button type="button" x-show="photoTaken" x-cloak @click="retakePhoto()" class="g-btn g-btn--secondary g-btn--block">
        <x-guru.icon name="refresh" /> Ulangi Foto
    </button>
</div>

<div class="g-status" aria-hidden="true">
    <span class="g-chip" :class="photoTaken ? 'g-chip--ok' : 'g-chip--neutral'">
        <x-guru.icon name="camera" :size="14" /><span x-text="photoTaken ? 'Wajah siap' : 'Wajah belum'">Wajah belum</span>
    </span>
    <span class="g-chip" :class="locationFetched && distanceOk ? 'g-chip--ok' : ((locationError || distanceWarning) ? 'g-chip--attn' : 'g-chip--neutral')">
        <x-guru.icon name="pin" :size="14" /><span x-text="locationFetched && distanceOk ? 'Lokasi sesuai' : ((locationError || distanceWarning) ? 'Lokasi bermasalah' : 'Mencari lokasi')">Mencari lokasi</span>
    </span>
    <span class="g-chip" :class="officeId ? 'g-chip--ok' : 'g-chip--neutral'">
        <x-guru.icon name="school" :size="14" /><span x-text="officeId ? 'Kantor dipilih' : 'Pilih kantor'">Pilih kantor</span>
    </span>
</div>

<x-guru.card as="div">
    <div class="g-field">
        <label for="office_id" class="g-label flex flex-wrap items-center gap-2">
            Kantor tujuan
            @if ($user->office_id)
                <x-guru.chip tone="ok"><x-guru.icon name="lock" :size="12" />Terkunci oleh admin</x-guru.chip>
            @endif
        </label>
        <select id="office_id" x-model="officeId" @change="calculateDistance()" @if ($user->office_id) disabled @endif class="g-input">
            @unless ($user->office_id)
                <option value="">-- Pilih Lokasi Kerja --</option>
            @endunless
            @foreach ($offices as $office)
                <option value="{{ $office->id }}" data-lat="{{ $office->latitude }}" data-lng="{{ $office->longitude }}" data-radius="{{ $office->radius_meters }}">{{ $office->name }}</option>
            @endforeach
        </select>
    </div>
</x-guru.card>

<x-guru.card as="div">
    <div class="flex items-center gap-3">
        <span class="g-list__icon" :class="locationError ? 'g-list__icon--attn' : 'g-list__icon--primary'"><x-guru.icon name="pin" /></span>
        <div class="min-w-0 flex-1">
            <p class="font-bold" x-text="locationLoading ? 'Mengambil lokasi GPS…' : (locationFetched ? 'Lokasi GPS didapat' : 'Lokasi GPS belum didapat')">Mengambil lokasi GPS…</p>
            <p x-show="locationFetched" x-cloak class="g-num text-xs text-guru-muted" x-text="latitude + ', ' + longitude + (accuracy ? ' · akurasi ±' + accuracy + ' m' : '')"></p>
            <p x-show="locationFetched && officeId" x-cloak class="g-num text-xs text-guru-muted" x-text="'Jarak ' + Math.round(currentDistance) + ' m · batas ' + maxDistance + ' m'"></p>
            <p x-show="locationError" x-cloak class="g-error mt-1" x-text="locationError"></p>
        </div>
        <button type="button" @click="fetchLocation()" :disabled="locationLoading" class="g-iconbtn" aria-label="Perbarui lokasi GPS">
            <x-guru.icon name="refresh" ::class="{ 'animate-spin': locationLoading }" />
        </button>
    </div>
</x-guru.card>

<x-guru.notice tone="error" title="Di luar radius kantor" x-show="distanceWarning" x-cloak>
    <p x-text="'Jarak ' + Math.round(currentDistance) + ' m, batas ' + maxDistance + ' m. Masuk ke area kantor untuk absen.'"></p>
    <p x-show="accuracy > 50" class="mt-1" x-text="'Sinyal GPS kurang akurat (±' + accuracy + ' m). Jika Anda sudah di kantor, coba dekat jendela atau di luar ruangan, lalu tekan tombol perbarui lokasi.'"></p>
</x-guru.notice>

<x-guru.notice tone="ok" title="Lokasi terverifikasi" x-show="distanceOk && locationFetched && officeId" x-cloak>
    <p x-text="'Jarak ' + Math.round(currentDistance) + ' m dari titik pusat kantor.'"></p>
</x-guru.notice>

@if ($errors->any())
    <x-guru.notice tone="error" title="Absen belum tersimpan" role="alert">
        <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-guru.notice>
@endif

<form method="POST" action="{{ $action }}" @submit.prevent="submitForm">
    @csrf
    <input type="hidden" name="latitude" x-model="latitude">
    <input type="hidden" name="longitude" x-model="longitude">
    <input type="hidden" name="image_base64" x-model="imageBase64">
    <input type="hidden" name="office_id" x-model="officeId">

    <button type="submit" :disabled="!canSubmit || isSubmitting" :aria-busy="isSubmitting.toString()"
        class="g-btn g-btn--block {{ $isPulang ? 'g-btn--pulang' : 'g-btn--primary' }}">
        <x-guru.icon :name="$isPulang ? 'logout' : 'login'" />
        <span x-text="isSubmitting ? 'Mengirim…' : @js($isPulang ? 'Kirim Absen Pulang' : 'Kirim Absen Masuk')">{{ $isPulang ? 'Kirim Absen Pulang' : 'Kirim Absen Masuk' }}</span>
    </button>
</form>
```

- [ ] **Step 4: Ganti bagian atas `selfie.blade.php`**

Hapus baris 1 sampai sebelum `    <!-- Alpine.js logic controller -->`, termasuk blok `<style>` lama; CSS kamera sekarang ada di `guru.css`. Ganti dengan kode di bawah. Script dan `</x-layouts.mobile>` di akhir berkas **tidak diubah**.

```blade
<x-layouts.mobile title="Absensi Masuk" backUrl="{{ route('attendance.dashboard') }}">
    <div x-data="attendanceForm()" x-init="init()" class="flex flex-col gap-4">
        @if ($todayAttendance)
            <x-guru.card variant="hero" aria-label="Absen masuk tercatat">
                <div class="flex items-center justify-between gap-3">
                    <span class="g-hero__label">Absen masuk tercatat</span>
                    <x-guru.chip :tone="$todayAttendance->status->value === 'late' ? 'hero-late' : 'hero'">{{ $todayAttendance->status->value === 'late' ? 'Terlambat' : 'Tepat waktu' }}</x-guru.chip>
                </div>
                <div class="flex items-center gap-4">
                    <img src="{{ $todayAttendance->image_url }}" alt="Foto absen masuk hari ini" width="72" height="72" class="size-18 flex-none rounded-2xl object-cover">
                    <div class="g-hero__time">
                        <span class="g-hero__time-label">Masuk</span>
                        <span class="g-hero__time-value">{{ $todayAttendance->created_at->format('H.i') }}</span>
                    </div>
                </div>
                <x-guru.button variant="hero" :href="route('attendance.dashboard')" icon="home">Kembali ke Beranda</x-guru.button>
            </x-guru.card>
        @else
            @include('attendance.partials.absen-form', ['mode' => 'masuk', 'action' => route('attendance.store')])
        @endif
    </div>

```

- [ ] **Step 5: Ganti bagian atas `checkout.blade.php`**

Sama seperti Step 4: hapus baris 1 sampai sebelum `    <!-- Alpine.js logic controller -->`, lalu ganti dengan:

```blade
<x-layouts.mobile title="Absensi Pulang" backUrl="{{ route('attendance.dashboard') }}">
    <div x-data="checkoutForm()" x-init="init()" class="flex flex-col gap-4">
        @if (! $todayAttendance)
            <x-guru.empty icon="login" title="Belum absen masuk">
                Absen masuk dulu sebelum mencatat absen pulang hari ini.
                <x-slot:action>
                    <x-guru.button :href="route('attendance.selfie')" icon="login">Absen Masuk Sekarang</x-guru.button>
                </x-slot:action>
            </x-guru.empty>
        @elseif (! ($checkoutTimeReached ?? true) && ! $todayAttendance->hasCheckedOut())
            <x-guru.empty icon="clock" title="Belum waktunya pulang">
                Absen pulang dibuka pukul {{ $checkoutOpensAt->format('H.i') }}.
                <x-slot:action>
                    <x-guru.button variant="secondary" :href="route('attendance.dashboard')" icon="home">Kembali ke Beranda</x-guru.button>
                </x-slot:action>
            </x-guru.empty>
        @elseif ($todayAttendance->hasCheckedOut())
            <x-guru.card variant="hero" aria-label="Absen pulang tercatat">
                <div class="flex items-center justify-between gap-3">
                    <span class="g-hero__label">Absen pulang tercatat</span>
                    <x-guru.chip tone="hero">Selesai</x-guru.chip>
                </div>
                <div class="flex items-center gap-4">
                    @if ($todayAttendance->check_out_image_url)
                        <img src="{{ $todayAttendance->check_out_image_url }}" alt="Foto absen pulang hari ini" width="72" height="72" class="size-18 flex-none rounded-2xl object-cover">
                    @endif
                    <div class="g-hero__time">
                        <span class="g-hero__time-label">Pulang</span>
                        <span class="g-hero__time-value">{{ $todayAttendance->check_out_at->format('H.i') }}</span>
                    </div>
                </div>
                <x-guru.button variant="hero" :href="route('attendance.dashboard')" icon="home">Kembali ke Beranda</x-guru.button>
            </x-guru.card>
        @else
            @include('attendance.partials.absen-form', ['mode' => 'pulang', 'action' => route('attendance.checkout.store')])
        @endif
    </div>

```

- [ ] **Step 6: Pastikan script tidak berubah**

Run:

```bash
for f in selfie checkout; do
  diff <(git show HEAD:resources/views/attendance/$f.blade.php | sed -n '/<!-- Alpine.js logic controller -->/,$p') \
       <(sed -n '/<!-- Alpine.js logic controller -->/,$p' resources/views/attendance/$f.blade.php) && echo "$f: script identik"
done
```

Expected: `selfie: script identik` dan `checkout: script identik`, tanpa baris diff.

- [ ] **Step 7: Jalankan test**

Run: `php artisan test tests/Feature/Employee/AbsenPageTest.php tests/Feature/Employee/LivenessFallbackTest.php tests/Feature/Employee/LockedOfficeTest.php tests/Feature/Employee/CheckoutTimeWindowTest.php tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add resources/views/attendance/partials/absen-form.blade.php resources/views/attendance/selfie.blade.php resources/views/attendance/checkout.blade.php tests/Feature/Employee/AbsenPageTest.php
git commit -m "feat(guru): halaman absen masuk/pulang gaya baru

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 7: Riwayat

**Files:**
- Modify (tulis ulang): `resources/views/attendance/index.blade.php`
- Test: `tests/Feature/Employee/RiwayatPageTest.php`

**Interfaces:**
- Consumes: `$attendances` (paginator 10 per halaman, urut terbaru, dari `AttendanceController@index`); komponen Task 2.

- [ ] **Step 1: Tulis test yang gagal**

```php
<?php

// tests/Feature/Employee/RiwayatPageTest.php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

test('history groups days per month with times and status chips', function () {
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $user = User::factory()->create(['role_id' => $role->id]);
    foreach ([['2026-07-20 06:52:00', AttendanceStatus::Present, '2026-07-20 14:05:00'], ['2026-06-30 07:31:00', AttendanceStatus::Late, null]] as [$at, $status, $out]) {
        $a = Attendance::create(['user_id' => $user->id, 'status' => $status, 'image_path' => 'x.jpg', 'check_in_lat' => -6.2, 'check_in_long' => 106.8, 'distance_meters' => 38, 'check_out_at' => $out]);
        $a->created_at = Carbon::parse($at);
        $a->save();
    }

    $this->actingAs($user)->get(route('attendance.index'))
        ->assertSuccessful()
        ->assertSeeInOrder(['Juli 2026', '06.52', '14.05', 'Tepat waktu', 'Juni 2026', '07.31', '––.––', 'Terlambat'])
        ->assertSee('38 m dari titik absen')
        ->assertDontSee('glass-card');
});

test('empty history teaches the first step', function () {
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    $this->actingAs(User::factory()->create(['role_id' => $role->id]))->get(route('attendance.index'))
        ->assertSee('Belum ada riwayat')
        ->assertSee('href="'.route('attendance.dashboard').'"', false);
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/RiwayatPageTest.php`
Expected: FAIL (`Juli 2026` tidak ditemukan).

- [ ] **Step 3: Tulis ulang `resources/views/attendance/index.blade.php`**

```blade
<x-layouts.mobile title="Riwayat Absen" backUrl="{{ route('attendance.dashboard') }}" activeTab="riwayat">
    <x-slot:headerAction>
        <x-guru.chip>{{ $attendances->total() }} hari</x-guru.chip>
    </x-slot:headerAction>

    @forelse ($attendances->getCollection()->groupBy(fn ($attendance) => $attendance->created_at->format('Y-m')) as $month => $days)
        <section class="flex flex-col gap-2.5" aria-labelledby="bulan-{{ $month }}">
            <h2 id="bulan-{{ $month }}" class="g-h2">{{ $days->first()->created_at->locale('id')->isoFormat('MMMM Y') }}</h2>
            <x-guru.list>
                @foreach ($days as $attendance)
                    <div class="g-list__item">
                        <span class="g-history__date">
                            <span>{{ $attendance->created_at->format('d') }}</span>
                            <span>{{ $attendance->created_at->locale('id')->isoFormat('ddd') }}</span>
                        </span>
                        <span class="g-list__body">
                            <span class="g-history__times g-num">
                                <span>Masuk <b>{{ $attendance->created_at->format('H.i') }}</b></span>
                                <span>Pulang <b>{{ $attendance->check_out_at?->format('H.i') ?? '––.––' }}</b></span>
                            </span>
                            <span class="g-list__desc">
                                {{ number_format((float) $attendance->distance_meters, 0, ',', '.') }} m dari titik absen
                                · <a href="{{ $attendance->image_url }}" target="_blank" rel="noopener">Foto masuk</a>
                                @if ($attendance->check_out_image_url)
                                    · <a href="{{ $attendance->check_out_image_url }}" target="_blank" rel="noopener">Foto pulang</a>
                                @endif
                            </span>
                        </span>
                        <x-guru.chip :tone="$attendance->status->value === 'late' ? 'late' : 'ok'">{{ $attendance->status->value === 'late' ? 'Terlambat' : 'Tepat waktu' }}</x-guru.chip>
                    </div>
                @endforeach
            </x-guru.list>
        </section>
    @empty
        <x-guru.empty icon="calendar" title="Belum ada riwayat">
            Jam masuk dan pulang Anda tercatat di sini setelah absen pertama.
            <x-slot:action>
                <x-guru.button :href="route('attendance.dashboard')" icon="home">Ke Beranda</x-guru.button>
            </x-slot:action>
        </x-guru.empty>
    @endforelse

    @if ($attendances->hasPages())
        <div class="g-pager">{{ $attendances->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
```

- [ ] **Step 4: Jalankan test, commit**

Run: `php artisan test tests/Feature/Employee/RiwayatPageTest.php tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php`
Expected: PASS.

```bash
git add resources/views/attendance/index.blade.php tests/Feature/Employee/RiwayatPageTest.php
git commit -m "feat(guru): halaman riwayat per bulan

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 8: Izin dan persetujuan izin

**Files:**
- Modify (tulis ulang): `resources/views/attendance/leaves/index.blade.php`, `create.blade.php`, `show.blade.php`, `approval-index.blade.php`, `approval-show.blade.php`
- Modify: `tests/Feature/Employee/LeaveCreatePageTest.php` (baris 15)
- Test: `tests/Feature/Employee/IzinPagesTest.php`

**Interfaces:**
- Consumes:
  - Variabel view: `$leaves` (paginator) di index dan approval-index; `$pendingCount` di approval-index; `$leave` di show dan approval-show.
  - Accessor model: `type_label`, `status_label`, `duration`, `attachment_url`, `isPending()`, `isApproved()`, `isRejected()`, `approver`, `approved_at`, `rejection_reason`, `user->initials()`.
  - Route: `attendance.leaves.store`, `approval.leaves.approve`, `approval.leaves.reject`. Field form: `type`, `start_date`, `end_date`, `reason`, `attachment`, `rejection_reason`.

- [ ] **Step 1: Tulis test yang gagal**

```php
<?php

// tests/Feature/Employee/IzinPagesTest.php

use App\Models\Leave;
use App\Models\Role;
use App\Models\User;

function izinUser(string $slug = 'guru'): User
{
    $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug), 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

function izinLeave(User $user, string $status, array $extra = []): Leave
{
    return Leave::create(array_merge(['user_id' => $user->id, 'type' => 'sakit', 'start_date' => '2026-07-21', 'end_date' => '2026-07-22', 'reason' => 'Demam tinggi', 'status' => $status], $extra));
}

test('leave list shows type, duration and a status chip', function () {
    $user = izinUser();
    izinLeave($user, 'pending');
    izinLeave($user, 'rejected', ['reason' => 'Acara keluarga']);

    $this->actingAs($user)->get(route('attendance.leaves.index'))
        ->assertSuccessful()
        ->assertSee('Sakit · 2 hari')
        ->assertSee('g-chip g-chip--pending', false)
        ->assertSee('g-chip g-chip--attn', false)
        ->assertSee('href="'.route('attendance.leaves.create').'"', false)
        ->assertDontSee('glass-card');
});

test('empty leave list teaches how to apply', function () {
    $this->actingAs(izinUser())->get(route('attendance.leaves.index'))
        ->assertSee('Belum ada pengajuan')
        ->assertSee('Ajukan Izin');
});

test('leave form offers three choice cards and an accessible attachment', function () {
    $html = $this->actingAs(izinUser())->get(route('attendance.leaves.create'))
        ->assertSee('Jenis Perizinan')
        ->assertSee('id="attachment"', false)
        ->assertSee('Kirim Pengajuan Izin')
        ->getContent();

    expect(substr_count($html, 'class="g-choice"'))->toBe(3)
        ->and(substr_count($html, 'name="type"'))->toBe(3);
});

test('leave detail shows the rejection reason', function () {
    $user = izinUser();
    $leave = izinLeave($user, 'rejected', ['rejection_reason' => 'Dokumen kurang']);

    $this->actingAs($user)->get(route('attendance.leaves.show', $leave))
        ->assertSee('Demam tinggi')
        ->assertSee('Dokumen kurang');
});

test('approval pages filter and act on requests', function () {
    $head = izinUser('kepala-sekolah');
    $leave = izinLeave(izinUser(), 'pending');

    $this->actingAs($head)->get(route('approval.leaves.index', ['status' => 'pending']))
        ->assertSuccessful()
        ->assertSee('aria-current="page"', false)
        ->assertSee('1 menunggu')
        ->assertSee(route('approval.leaves.show', $leave));

    $this->actingAs($head)->get(route('approval.leaves.show', $leave))
        ->assertSee('Setujui Pengajuan')
        ->assertSee('name="rejection_reason"', false)
        ->assertSee('Tolak Pengajuan');
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/IzinPagesTest.php`
Expected: FAIL.

- [ ] **Step 3: Tulis ulang `leaves/index.blade.php`**

```blade
@php
    $toneFor = fn ($leave) => match ($leave->status) { 'approved' => 'ok', 'rejected' => 'attn', default => 'pending' };
    $iconFor = fn ($leave) => match ($leave->type) { 'sakit' => 'medical', 'cuti' => 'briefcase', default => 'doc' };
@endphp

<x-layouts.mobile title="Perizinan Saya" backUrl="{{ route('attendance.dashboard') }}" activeTab="izin">
    <x-slot:headerAction>
        <x-guru.button :href="route('attendance.leaves.create')" icon="plus" class="g-btn--sm">Ajukan</x-guru.button>
    </x-slot:headerAction>

    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    @if ($leaves->isEmpty())
        <x-guru.empty icon="doc" title="Belum ada pengajuan">
            Izin, cuti, atau sakit yang Anda ajukan tampil di sini beserta statusnya.
            <x-slot:action>
                <x-guru.button :href="route('attendance.leaves.create')" icon="plus">Ajukan Izin</x-guru.button>
            </x-slot:action>
        </x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($leaves as $leave)
                <x-guru.list-item :href="route('attendance.leaves.show', $leave)" :icon="$iconFor($leave)" tone="izin"
                    :title="$leave->type_label.' · '.$leave->duration.' hari'" :desc="$leave->reason">
                    <span class="g-list__desc g-num">
                        {{ $leave->start_date->locale('id')->isoFormat('D MMM Y') }}@if (! $leave->start_date->equalTo($leave->end_date)) – {{ $leave->end_date->locale('id')->isoFormat('D MMM Y') }}@endif
                    </span>
                    <x-slot:end>
                        <x-guru.chip :tone="$toneFor($leave)">{{ $leave->status_label }}</x-guru.chip>
                    </x-slot:end>
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($leaves->hasPages())
        <div class="g-pager">{{ $leaves->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
```

- [ ] **Step 4: Tulis ulang `leaves/create.blade.php`**

```blade
<x-layouts.mobile title="Ajukan Izin" backUrl="{{ route('attendance.leaves.index') }}" activeTab="izin">
    <form action="{{ route('attendance.leaves.store') }}" method="POST" enctype="multipart/form-data" class="g-card"
        x-data="{
            sending: false,
            imagePreview: null,
            pick(event) {
                const file = event.target.files[0];
                if (! file) { this.imagePreview = null; return; }
                const reader = new FileReader();
                reader.onload = (loaded) => { this.imagePreview = loaded.target.result; };
                reader.readAsDataURL(file);
            },
            clearFile() { this.imagePreview = null; this.$refs.file.value = ''; },
        }"
        @submit="sending = true" @pageshow.window="sending = false">
        @csrf

        @if ($errors->any())
            <x-guru.notice tone="error" title="Periksa kembali isian Anda" role="alert">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-guru.notice>
        @endif

        <fieldset>
            <legend class="g-label">Jenis Perizinan <span class="g-req" aria-hidden="true">*</span></legend>
            <div class="g-choices">
                @foreach (['izin' => ['Izin', 'doc'], 'cuti' => ['Cuti', 'briefcase'], 'sakit' => ['Sakit', 'medical']] as $value => [$label, $icon])
                    <label class="g-choice">
                        <input type="radio" name="type" value="{{ $value }}" @checked(old('type', 'izin') === $value) required>
                        <x-guru.icon :name="$icon" :size="22" />
                        {{ $label }}
                        <span class="g-choice__check" aria-hidden="true"><x-guru.icon name="check" :size="14" /></span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="grid grid-cols-2 gap-3">
            <x-guru.field label="Tanggal mulai" for="start_date" error="start_date" :required="true">
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" class="g-input" required
                    @error('start_date') aria-invalid="true" aria-describedby="start_date-error" @enderror>
            </x-guru.field>
            <x-guru.field label="Tanggal selesai" for="end_date" error="end_date" :required="true">
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', date('Y-m-d')) }}" class="g-input" required
                    @error('end_date') aria-invalid="true" aria-describedby="end_date-error" @enderror>
            </x-guru.field>
        </div>

        <x-guru.field label="Alasan pengajuan" for="reason" error="reason" :required="true">
            <textarea name="reason" id="reason" rows="3" class="g-input" placeholder="Tuliskan alasan dengan jelas" required
                @error('reason') aria-invalid="true" aria-describedby="reason-error" @enderror>{{ old('reason') }}</textarea>
        </x-guru.field>

        <div class="g-field">
            <span class="g-label">Dokumen pendukung <span class="font-medium text-guru-muted">(opsional)</span></span>
            <label for="attachment" class="g-drop">
                <input type="file" name="attachment" id="attachment" accept="image/*" class="sr-only" x-ref="file" @change="pick($event)" aria-describedby="attachment-hint">
                <template x-if="imagePreview">
                    <img :src="imagePreview" alt="Pratinjau lampiran" class="g-drop__preview">
                </template>
                <span x-show="! imagePreview" class="g-list__icon g-list__icon--izin"><x-guru.icon name="image" /></span>
                <span class="font-bold" x-text="imagePreview ? 'Ganti foto' : 'Pilih foto bukti atau surat'">Pilih foto bukti atau surat</span>
                <span id="attachment-hint" class="text-xs text-guru-muted">JPG atau PNG, maksimal 5 MB</span>
            </label>
            <button type="button" x-show="imagePreview" x-cloak @click="clearFile()" class="g-btn g-btn--secondary g-btn--sm mt-2 self-start">
                <x-guru.icon name="x" /> Hapus lampiran
            </button>
            @error('attachment')
                <p id="attachment-error" class="g-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="g-btn g-btn--primary g-btn--block" :disabled="sending" :aria-busy="sending.toString()">
            <x-guru.icon name="send" />
            <span x-text="sending ? 'Mengirim…' : 'Kirim Pengajuan Izin'">Kirim Pengajuan Izin</span>
        </button>
    </form>
</x-layouts.mobile>
```

- [ ] **Step 5: Tulis ulang `leaves/show.blade.php`**

```blade
@php
    $tone = match ($leave->status) { 'approved' => 'ok', 'rejected' => 'attn', default => 'pending' };
@endphp

<x-layouts.mobile title="Detail Perizinan" backUrl="{{ route('attendance.leaves.index') }}" activeTab="izin">
    <x-guru.card>
        <div class="flex items-center justify-between gap-3">
            <x-guru.chip tone="izin">{{ $leave->type_label }}</x-guru.chip>
            <x-guru.chip :tone="$tone">{{ $leave->status_label }}</x-guru.chip>
        </div>
        <div class="flex flex-col gap-1">
            <span class="g-card__caps">Tanggal</span>
            <p class="g-card__title">
                {{ $leave->start_date->locale('id')->isoFormat('D MMM Y') }}@if (! $leave->start_date->equalTo($leave->end_date)) – {{ $leave->end_date->locale('id')->isoFormat('D MMM Y') }}@endif
            </p>
            <p class="text-sm text-guru-muted">{{ $leave->duration }} hari · diajukan {{ $leave->created_at->locale('id')->isoFormat('D MMM Y, HH.mm') }}</p>
        </div>
    </x-guru.card>

    <x-guru.card as="div">
        <h2 class="g-h2">Alasan</h2>
        <p class="whitespace-pre-line">{{ $leave->reason }}</p>
    </x-guru.card>

    @if ($leave->attachment)
        <x-guru.card as="div">
            <h2 class="g-h2">Lampiran</h2>
            <a href="{{ $leave->attachment_url }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-2xl bg-guru-surface-2">
                <img src="{{ $leave->attachment_url }}" alt="Lampiran pengajuan" class="max-h-72 w-full object-contain">
            </a>
        </x-guru.card>
    @endif

    @unless ($leave->isPending())
        <x-guru.card as="div">
            <h2 class="g-h2">{{ $leave->isApproved() ? 'Disetujui oleh' : 'Ditolak oleh' }}</h2>
            <div class="flex items-center gap-3">
                <span class="g-avatar g-avatar--sm">{{ $leave->approver?->initials() ?? '?' }}</span>
                <div class="min-w-0">
                    <p class="truncate font-bold">{{ $leave->approver?->name ?? '-' }}</p>
                    <p class="text-xs text-guru-muted">{{ $leave->approved_at?->locale('id')->isoFormat('D MMM Y, HH.mm') ?? '-' }}</p>
                </div>
            </div>
            @if ($leave->isRejected() && $leave->rejection_reason)
                <x-guru.notice tone="error" title="Alasan penolakan">{{ $leave->rejection_reason }}</x-guru.notice>
            @endif
        </x-guru.card>
    @endunless
</x-layouts.mobile>
```

- [ ] **Step 6: Tulis ulang `leaves/approval-index.blade.php`**

```blade
@php
    $toneFor = fn ($leave) => match ($leave->status) { 'approved' => 'ok', 'rejected' => 'attn', default => 'pending' };
    $current = (string) request('status', '');
@endphp

<x-layouts.mobile title="Persetujuan Izin" backUrl="{{ route('attendance.dashboard') }}">
    @if ($pendingCount > 0)
        <x-slot:headerAction>
            <x-guru.chip tone="pending">{{ $pendingCount }} menunggu</x-guru.chip>
        </x-slot:headerAction>
    @endif

    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    <nav aria-label="Filter status" class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
        @foreach (['' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
            <a href="{{ route('approval.leaves.index', $value !== '' ? ['status' => $value] : []) }}"
                @class(['g-chip min-h-11 px-4', 'g-chip--ok' => $current === $value])
                @if ($current === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($leaves->isEmpty())
        <x-guru.empty icon="clipboard" title="Tidak ada pengajuan">Belum ada pengajuan izin untuk filter ini.</x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($leaves as $leave)
                <a href="{{ route('approval.leaves.show', $leave) }}" class="g-list__item">
                    <span class="g-avatar g-avatar--sm">{{ $leave->user->initials() }}</span>
                    <span class="g-list__body">
                        <span class="g-list__title truncate">{{ $leave->user->name }}</span>
                        <span class="g-list__desc">{{ $leave->type_label }} · {{ $leave->duration }} hari · {{ $leave->start_date->locale('id')->isoFormat('D MMM Y') }}</span>
                    </span>
                    <x-guru.chip :tone="$toneFor($leave)">{{ $leave->status_label }}</x-guru.chip>
                    <x-guru.icon name="chevron" :size="18" class="g-list__chev" />
                </a>
            @endforeach
        </x-guru.list>
    @endif

    @if ($leaves->hasPages())
        <div class="g-pager">{{ $leaves->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
```

- [ ] **Step 7: Tulis ulang `leaves/approval-show.blade.php`**

```blade
@php
    $tone = match ($leave->status) { 'approved' => 'ok', 'rejected' => 'attn', default => 'pending' };
@endphp

<x-layouts.mobile title="Detail Pengajuan" backUrl="{{ route('approval.leaves.index') }}">
    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif
    @if (session('error'))
        <x-guru.notice tone="error" role="alert">{{ session('error') }}</x-guru.notice>
    @endif

    <x-guru.card as="div">
        <div class="flex items-center gap-3">
            <span class="g-avatar">{{ $leave->user->initials() }}</span>
            <div class="min-w-0">
                <p class="truncate text-[17px] font-bold">{{ $leave->user->name }}</p>
                <p class="truncate text-xs text-guru-muted">{{ $leave->user->role?->name ?? 'Pegawai' }} · {{ $leave->user->office?->name ?? '-' }}</p>
            </div>
        </div>
    </x-guru.card>

    <x-guru.card>
        <div class="flex items-center justify-between gap-3">
            <x-guru.chip tone="izin">{{ $leave->type_label }}</x-guru.chip>
            <x-guru.chip :tone="$tone">{{ $leave->status_label }}</x-guru.chip>
        </div>
        <div class="flex flex-col gap-1">
            <span class="g-card__caps">Tanggal</span>
            <p class="g-card__title">
                {{ $leave->start_date->locale('id')->isoFormat('D MMM Y') }}@if (! $leave->start_date->equalTo($leave->end_date)) – {{ $leave->end_date->locale('id')->isoFormat('D MMM Y') }}@endif
            </p>
            <p class="text-sm text-guru-muted">{{ $leave->duration }} hari · diajukan {{ $leave->created_at->locale('id')->isoFormat('D MMM Y, HH.mm') }}</p>
        </div>
        <div class="flex flex-col gap-1">
            <span class="g-card__caps">Alasan</span>
            <p class="whitespace-pre-line">{{ $leave->reason }}</p>
        </div>
    </x-guru.card>

    @if ($leave->attachment)
        <x-guru.card as="div">
            <h2 class="g-h2">Lampiran</h2>
            <a href="{{ $leave->attachment_url }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-2xl bg-guru-surface-2">
                <img src="{{ $leave->attachment_url }}" alt="Lampiran pengajuan" class="max-h-72 w-full object-contain">
            </a>
        </x-guru.card>
    @endif

    @if ($leave->isPending())
        <form action="{{ route('approval.leaves.approve', $leave) }}" method="POST">
            @csrf
            <x-guru.button type="submit" icon="check" class="g-btn--block" onclick="return confirm('Setujui pengajuan izin ini?')">Setujui Pengajuan</x-guru.button>
        </form>

        <form action="{{ route('approval.leaves.reject', $leave) }}" method="POST" class="g-card">
            @csrf
            <x-guru.field label="Tolak dengan alasan" for="rejection_reason" error="rejection_reason">
                <textarea name="rejection_reason" id="rejection_reason" rows="2" class="g-input" placeholder="Tuliskan alasan penolakan"
                    @error('rejection_reason') aria-invalid="true" aria-describedby="rejection_reason-error" @enderror>{{ old('rejection_reason') }}</textarea>
            </x-guru.field>
            <x-guru.button type="submit" variant="danger" icon="x" class="g-btn--block" onclick="return confirm('Tolak pengajuan izin ini?')">Tolak Pengajuan</x-guru.button>
        </form>
    @else
        <x-guru.card as="div">
            <h2 class="g-h2">{{ $leave->isApproved() ? 'Disetujui oleh' : 'Ditolak oleh' }}</h2>
            <div class="flex items-center gap-3">
                <span class="g-avatar g-avatar--sm">{{ $leave->approver?->initials() ?? '?' }}</span>
                <div class="min-w-0">
                    <p class="truncate font-bold">{{ $leave->approver?->name ?? '-' }}</p>
                    <p class="text-xs text-guru-muted">{{ $leave->approved_at?->locale('id')->isoFormat('D MMM Y, HH.mm') ?? '-' }}</p>
                </div>
            </div>
            @if ($leave->isRejected() && $leave->rejection_reason)
                <x-guru.notice tone="error" title="Alasan penolakan">{{ $leave->rejection_reason }}</x-guru.notice>
            @endif
        </x-guru.card>
    @endif
</x-layouts.mobile>
```

- [ ] **Step 8: Sesuaikan `LeaveCreatePageTest`**

Ganti `->assertSee('peer-checked:opacity-100', false);` dengan `->assertSee('g-choice__check', false);`.

- [ ] **Step 9: Jalankan test, commit**

Run: `php artisan test tests/Feature/Employee/IzinPagesTest.php tests/Feature/Employee/LeaveFlowTest.php tests/Feature/Employee/LeaveCreatePageTest.php tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php`
Expected: PASS.

```bash
git add resources/views/attendance/leaves tests/Feature/Employee/IzinPagesTest.php tests/Feature/Employee/LeaveCreatePageTest.php
git commit -m "feat(guru): halaman izin dan persetujuan izin gaya baru

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 9: Profil (+tema, keluar), Ganti Password, Informasi

**Files:**
- Modify (tulis ulang): `resources/views/attendance/profile.blade.php`, `resources/views/attendance/password.blade.php`, `resources/views/attendance/information/show.blade.php`
- Test: `tests/Feature/Employee/ProfilPagesTest.php`

**Interfaces:**
- Consumes:
  - `$user` di profil (dari `Employee\ProfileController@show`); `$announcement` di informasi.
  - Route `attendance.profile.update` (PUT, field `avatar`, `name`, `email`), `attendance.password.update` (PUT, field `current_password`, `password`, `password_confirmation`), `logout`.
  - Fungsi `window.guruSetTheme`, `window.guruThemeMode` (Task 3).

- [ ] **Step 1: Tulis test yang gagal**

```php
<?php

// tests/Feature/Employee/ProfilPagesTest.php

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;

function profilUser(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id, 'name' => 'Siti Rahmawati']);
}

test('profile offers the theme picker, password link and logout', function () {
    $html = $this->actingAs(profilUser())->get(route('attendance.profile'))
        ->assertSuccessful()
        ->assertSee('Profil Saya')
        ->assertSee('Siti Rahmawati')
        ->assertSee('Tema tampilan')
        ->assertSee('guruSetTheme(mode)', false)
        ->assertSee('href="'.route('attendance.password').'"', false)
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('Keluar')
        ->getContent();

    expect(substr_count($html, 'name="appearance"'))->toBe(3);
});

test('password form labels every field and offers show/hide toggles', function () {
    $html = $this->actingAs(profilUser())->get(route('attendance.password'))
        ->assertSee('Ganti Password')
        ->assertSee('for="current_password"', false)
        ->assertSee('autocomplete="current-password"', false)
        ->assertSee('autocomplete="new-password"', false)
        ->getContent();

    expect(substr_count($html, ':aria-pressed="show.toString()"'))->toBe(3);
});

test('announcement detail renders title and body', function () {
    $user = profilUser();
    $announcement = Announcement::create(['title' => 'Rapat Guru', 'summary' => 'Jumat pagi', 'body' => 'Rapat di aula.', 'is_active' => true]);

    $this->actingAs($user)->get(route('attendance.information.show', $announcement))
        ->assertSuccessful()
        ->assertSee('Rapat Guru')
        ->assertSee('Rapat di aula.');
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/ProfilPagesTest.php`
Expected: FAIL (`Tema tampilan` tidak ditemukan).

- [ ] **Step 3: Tulis ulang `attendance/profile.blade.php`**

```blade
<x-layouts.mobile title="Profil Saya" backUrl="{{ route('attendance.dashboard') }}">
    <x-guru.card class="items-center text-center">
        <span class="g-avatar g-avatar--xl">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="">
            @else
                {{ $user->initials() }}
            @endif
        </span>
        <div class="flex flex-col gap-1">
            <p class="g-card__title">{{ $user->name }}</p>
            <p class="text-sm text-guru-muted">{{ $user->role?->name ?? 'Pegawai' }} · {{ $user->office?->name ?? '-' }}</p>
        </div>
    </x-guru.card>

    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    <form action="{{ route('attendance.profile.update') }}" method="POST" enctype="multipart/form-data" class="g-card" x-data="{ preview: null }">
        @csrf
        @method('PUT')
        <h2 class="g-h2">Data diri</h2>

        <div class="g-field">
            <span class="g-label">Foto profil</span>
            <div class="flex items-center gap-3">
                <span class="g-avatar">
                    <template x-if="preview"><img :src="preview" alt=""></template>
                    <template x-if="! preview">
                        <span class="contents">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="">
                            @else
                                {{ $user->initials() }}
                            @endif
                        </span>
                    </template>
                </span>
                <label class="g-btn g-btn--secondary g-btn--sm cursor-pointer has-[input:focus-visible]:outline-3 has-[input:focus-visible]:outline-guru-primary">
                    <x-guru.icon name="image" /> Pilih foto
                    <input type="file" name="avatar" accept="image/*" class="sr-only" aria-describedby="avatar-hint"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null">
                </label>
            </div>
            <p id="avatar-hint" class="g-hint">JPG, PNG, atau WEBP, maksimal 8 MB. Otomatis dikompres.</p>
            @error('avatar')
                <p class="g-error">{{ $message }}</p>
            @enderror
        </div>

        <x-guru.field label="Nama lengkap" for="name" error="name" :required="true">
            <input type="text" name="name" id="name" autocomplete="name" value="{{ old('name', $user->name) }}" class="g-input" required
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
        </x-guru.field>

        <x-guru.field label="Alamat email" for="email" error="email" :required="true">
            <input type="email" name="email" id="email" autocomplete="email" value="{{ old('email', $user->email) }}" class="g-input" required
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
        </x-guru.field>

        <x-guru.button type="submit" class="g-btn--block">Simpan Perubahan</x-guru.button>
    </form>

    <section class="g-card" aria-labelledby="judul-tema" x-data="{ mode: window.guruThemeMode() }">
        <h2 id="judul-tema" class="g-h2">Tema tampilan</h2>
        <div class="g-seg" role="radiogroup" aria-labelledby="judul-tema">
            @foreach (['light' => ['Terang', 'sun'], 'dark' => ['Gelap', 'moon'], 'system' => ['Ikut sistem', 'monitor']] as $value => [$label, $icon])
                <label>
                    <input type="radio" name="appearance" value="{{ $value }}" x-model="mode" @change="guruSetTheme(mode)">
                    <x-guru.icon :name="$icon" />
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </section>

    <x-guru.list>
        <x-guru.list-item :href="route('attendance.password')" icon="lock" tone="neutral" title="Ganti Password" desc="Kelola kata sandi akun" />
    </x-guru.list>

    <x-guru.card as="div">
        <h2 class="g-h2">Akun</h2>
        <dl class="g-dl">
            <div><dt>Instansi</dt><dd>{{ $user->office?->name ?? '-' }}</dd></div>
            <div><dt>Peran</dt><dd>{{ $user->role?->name ?? '-' }}</dd></div>
            <div><dt>Bergabung</dt><dd>{{ $user->created_at?->locale('id')->isoFormat('D MMMM Y') }}</dd></div>
        </dl>
    </x-guru.card>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-guru.button type="submit" variant="secondary" icon="logout" class="g-btn--block">Keluar</x-guru.button>
    </form>
</x-layouts.mobile>
```

- [ ] **Step 4: Tulis ulang `attendance/password.blade.php`**

```blade
<x-layouts.mobile title="Ganti Password" backUrl="{{ route('attendance.profile') }}">
    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    @if ($errors->any())
        <x-guru.notice tone="error" title="Password belum diganti" role="alert">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-guru.notice>
    @endif

    <form action="{{ route('attendance.password.update') }}" method="POST" class="g-card">
        @csrf
        @method('PUT')

        @foreach ([
            ['current_password', 'Password saat ini', 'current-password', 'Masukkan password saat ini'],
            ['password', 'Password baru', 'new-password', 'Minimal 8 karakter'],
            ['password_confirmation', 'Konfirmasi password baru', 'new-password', 'Ulangi password baru'],
        ] as [$name, $label, $autocomplete, $placeholder])
            <x-guru.field :label="$label" :for="$name" :error="$name" :required="true">
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" type="password" name="{{ $name }}" id="{{ $name }}" autocomplete="{{ $autocomplete }}"
                        placeholder="{{ $placeholder }}" class="g-input pr-14" required
                        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror>
                    <button type="button" @click="show = ! show" :aria-pressed="show.toString()" :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                        class="g-iconbtn absolute right-1 top-1/2 -translate-y-1/2 border-0 bg-transparent">
                        <x-guru.icon name="eye" x-show="! show" />
                        <x-guru.icon name="eye-off" x-show="show" x-cloak />
                    </button>
                </div>
            </x-guru.field>
        @endforeach

        <x-guru.button type="submit" class="g-btn--block">Simpan Password</x-guru.button>
    </form>

    <x-guru.notice title="Password yang kuat">
        <ul class="mt-1 list-disc pl-4">
            <li>Minimal 8 karakter.</li>
            <li>Campurkan huruf besar, huruf kecil, dan angka.</li>
            <li>Hindari nama atau tanggal lahir.</li>
        </ul>
    </x-guru.notice>
</x-layouts.mobile>
```

- [ ] **Step 5: Tulis ulang `attendance/information/show.blade.php`**

```blade
<x-layouts.mobile title="Informasi" backUrl="{{ route('attendance.dashboard') }}">
    <article class="g-card g-card--flush">
        @if ($announcement->image_url)
            <img src="{{ $announcement->image_url }}" alt="" class="aspect-video w-full object-cover">
        @endif
        <div class="flex flex-col gap-3 p-5">
            <x-guru.chip tone="ok" class="self-start">Pengumuman · {{ $announcement->created_at->locale('id')->isoFormat('D MMMM Y') }}</x-guru.chip>
            <h2 class="g-card__title">{{ $announcement->title }}</h2>
            @if ($announcement->summary)
                <p class="text-guru-muted">{{ $announcement->summary }}</p>
            @endif
            <p class="whitespace-pre-line border-t border-guru-divider pt-4 leading-relaxed">{{ $announcement->body }}</p>
        </div>
    </article>
</x-layouts.mobile>
```

- [ ] **Step 6: Jalankan test, commit**

Run: `php artisan test tests/Feature/Employee/ProfilPagesTest.php tests/Feature/AnnouncementTest.php tests/Feature/ProfileAvatarTest.php tests/Feature/Employee/TeacherAttendanceVisualSystemTest.php`
Expected: PASS.

```bash
git add resources/views/attendance/profile.blade.php resources/views/attendance/password.blade.php resources/views/attendance/information tests/Feature/Employee/ProfilPagesTest.php
git commit -m "feat(guru): profil dengan pengaturan tema, password, informasi

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 10: Kelas wali

**Files:**
- Modify (tulis ulang): `resources/views/attendance/my-class/index.blade.php`, `resources/views/attendance/my-class/show.blade.php`
- Delete: `resources/views/attendance/my-class/violation.blade.php` (tidak dipakai route atau controller mana pun)
- Test: `tests/Feature/Employee/MyClassTest.php` (tetap hijau), `tests/Feature/Employee/KelasPageTest.php`

**Interfaces:**
- Consumes: `$assignment`, `$students` (paginator dengan `violations_count`) di index; `$assignment`, `$student`, `$violations` di show; route `attendance.my-class.show`, `attendance.kesiswaan.referrals.create`.
- Wajib dipertahankan (`MyClassTest`):
  - index memuat `data-my-class-list`, `divide-y`, dan `violations_count > 0`, dan **tidak** memuat `space-y-4 p-4` maupun `solid-panel flex min-h-16`;
  - show memuat persis `backUrl="{{ route('attendance.my-class.index') }}"` dan teks `Status: {{ $record->status }}`.

- [ ] **Step 1: Pastikan berkas pelanggaran memang tidak dipakai, lalu tulis test yang gagal**

Run: `grep -rn "my-class.violation\|my-class/violation" app routes resources tests`
Expected: tidak ada hasil.

```php
<?php

// tests/Feature/Employee/KelasPageTest.php

test('my class views use the guru components', function () {
    $index = file_get_contents(resource_path('views/attendance/my-class/index.blade.php'));
    $show = file_get_contents(resource_path('views/attendance/my-class/show.blade.php'));

    expect($index)->toContain('activeTab="kelas"')->toContain('g-list__item')->not->toContain('theme-')
        ->and($show)->toContain('x-guru.card')->not->toContain('solid-panel');
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/KelasPageTest.php`
Expected: FAIL.

- [ ] **Step 3: Tulis ulang `my-class/index.blade.php`**

```blade
<x-layouts.mobile title="Kelas Saya" activeTab="kelas">
    <x-guru.card as="div">
        <span class="g-card__caps">Kelas wali · {{ $assignment->academicYear->name }}</span>
        <p class="g-card__title">{{ $assignment->schoolClass->name }}</p>
        <div class="flex flex-wrap gap-2">
            <x-guru.chip>{{ $students->total() }} siswa</x-guru.chip>
        </div>
    </x-guru.card>

    <form method="GET" role="search" class="g-search">
        <label class="sr-only" for="student-search">Cari siswa</label>
        <x-guru.icon name="search" :size="18" />
        <input id="student-search" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa" class="g-input">
    </form>

    <section class="g-list" data-my-class-list aria-label="Daftar siswa">
        <div class="divide-y divide-guru-divider">
            @forelse ($students as $student)
                <a href="{{ route('attendance.my-class.show', $student) }}" class="g-list__item">
                    <span class="g-avatar g-avatar--sm">{{ str($student->nama_lengkap)->substr(0, 2)->upper() }}</span>
                    <span class="g-list__body">
                        <span class="g-list__title truncate">{{ $student->nama_lengkap }}</span>
                        <span class="g-list__desc">NISN {{ $student->nisn ?? '-' }}</span>
                    </span>
                    @if ($student->violations_count > 0)
                        <x-guru.chip tone="attn" aria-label="{{ $student->violations_count }} pelanggaran">{{ $student->violations_count }}</x-guru.chip>
                    @endif
                    <x-guru.icon name="chevron" :size="18" class="g-list__chev" />
                </a>
            @empty
                <div class="g-empty flex flex-col">
                    <span class="g-empty__icon"><x-guru.icon name="users" :size="24" /></span>
                    <h2>Siswa tidak ditemukan</h2>
                    <p>{{ request('search') ? 'Coba gunakan kata kunci lain.' : 'Data siswa kelas ini belum tersedia.' }}</p>
                </div>
            @endforelse
        </div>
    </section>

    @if ($students->hasPages())
        <div class="g-pager">{{ $students->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
```

- [ ] **Step 4: Tulis ulang `my-class/show.blade.php`**

```blade
<x-layouts.mobile title="Profil Siswa" backUrl="{{ route('attendance.my-class.index') }}" activeTab="kelas">
    <x-guru.card>
        <span class="g-card__caps">{{ $assignment->schoolClass->name }}</span>
        <h2 class="g-card__title">{{ $student->nama_lengkap }}</h2>
        <dl class="g-dl">
            <div><dt>NISN</dt><dd>{{ $student->nisn ?? '-' }}</dd></div>
            <div><dt>Status</dt><dd>{{ $student->status }}</dd></div>
        </dl>
        <x-guru.button :href="route('attendance.kesiswaan.referrals.create', $student)" icon="send" class="g-btn--block">Buat rujukan ke Guru BK</x-guru.button>
    </x-guru.card>

    <section class="flex flex-col gap-2.5" aria-labelledby="judul-pelanggaran">
        <h2 id="judul-pelanggaran" class="g-h2">Ringkasan pelanggaran</h2>
        @if ($violations->isEmpty())
            <x-guru.empty icon="check" title="Tidak ada pelanggaran">Catatan pelanggaran siswa ini dari guru BK akan tampil di sini.</x-guru.empty>
        @else
            <x-guru.list>
                @foreach ($violations as $record)
                    <div class="g-list__item">
                        <span class="g-list__icon g-list__icon--attn"><x-guru.icon name="alert" /></span>
                        <span class="g-list__body">
                            <span class="g-list__title">{{ $record->category?->name ?? $record->custom_topic ?? 'Pelanggaran' }}</span>
                            <span class="g-list__desc">{{ $record->occurred_at->locale('id')->isoFormat('D MMM Y') }} · Status: {{ $record->status }}</span>
                        </span>
                    </div>
                @endforeach
            </x-guru.list>
        @endif
    </section>
</x-layouts.mobile>
```

- [ ] **Step 5: Hapus view pelanggaran yang mati, jalankan test, commit**

```bash
git rm resources/views/attendance/my-class/violation.blade.php
php artisan test tests/Feature/Employee/KelasPageTest.php tests/Feature/Employee/MyClassTest.php
git add resources/views/attendance/my-class tests/Feature/Employee/KelasPageTest.php
git commit -m "feat(guru): halaman kelas wali gaya baru

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

Expected: PASS.

---

### Task 11: Bimbingan Konseling (BK)

**Files:**
- Modify (tulis ulang): `resources/views/attendance/bk/index.blade.php`, `bk/form.blade.php`, `bk/show.blade.php`
- Modify (ganti class): `resources/views/attendance/bk/partials/student-combobox.blade.php`, `bk/partials/related-students-combobox.blade.php`
- Delete: `resources/views/attendance/bk/create.blade.php` (controller memakai `bk.form` untuk create dan edit)
- Test: `tests/Feature/Feature/BkAccessTest.php` (tetap hijau), `tests/Feature/Employee/BkPagesTest.php`

**Interfaces:**
- Consumes:
  - `$records` (paginator) di index; `$record`, `$students`, `$categories`, dan `$referral` (opsional) di form; `$record` beserta relasi `student`, `category`, `attachments`, `followUps`, dan `parentContacts` di show.
  - Nama field dan route tidak berubah: `attendance.bk.store`, `.update`, `.follow-ups.store`, `.parent-contacts.store`, `.archive`, `.restore`, `.attachments.show`, `.edit`.
- Wajib dipertahankan (BkAccessTest): `data-bk-student-combobox="primary|related"`, `role="combobox"`, `aria-controls="primary-student-options"`, `name="student_id"`, `name="related_student_ids[]"`.

- [ ] **Step 1: Pastikan `bk/create.blade.php` tidak dipakai, lalu tulis test yang gagal**

Run: `grep -rn "bk\.create'\|attendance.bk.create\b" app | grep view`
Expected: tidak ada hasil (`BkRecordController@create` mengembalikan `attendance.bk.form`).

```php
<?php

// tests/Feature/Employee/BkPagesTest.php

test('bk views use the guru components and no legacy classes', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/bk/{$view}.blade.php"));

    expect($template)->not->toContain('glass-card')->not->toContain('theme-')->not->toContain('solid-panel');
})->with(['index', 'form', 'show', 'partials/student-combobox', 'partials/related-students-combobox']);

test('the unused bk create view is gone', function () {
    expect(file_exists(resource_path('views/attendance/bk/create.blade.php')))->toBeFalse();
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/BkPagesTest.php`
Expected: FAIL.

- [ ] **Step 3: Tulis ulang `bk/index.blade.php`**

```blade
@php
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Diproses', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
@endphp

<x-layouts.mobile title="Catatan BK" backUrl="{{ route('attendance.dashboard') }}">
    <x-slot:headerAction>
        <x-guru.button :href="route('attendance.bk.create')" icon="plus" class="g-btn--sm">Tambah</x-guru.button>
    </x-slot:headerAction>

    <p class="text-sm text-guru-muted">Catatan bimbingan dan konseling yang Anda tangani.</p>

    @if ($records->isEmpty())
        <x-guru.empty icon="chat" title="Belum ada catatan">
            Buat catatan pertama untuk siswa yang Anda tangani.
            <x-slot:action>
                <x-guru.button :href="route('attendance.bk.create')" icon="plus">Buat Catatan</x-guru.button>
            </x-slot:action>
        </x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($records as $record)
                <x-guru.list-item :href="route('attendance.bk.show', $record)"
                    :icon="$record->record_type === 'violation' ? 'alert' : 'chat'"
                    :tone="$record->record_type === 'violation' ? 'attn' : 'izin'"
                    :title="$record->student->nama_lengkap"
                    :desc="$record->occurred_at->locale('id')->isoFormat('D MMM Y, HH.mm').' · '.($record->category?->name ?? $record->custom_topic)">
                    <x-slot:end>
                        <x-guru.chip>{{ $statusLabels[$record->status] ?? $record->status }}</x-guru.chip>
                    </x-slot:end>
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($records->hasPages())
        <div class="g-pager">{{ $records->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
```

- [ ] **Step 4: Tulis ulang `bk/form.blade.php`**

```blade
<x-layouts.mobile :title="$record->exists ? 'Edit Catatan BK' : 'Catatan BK Baru'"
    :backUrl="$record->exists ? route('attendance.bk.show', $record) : route('attendance.bk.index')">
    <form method="POST" enctype="multipart/form-data" action="{{ $record->exists ? route('attendance.bk.update', $record) : route('attendance.bk.store') }}" class="flex flex-col gap-4">
        @csrf
        @if ($record->exists)
            @method('PUT')
        @endif
        @if (! $record->exists && $record->student_referral_id)
            <input type="hidden" name="student_referral_id" value="{{ $record->student_referral_id }}">
        @endif

        @if ($errors->any())
            <x-guru.notice tone="error" title="Catatan belum tersimpan" role="alert">{{ $errors->first() }}</x-guru.notice>
        @endif

        <x-guru.card as="div">
            <h2 class="g-h2">Siswa</h2>
            @if (! $record->exists && $record->student_referral_id)
                <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                <x-guru.notice tone="ok">Siswa dari rujukan: <strong class="inline">{{ $referral->student->nama_lengkap }}</strong>. Siswa dan rujukan dikunci oleh server.</x-guru.notice>
            @else
                @include('attendance.bk.partials.student-combobox')
            @endif
            @include('attendance.bk.partials.related-students-combobox')
        </x-guru.card>

        <x-guru.card as="div">
            <h2 class="g-h2">Catatan</h2>
            <x-guru.field label="Jenis" for="record_type">
                <select id="record_type" name="record_type" class="g-input">
                    <option value="violation" @selected(old('record_type', $record->record_type) === 'violation')>Pelanggaran</option>
                    <option value="counseling" @selected(old('record_type', $record->record_type) === 'counseling')>Konseling</option>
                </select>
            </x-guru.field>
            <x-guru.field label="Kategori" for="category_id">
                <select id="category_id" name="category_id" class="g-input">
                    <option value="">Lainnya</option>
                    @foreach ($categories as $c)
                        <option data-type="{{ $c->record_type }}" data-severity="{{ $c->default_severity }}" value="{{ $c->id }}" @selected(old('category_id', $record->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </x-guru.field>
            <x-guru.field label="Topik lainnya" for="custom_topic">
                <input id="custom_topic" name="custom_topic" value="{{ old('custom_topic', $record->custom_topic) }}" class="g-input">
            </x-guru.field>
            <x-guru.field label="Waktu" for="occurred_at" :required="true">
                <input id="occurred_at" type="datetime-local" required name="occurred_at" value="{{ old('occurred_at', $record->occurred_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" class="g-input">
            </x-guru.field>

            <div id="violation" class="flex flex-col gap-4">
                <x-guru.field label="Tingkat" for="severity">
                    <select id="severity" name="severity" class="g-input">
                        @foreach (['light' => 'Ringan', 'medium' => 'Sedang', 'heavy' => 'Berat'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('severity', $record->severity) === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </x-guru.field>
                <x-guru.field label="Kronologi" for="chronology">
                    <textarea id="chronology" name="chronology" class="g-input">{{ old('chronology', $record->chronology) }}</textarea>
                </x-guru.field>
                <x-guru.field label="Tindakan" for="action_taken">
                    <textarea id="action_taken" name="action_taken" class="g-input">{{ old('action_taken', $record->action_taken) }}</textarea>
                </x-guru.field>
            </div>

            <div id="counseling" class="flex flex-col gap-4">
                <x-guru.field label="Isi konseling" for="counseling_content">
                    <textarea id="counseling_content" name="counseling_content" class="g-input">{{ old('counseling_content', $record->counseling_content) }}</textarea>
                </x-guru.field>
                <x-guru.field label="Hasil" for="counseling_result">
                    <textarea id="counseling_result" name="counseling_result" class="g-input">{{ old('counseling_result', $record->counseling_result) }}</textarea>
                </x-guru.field>
            </div>

            <x-guru.field label="Rencana tindak lanjut" for="follow_up_plan">
                <textarea id="follow_up_plan" name="follow_up_plan" class="g-input">{{ old('follow_up_plan', $record->follow_up_plan) }}</textarea>
            </x-guru.field>
            <x-guru.field label="Status" for="status">
                <select id="status" name="status" class="g-input">
                    @foreach (['new' => 'Baru', 'in_progress' => 'Diproses', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('status', $record->status ?? 'new') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </x-guru.field>
            <x-guru.field label="Lampiran privat (maks. 5)" for="attachments">
                <input id="attachments" type="file" multiple name="attachments[]" class="g-input">
            </x-guru.field>
        </x-guru.card>

        <x-guru.button type="submit" class="g-btn--block">Simpan</x-guru.button>
    </form>

    <x-slot:scripts>
        <script>
            (function () {
                const type = document.querySelector('#record_type');
                const violation = document.querySelector('#violation');
                const counseling = document.querySelector('#counseling');
                const category = document.querySelector('#category_id');
                function sync() {
                    violation.hidden = type.value !== 'violation';
                    counseling.hidden = type.value !== 'counseling';
                    [...category.options].forEach((option) => { option.hidden = option.dataset.type && option.dataset.type !== type.value; });
                }
                type.onchange = sync;
                category.onchange = () => {
                    const option = category.selectedOptions[0];
                    if (option.dataset.severity) document.querySelector('#severity').value = option.dataset.severity;
                };
                sync();
            })();
        </script>
    </x-slot:scripts>
</x-layouts.mobile>
```

- [ ] **Step 5: Tulis ulang `bk/show.blade.php`**

```blade
@php
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Diproses', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
    $methods = ['phone' => 'Telepon', 'whatsapp' => 'WhatsApp', 'meeting' => 'Pertemuan', 'letter' => 'Surat', 'other' => 'Lainnya'];
@endphp

<x-layouts.mobile title="Detail BK" backUrl="{{ route('attendance.bk.index') }}">
    <x-slot:headerAction>
        <x-guru.button :href="route('attendance.bk.edit', $record)" variant="secondary" class="g-btn--sm">Edit</x-guru.button>
    </x-slot:headerAction>

    <x-guru.card>
        <div class="flex flex-wrap items-center gap-2">
            <x-guru.chip :tone="$record->record_type === 'violation' ? 'attn' : 'izin'">{{ $record->record_type === 'violation' ? 'Pelanggaran' : 'Konseling' }}</x-guru.chip>
            <x-guru.chip>{{ $statusLabels[$record->status] ?? $record->status }}</x-guru.chip>
            @if ($record->archived_at)
                <x-guru.chip tone="pending">Diarsipkan</x-guru.chip>
            @endif
        </div>
        <h2 class="g-card__title">{{ $record->student->nama_lengkap }}</h2>
        <p class="text-sm text-guru-muted">{{ $record->category?->name ?? $record->custom_topic }} · {{ $record->occurred_at->locale('id')->isoFormat('D MMM Y, HH.mm') }}</p>
    </x-guru.card>

    <x-guru.card as="div">
        <dl class="g-dl">
            @foreach (['chronology' => 'Kronologi', 'action_taken' => 'Tindakan', 'counseling_content' => 'Isi konseling', 'counseling_result' => 'Hasil', 'follow_up_plan' => 'Rencana'] as $field => $label)
                @if ($record->$field)
                    <div class="flex-col !items-start">
                        <dt>{{ $label }}</dt>
                        <dd class="!max-w-none whitespace-pre-line !text-left !font-normal">{{ $record->$field }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </x-guru.card>

    @if ($record->attachments->isNotEmpty())
        <section class="flex flex-col gap-2.5" aria-labelledby="judul-lampiran">
            <h2 id="judul-lampiran" class="g-h2">Lampiran privat</h2>
            <x-guru.list>
                @foreach ($record->attachments as $attachment)
                    <x-guru.list-item :href="route('attendance.bk.attachments.show', $attachment)" icon="paperclip" tone="neutral" :title="$attachment->original_name" />
                @endforeach
            </x-guru.list>
        </section>
    @endif

    <x-guru.card as="section" aria-labelledby="judul-tindak-lanjut">
        <h2 id="judul-tindak-lanjut" class="g-h2">Timeline tindak lanjut</h2>
        @foreach ($record->followUps->sortByDesc('followed_up_at') as $followUp)
            <div class="flex flex-col gap-1 border-t border-guru-divider pt-3 text-sm">
                <b class="g-num">{{ $followUp->followed_up_at->locale('id')->isoFormat('D MMM Y, HH.mm') }}</b>
                <p>{{ $followUp->progress_notes }}</p>
                @if ($followUp->result)
                    <p class="text-guru-muted">{{ $followUp->result }}</p>
                @endif
            </div>
        @endforeach
        <form method="POST" action="{{ route('attendance.bk.follow-ups.store', $record) }}" class="flex flex-col gap-3 border-t border-guru-divider pt-4">
            @csrf
            <x-guru.field label="Waktu" for="followed_up_at" :required="true">
                <input id="followed_up_at" type="datetime-local" name="followed_up_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="g-input">
            </x-guru.field>
            <x-guru.field label="Catatan progres" for="progress_notes" :required="true">
                <textarea id="progress_notes" name="progress_notes" required class="g-input"></textarea>
            </x-guru.field>
            <x-guru.field label="Hasil" for="result">
                <textarea id="result" name="result" class="g-input"></textarea>
            </x-guru.field>
            <x-guru.button type="submit" variant="secondary" icon="plus" class="g-btn--block">Tambah tindak lanjut</x-guru.button>
        </form>
    </x-guru.card>

    <x-guru.card as="section" aria-labelledby="judul-kontak">
        <h2 id="judul-kontak" class="g-h2">Kontak orang tua</h2>
        @foreach ($record->parentContacts->sortByDesc('contacted_at') as $contact)
            <div class="flex flex-col gap-1 border-t border-guru-divider pt-3 text-sm">
                <b>{{ $contact->contact_name }} · {{ $methods[$contact->method] ?? $contact->method }}</b>
                <p>{{ $contact->summary }}</p>
            </div>
        @endforeach
        <form method="POST" action="{{ route('attendance.bk.parent-contacts.store', $record) }}" class="flex flex-col gap-3 border-t border-guru-divider pt-4">
            @csrf
            <x-guru.field label="Waktu" for="contacted_at" :required="true">
                <input id="contacted_at" type="datetime-local" name="contacted_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="g-input">
            </x-guru.field>
            <x-guru.field label="Cara" for="method">
                <select id="method" name="method" class="g-input">
                    @foreach ($methods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </x-guru.field>
            <x-guru.field label="Nama orang tua/wali" for="contact_name" :required="true">
                <input id="contact_name" name="contact_name" required class="g-input">
            </x-guru.field>
            <x-guru.field label="Ringkasan" for="summary" :required="true">
                <textarea id="summary" name="summary" required class="g-input"></textarea>
            </x-guru.field>
            <x-guru.button type="submit" variant="secondary" icon="plus" class="g-btn--block">Tambah kontak</x-guru.button>
        </form>
    </x-guru.card>

    <form method="POST" action="{{ route($record->archived_at ? 'attendance.bk.restore' : 'attendance.bk.archive', $record) }}">
        @csrf
        @method('PATCH')
        <x-guru.button type="submit" variant="secondary" class="g-btn--block">{{ $record->archived_at ? 'Pulihkan' : 'Arsipkan' }}</x-guru.button>
    </form>
</x-layouts.mobile>
```

- [ ] **Step 6: Ganti class di kedua partial combobox**

Lakukan penggantian persis ini di `bk/partials/student-combobox.blade.php` dan `bk/partials/related-students-combobox.blade.php`. Atribut dan logika Alpine tidak diubah.

| Lama | Baru |
|---|---|
| `class="block text-xs font-bold"` (label/span judul) | `class="g-label"` |
| `theme-input mt-2 flex min-h-12 w-full` | `g-input mt-2 flex w-full` |
| `solid-panel absolute z-40 mt-2 w-full rounded-2xl p-2 shadow-xl` | `absolute z-40 mt-2 w-full rounded-2xl border border-guru-border bg-guru-surface p-2 shadow-xl` |
| `solid-panel absolute z-30 mt-2 w-full rounded-2xl p-2 shadow-xl` | `absolute z-30 mt-2 w-full rounded-2xl border border-guru-border bg-guru-surface p-2 shadow-xl` |
| `theme-input w-full rounded-xl py-3 pl-10 pr-3 text-xs` | `g-input pl-10` |
| `theme-input w-full rounded-xl p-3 text-xs` | `g-input` |
| `theme-text-muted` (semua kemunculan) | `text-guru-muted` |
| `'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'hover:bg-emerald-500/10'` | `'bg-guru-primary-soft text-guru-primary' : 'hover:bg-guru-surface-2'` |
| `hover:bg-emerald-500/10` (tombol opsi siswa terkait) | `hover:bg-guru-surface-2` |
| `rounded-full bg-emerald-500/15 px-3 text-xs font-bold` | `rounded-full bg-guru-primary-soft px-3 text-xs font-bold text-guru-primary` |
| `rounded border border-emerald-500" :class="selectedIds.includes(student.id) ? 'bg-emerald-500 text-white' : ''"` | `rounded border border-guru-primary" :class="selectedIds.includes(student.id) ? 'bg-guru-primary text-guru-surface' : ''"` |
| `<p class="mt-1 text-xs text-red-500">` | `<p class="g-error">` |

Setelah itu jalankan `grep -n "theme-\|solid-panel\|emerald" resources/views/attendance/bk/partials/*.blade.php`. Expected: tidak ada hasil.

- [ ] **Step 7: Hapus view mati, jalankan test, commit**

```bash
git rm resources/views/attendance/bk/create.blade.php
php artisan test tests/Feature/Employee/BkPagesTest.php tests/Feature/Feature/BkAccessTest.php tests/Feature/Kesiswaan
git add resources/views/attendance/bk tests/Feature/Employee/BkPagesTest.php
git commit -m "feat(guru): halaman BK gaya baru

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

Expected: PASS.

---

### Task 12: Kesiswaan dan rujukan

**Files:**
- Modify (tulis ulang): `resources/views/attendance/kesiswaan/index.blade.php`, `show.blade.php`, `my-referrals.blade.php`, `referral-queue.blade.php`
- Test: `tests/Feature/Kesiswaan/StudentReferralTest.php` dan `StudentAffairsAccessTest.php` (tetap hijau), `tests/Feature/Employee/KesiswaanPagesTest.php`

**Interfaces:**
- Consumes:
  - `$students` (paginator) di index; `$student`, `$summary` (`active_count`, `types`, `statuses`, `needs_follow_up`), dan `$referrals` (paginator) di show; `$referrals` di my-referrals dan referral-queue.
  - Enum `StudentReferralStatus` (`new`, `in_handling`, `completed`, `rejected`) dan `StudentReferralUrgency` (`normal`, `important`, `urgent`).
- Wajib dipertahankan (StudentReferralTest):
  - index: `<x-layouts.mobile`, `data-kesiswaan-list="mobile"`, `data-kesiswaan-hero="directory"`, `data-kesiswaan-search="students"`, `Cakupan siswa`, `Direktori siswa`, `id="student-directory"`, `data-kesiswaan-pagination="stable"`, `links('pagination::simple-tailwind')`;
  - show: `data-kesiswaan-design="profile-centered"`, `data-profile-hero="student"`, `data-profile-summary="student"`, `Informasi pribadi`, `Ringkasan BK`, `Buat rujukan ke Guru BK`.

- [ ] **Step 1: Tulis test yang gagal**

```php
<?php

// tests/Feature/Employee/KesiswaanPagesTest.php

test('kesiswaan views use the guru components and translate raw values', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/kesiswaan/{$view}.blade.php"));

    expect($template)
        ->not->toContain('solid-panel')
        ->not->toContain('theme-')
        ->not->toContain('border-l-4')
        ->not->toContain('strtoupper($referral->urgency->value)')
        ->not->toContain("ucfirst(str_replace('_', ' ', \$referral->status->value))");
})->with(['index', 'show', 'my-referrals', 'referral-queue']);
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Employee/KesiswaanPagesTest.php`
Expected: FAIL.

- [ ] **Step 3: Tulis ulang `kesiswaan/index.blade.php`**

```blade
<x-layouts.mobile title="Kesiswaan" backUrl="{{ route('attendance.dashboard') }}">
    <div class="flex flex-col gap-5" data-kesiswaan-list="mobile">
        <x-guru.card as="section" data-kesiswaan-hero="directory">
            <div class="flex items-start gap-3">
                <span class="g-list__icon size-11 rounded-[14px]"><x-guru.icon name="school" :size="22" /></span>
                <div class="min-w-0">
                    <span class="g-card__caps">Petugas kesiswaan</span>
                    <h2 class="g-card__title">Direktori siswa</h2>
                    <p class="mt-1 text-sm text-guru-muted">Buka profil siswa dan pantau ringkasan penanganan sesuai kewenangan Anda.</p>
                </div>
            </div>
            <dl class="g-dl">
                <div><dt>Cakupan siswa</dt><dd>{{ strtoupper(auth()->user()->office?->school_level ?? '-') }}</dd></div>
                <div><dt>Hasil ditemukan</dt><dd>{{ $students->total() }} siswa</dd></div>
            </dl>
        </x-guru.card>

        <form data-kesiswaan-search="students" method="GET" action="{{ route('attendance.kesiswaan.index') }}" class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <div class="g-search min-w-0 flex-1">
                    <label for="student-search" class="sr-only">Cari nama, NISN, atau NIK</label>
                    <x-guru.icon name="search" :size="18" />
                    <input id="student-search" name="search" value="{{ request('search') }}" placeholder="Nama, NISN, atau NIK" class="g-input">
                </div>
                <button type="submit" class="g-btn g-btn--primary size-[52px] flex-none p-0" aria-label="Cari siswa"><x-guru.icon name="search" /></button>
            </div>
            @if (request('search'))
                <div class="flex items-center justify-between gap-3 text-xs">
                    <p class="truncate text-guru-muted">Hasil untuk “{{ request('search') }}”</p>
                    <a href="{{ route('attendance.kesiswaan.index') }}" class="flex min-h-11 items-center font-bold">Hapus pencarian</a>
                </div>
            @endif
        </form>

        <section id="student-directory" class="flex scroll-mt-4 flex-col gap-2.5" aria-labelledby="judul-direktori">
            <div class="flex items-end justify-between gap-3">
                <h2 id="judul-direktori" class="g-h2">Siswa dalam cakupan</h2>
                <span class="g-num text-xs font-bold text-guru-muted">{{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }}</span>
            </div>

            @if ($students->isEmpty())
                <x-guru.empty icon="search" title="Siswa tidak ditemukan">Ubah kata pencarian atau hapus pencarian untuk melihat semua siswa dalam cakupan.</x-guru.empty>
            @else
                <x-guru.list>
                    @foreach ($students as $student)
                        @php
                            $initials = collect(explode(' ', $student->nama_lengkap))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
                        @endphp
                        <a href="{{ route('attendance.kesiswaan.show', $student) }}" class="g-list__item">
                            <span class="g-avatar g-avatar--sm" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                            <span class="g-list__body">
                                <span class="g-list__title truncate">{{ $student->nama_lengkap }}</span>
                                <span class="g-list__desc truncate">{{ $student->schoolClass?->name ?? 'Belum memiliki kelas' }} · NISN {{ $student->nisn ?: '-' }}</span>
                            </span>
                            <x-guru.icon name="chevron" :size="18" class="g-list__chev" />
                        </a>
                    @endforeach
                </x-guru.list>
            @endif

            @if ($students->hasPages())
                <div data-kesiswaan-pagination="stable" class="g-pager">
                    {{ $students->links('pagination::simple-tailwind') }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.mobile>
```

- [ ] **Step 4: Tulis ulang `kesiswaan/show.blade.php`**

```blade
@php
    $assignment = $student->schoolClass?->homeroomAssignments?->first();
    $initials = collect(explode(' ', $student->nama_lengkap))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $typeLabels = ['violation' => 'Pelanggaran', 'counseling' => 'Konseling'];
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Dalam penanganan', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
    $referralStatusLabels = ['new' => 'Baru', 'in_handling' => 'Ditangani', 'completed' => 'Selesai', 'rejected' => 'Ditolak'];
@endphp

<x-layouts.mobile title="Profil Siswa" backUrl="{{ route('attendance.kesiswaan.index') }}">
    <div class="flex flex-col gap-5" data-kesiswaan-design="profile-centered">
        <x-guru.card as="section" data-profile-hero="student">
            <div class="flex items-center gap-4">
                <span class="g-avatar size-16 rounded-[20px] text-lg" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-guru.chip tone="ok">{{ $student->status ?? 'Siswa' }}</x-guru.chip>
                        <x-guru.chip>{{ strtoupper($student->school_level) }}</x-guru.chip>
                    </div>
                    <h2 class="g-card__title mt-2">{{ $student->nama_lengkap }}</h2>
                    <p class="text-xs text-guru-muted">NISN {{ $student->nisn ?: 'belum tersedia' }}</p>
                </div>
            </div>
            <div data-profile-summary="student" class="g-stats">
                <div class="g-stat"><span class="g-stat__value text-[22px]">{{ $student->schoolClass?->name ?? '-' }}</span><span class="g-stat__label">Kelas</span></div>
                <x-guru.stat :value="$summary['active_count']" label="BK aktif" tone="primary" />
                <x-guru.stat :value="$referrals->total()" label="Rujukan" />
            </div>
        </x-guru.card>

        <x-guru.card as="section" aria-labelledby="judul-akademik">
            <h2 id="judul-akademik" class="g-h2">Informasi akademik</h2>
            <dl class="g-dl">
                <div><dt>Kelas aktif</dt><dd>{{ $student->schoolClass?->name ?? '-' }}</dd></div>
                <div><dt>Wali kelas</dt><dd>{{ $assignment?->teacher?->name ?? '-' }}</dd></div>
                <div><dt>Tahun ajaran</dt><dd>{{ $assignment?->academicYear?->name ?? '-' }}</dd></div>
            </dl>
        </x-guru.card>

        <x-guru.card as="section" aria-labelledby="judul-pribadi">
            <h2 id="judul-pribadi" class="g-h2">Informasi pribadi</h2>
            <dl class="g-dl">
                <div><dt>NIK</dt><dd class="break-all">{{ $student->nik ?: '-' }}</dd></div>
                <div><dt>Tempat, tanggal lahir</dt><dd>{{ $student->tempat_lahir ?: '-' }}{{ $student->tanggal_lahir ? ', '.$student->tanggal_lahir->translatedFormat('d F Y') : '' }}</dd></div>
                <div><dt>Telepon</dt><dd>{{ $student->no_telepon ?: '-' }}</dd></div>
                <div><dt>Alamat</dt><dd>{{ $student->alamat ?: '-' }}</dd></div>
            </dl>
        </x-guru.card>

        <x-guru.card as="section" aria-labelledby="judul-ringkasan-bk">
            <div class="flex items-center justify-between gap-3">
                <h2 id="judul-ringkasan-bk" class="g-h2">Ringkasan BK</h2>
                <x-guru.chip tone="ok">{{ $summary['active_count'] }} aktif</x-guru.chip>
            </div>
            <p class="text-xs text-guru-muted">Hanya informasi umum yang aman ditampilkan.</p>
            <dl class="g-dl">
                <div><dt>Jenis catatan</dt><dd>{{ collect($summary['types'])->map(fn ($type) => $typeLabels[$type] ?? ucfirst($type))->implode(', ') ?: '-' }}</dd></div>
                <div><dt>Status penanganan</dt><dd>{{ collect($summary['statuses'])->map(fn ($status) => $statusLabels[$status] ?? $status)->implode(', ') ?: '-' }}</dd></div>
                <div><dt>Perlu tindak lanjut</dt><dd @class(['text-guru-late' => $summary['needs_follow_up'], 'text-guru-primary' => ! $summary['needs_follow_up']])>{{ $summary['needs_follow_up'] ? 'Ya' : 'Tidak' }}</dd></div>
            </dl>
        </x-guru.card>

        @can('create', App\Models\StudentReferral::class)
            <x-guru.button :href="route('attendance.kesiswaan.referrals.create', $student)" icon="send" class="g-btn--block">Buat rujukan ke Guru BK</x-guru.button>
        @endcan

        <section class="flex flex-col gap-2.5" aria-labelledby="judul-riwayat-rujukan">
            <div class="flex items-end justify-between gap-3">
                <h2 id="judul-riwayat-rujukan" class="g-h2">Riwayat rujukan</h2>
                <span class="g-num text-xs font-bold text-guru-muted">{{ $referrals->total() }}</span>
            </div>
            @if ($referrals->isEmpty())
                <x-guru.empty icon="send" title="Belum ada rujukan">Rujukan siswa akan tampil di bagian ini.</x-guru.empty>
            @else
                <x-guru.list>
                    @foreach ($referrals as $referral)
                        <x-guru.list-item :href="route('attendance.kesiswaan.referrals.show', $referral)" icon="send" tone="attn" :title="$referral->reason"
                            :desc="$referral->observed_at?->translatedFormat('d M Y').' · '.($referral->counselor?->name ?? 'Belum ditangani')">
                            <x-slot:end>
                                <x-guru.chip>{{ $referralStatusLabels[$referral->status->value] ?? $referral->status->value }}</x-guru.chip>
                            </x-slot:end>
                        </x-guru.list-item>
                    @endforeach
                </x-guru.list>
            @endif
            @if ($referrals->hasPages())
                <div class="g-pager">{{ $referrals->links('pagination::simple-tailwind') }}</div>
            @endif
        </section>
    </div>
</x-layouts.mobile>
```

- [ ] **Step 5: Tulis ulang `kesiswaan/my-referrals.blade.php`**

```blade
@php
    $statusLabels = ['new' => 'Baru', 'in_handling' => 'Ditangani', 'completed' => 'Selesai', 'rejected' => 'Ditolak'];
    $statusTones = ['new' => 'pending', 'in_handling' => 'izin', 'completed' => 'ok', 'rejected' => 'attn'];
@endphp

<x-layouts.mobile title="Rujukan Saya" backUrl="{{ route('attendance.dashboard') }}">
    <p class="text-sm text-guru-muted">Pantau rujukan yang Anda kirim ke guru BK.</p>

    @if ($referrals->isEmpty())
        <x-guru.empty icon="send" title="Belum ada rujukan">Buat rujukan dari profil siswa di menu Kelas.</x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($referrals as $referral)
                <x-guru.list-item :href="route('attendance.kesiswaan.referrals.show', $referral)" icon="send" tone="attn"
                    :title="$referral->student->nama_lengkap"
                    :desc="($referral->student->schoolClass?->name ?? strtoupper($referral->school_level)).' · '.$referral->reason">
                    <span class="g-list__desc">{{ $referral->counselor?->name ?? 'Belum ditangani' }} · {{ $referral->observed_at?->translatedFormat('d M Y') }}</span>
                    <x-slot:end>
                        <x-guru.chip :tone="$statusTones[$referral->status->value] ?? 'neutral'">{{ $statusLabels[$referral->status->value] ?? $referral->status->value }}</x-guru.chip>
                    </x-slot:end>
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($referrals->hasPages())
        <div class="g-pager">{{ $referrals->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
```

- [ ] **Step 6: Tulis ulang `kesiswaan/referral-queue.blade.php`**

```blade
@php
    $urgencyLabels = ['normal' => 'Biasa', 'important' => 'Penting', 'urgent' => 'Mendesak'];
    $urgencyTones = ['normal' => 'neutral', 'important' => 'pending', 'urgent' => 'attn'];
@endphp

<x-layouts.mobile title="Antrean Rujukan" backUrl="{{ route('attendance.dashboard') }}">
    <p class="text-sm text-guru-muted">Rujukan jenjang Anda, diurutkan berdasarkan urgensi dan waktu.</p>

    @if ($referrals->isEmpty())
        <x-guru.empty icon="inbox" title="Antrean kosong">Belum ada rujukan baru atau rujukan yang Anda tangani.</x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($referrals as $referral)
                <x-guru.list-item :href="route('attendance.kesiswaan.referrals.show', $referral)" icon="inbox"
                    :tone="$referral->urgency->value === 'urgent' ? 'attn' : 'izin'"
                    :title="$referral->student->nama_lengkap"
                    :desc="($referral->student->schoolClass?->name ?? strtoupper($referral->school_level)).' · '.$referral->reason">
                    <span class="g-list__desc">{{ $referral->status->value === 'new' ? 'Belum ditangani' : ($referral->counselor?->name ?? 'Dalam penanganan') }} · {{ $referral->observed_at?->translatedFormat('d M Y') }}</span>
                    <x-slot:end>
                        <x-guru.chip :tone="$urgencyTones[$referral->urgency->value] ?? 'neutral'">{{ $urgencyLabels[$referral->urgency->value] ?? $referral->urgency->value }}</x-guru.chip>
                    </x-slot:end>
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($referrals->hasPages())
        <div class="g-pager">{{ $referrals->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
```

- [ ] **Step 7: Jalankan test, commit**

Run: `php artisan test tests/Feature/Employee/KesiswaanPagesTest.php tests/Feature/Kesiswaan`
Expected: PASS.

```bash
git add resources/views/attendance/kesiswaan tests/Feature/Employee/KesiswaanPagesTest.php
git commit -m "feat(guru): halaman kesiswaan dan rujukan gaya baru

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

### Task 13: Halaman offline, pembersihan, dan verifikasi visual

**Files:**
- Modify (tulis ulang): `public/offline.html`
- Modify: `public/sw.js` (baris 1: `CACHE_NAME` v6 → v7, supaya `/offline` yang baru di-precache ulang)
- Create (tidak di-commit): `storage/app/visual/shoot.mjs`
- Create: `docs/screenshots/redesign-guru/*.png`
- Test: `tests/Feature/Guru/GuruCleanupTest.php`

- [ ] **Step 1: Tulis test pembersihan yang gagal**

```php
<?php

// tests/Feature/Guru/GuruCleanupTest.php

use Illuminate\Support\Facades\File;

test('teacher views no longer carry the previous design classes', function () {
    $legacy = ['glass-card', 'theme-text-', 'theme-input', 'theme-btn-submit', 'solid-panel', 'font-outfit', 'animate-stagger', 'pwa-m3'];
    // Di luar cakupan: kamera lama (x-layouts.app, tidak dipakai route) dan tiga halaman kesiswaan ber-layout admin.
    $skip = ['create.blade.php', 'notifications.blade.php', 'referral-form.blade.php', 'referral.blade.php'];

    $paths = collect(File::allFiles(resource_path('views/attendance')))
        ->reject(fn ($file) => in_array($file->getFilename(), $skip, true))
        ->map(fn ($file) => $file->getPathname())
        ->push(resource_path('views/components/layouts/mobile.blade.php'));

    $offenders = $paths
        ->filter(fn (string $path) => str(file_get_contents($path))->contains($legacy))
        ->map(fn (string $path) => str_replace(resource_path('views/'), '', $path))
        ->values()
        ->all();

    expect($offenders)->toBe([]);
});

test('the offline page uses the guru palette', function () {
    $html = file_get_contents(public_path('offline.html'));

    expect($html)->toContain('#F4F1EA')->toContain('#1E5A48')->toContain('prefers-color-scheme: dark')->toContain('prefers-reduced-motion');
});
```

- [ ] **Step 2: Jalankan test, pastikan hasilnya sesuai**

Run: `php artisan test tests/Feature/Guru/GuruCleanupTest.php`
Expected: test offline FAIL. Test class lama harus sudah PASS bila Task 6–12 selesai; kalau FAIL, daftar `$offenders` menunjukkan berkas yang masih perlu ditulis ulang.

- [ ] **Step 3: Tulis ulang `public/offline.html`**

```html
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#F4F1EA">
    <title>Offline · absenKU</title>
    <style>
        :root {
            --ground: #F4F1EA; --surface: #FFFFFF; --ink: #1B2420; --muted: #5E665F; --border: #E3DDD0;
            --primary: #1E5A48; --primary-soft: #DDE9E2; --attn-soft: #F5E1D7; --attn-ink: #8A361E;
            color-scheme: light;
        }
        @media (prefers-color-scheme: dark) {
            :root { --ground: #111512; --surface: #1A201C; --ink: #ECE8DF; --muted: #A3ABA4; --border: #2E3631; --primary-soft: #22382F; --attn-soft: #3A241C; --attn-ink: #F0A58B; color-scheme: dark; }
        }
        * { box-sizing: border-box; margin: 0; }
        body {
            min-height: 100svh; display: grid; place-items: center; padding: 24px 20px;
            background: var(--ground); color: var(--ink);
            font: 15px/1.5 "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
        }
        main { width: 100%; max-width: 24rem; display: flex; flex-direction: column; gap: 20px; }
        .card { display: flex; flex-direction: column; gap: 16px; padding: 24px 20px; border-radius: 24px; background: var(--surface); border: 1px solid var(--border); text-align: center; }
        .icon { width: 56px; height: 56px; margin: 0 auto; display: grid; place-items: center; border-radius: 16px; background: var(--primary-soft); color: var(--primary); }
        h1 { font-family: Fraunces, Georgia, serif; font-size: 26px; font-weight: 600; }
        p { color: var(--muted); }
        .status { display: inline-flex; align-self: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--attn-soft); color: var(--attn-ink); font-size: 12px; font-weight: 700; }
        button { min-height: 56px; border: 0; border-radius: 16px; background: #1E5A48; color: #FFFFFF; font: inherit; font-size: 16px; font-weight: 800; cursor: pointer; }
        button:focus-visible { outline: 3px solid var(--primary); outline-offset: 2px; }
        ul { padding: 0; list-style: none; text-align: left; display: grid; gap: 8px; color: var(--muted); font-size: 14px; }
        li::before { content: "·"; margin-right: 8px; color: var(--primary); font-weight: 800; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
    </style>
</head>
<body>
    <main>
        <div class="card">
            <div class="icon" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M8.5 16.5a5 5 0 0 1 7 0M5 13a10 10 0 0 1 5-2.8M19 13a10 10 0 0 0-2.7-2M2 9.5a15 15 0 0 1 4.6-2.8M22 9.5A15 15 0 0 0 12 5.5"/><path d="M12 20h.01"/></svg>
            </div>
            <h1>Anda sedang offline</h1>
            <p>absenKU butuh internet untuk mencatat absen. Periksa koneksi, lalu coba lagi.</p>
            <span class="status" role="status">Tidak ada koneksi internet</span>
            <button type="button" onclick="location.reload()">Coba Lagi</button>
        </div>
        <ul>
            <li>Periksa Wi-Fi atau data seluler.</li>
            <li>Nyalakan lalu matikan mode pesawat.</li>
            <li>Halaman dimuat ulang otomatis saat koneksi kembali.</li>
        </ul>
    </main>
    <script>
        window.addEventListener('online', () => location.reload());
    </script>
</body>
</html>
```

- [ ] **Step 4: Naikkan versi cache service worker**

Di `public/sw.js`, ganti `const CACHE_NAME = 'absensi-selfie-geo-v6';` dengan `const CACHE_NAME = 'absensi-selfie-geo-v7';`. Lalu jalankan `grep -n "absensi-selfie-geo" tests -r`, dan kalau ada test yang mengecek versi lama, sesuaikan.

- [ ] **Step 5: Verifikasi otomatis lengkap**

```bash
./vendor/bin/pint --dirty
./vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw | grep -c .    # harus 132
npm run build 2>&1 | grep -E "built|rror"
php artisan test 2>&1 | tail -3
```

Expected: Pint tanpa perubahan, PHPStan 132, `✓ built`, dan semua test PASS.

- [ ] **Step 6: Siapkan akun guru sementara di database lokal**

Server lokal: `php artisan serve` di `http://localhost:8000`, atau Herd/Valet yang sudah berjalan. Jalankan `php artisan migrate` bila kolom `liveness_verified` belum ada.

```bash
php artisan tinker --execute='
$role = App\Models\Role::firstOrCreate(["slug" => "guru"], ["name" => "Guru", "is_admin" => false]);
$office = App\Models\Office::first();
$u = App\Models\User::create(["name" => "Guru Uji Visual", "email" => "visual.tmp@example.test", "password" => "VisualUji12345", "role_id" => $role->id, "office_id" => $office?->id]);
$u->forceFill(["email_verified_at" => now()])->save();
$year = App\Models\AcademicYear::getActive();
$class = App\Models\SchoolClass::query()->first();
if ($year && $class) { App\Models\HomeroomAssignment::create(["academic_year_id" => $year->id, "school_class_id" => $class->id, "teacher_id" => $u->id]); }
echo $u->id;'
```

- [ ] **Step 7: Buat alat screenshot `storage/app/visual/shoot.mjs` (tidak di-commit)**

```js
// Headless Chrome via CDP (Node 22: WebSocket dan fetch bawaan). Kamera palsu dan lokasi palsu.
import { spawn } from 'node:child_process';
import { mkdir, writeFile } from 'node:fs/promises';

const BASE = process.env.BASE ?? 'http://localhost:8000';
const OUT = process.env.OUT ?? 'docs/screenshots/redesign-guru';
const CHROME = process.env.CHROME ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const PAGES = [
    ['beranda', '/attendance/dashboard'], ['absen-masuk', '/attendance/selfie'], ['riwayat', '/attendance/history'],
    ['izin', '/attendance/leaves'], ['izin-baru', '/attendance/leaves/create'], ['profil', '/attendance/profile'], ['kelas', '/attendance/my-class'],
];
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

await mkdir(OUT, { recursive: true });
const chrome = spawn(CHROME, ['--headless=new', '--remote-debugging-port=9333', '--use-fake-device-for-media-stream', '--use-fake-ui-for-media-stream',
    '--no-first-run', '--no-default-browser-check', `--user-data-dir=${process.cwd()}/storage/app/visual/profile`, 'about:blank'], { stdio: 'ignore' });
await sleep(2000);

function connect(url) {
    const ws = new WebSocket(url);
    let id = 0;
    const waiting = new Map();
    ws.addEventListener('message', (e) => { const m = JSON.parse(e.data); if (waiting.has(m.id)) { waiting.get(m.id)(m.result ?? m); waiting.delete(m.id); } });
    const ready = new Promise((r) => ws.addEventListener('open', r, { once: true }));
    return { ready, send: (method, params = {}) => new Promise((r) => { const i = ++id; waiting.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); }) };
}

const browser = connect((await (await fetch('http://127.0.0.1:9333/json/version')).json()).webSocketDebuggerUrl);
await browser.ready;
await browser.send('Browser.grantPermissions', { origin: BASE, permissions: ['videoCapture', 'geolocation'] });
const page = connect((await (await fetch('http://127.0.0.1:9333/json')).json()).find((t) => t.type === 'page').webSocketDebuggerUrl);
await page.ready;
await page.send('Page.enable');
await page.send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 2, mobile: true });
await page.send('Emulation.setGeolocationOverride', { latitude: -6.2, longitude: 106.8, accuracy: 12 });
const run = async (expression) => (await page.send('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true })).result?.value;
const go = async (path) => { await page.send('Page.navigate', { url: BASE + path }); await sleep(3500); };

await go('/login');
await run(`(() => { const f = document.querySelector('form'); f.querySelector('[name=email]').value = 'visual.tmp@example.test'; f.querySelector('[name=password]').value = 'VisualUji12345'; f.submit(); })()`);
await sleep(3500);

const report = [];
for (const theme of ['light', 'dark']) {
    await page.send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-color-scheme', value: theme }] });
    await run(`localStorage.removeItem('appearance'); localStorage.removeItem('welcome-theme'); true`);
    for (const [name, path] of PAGES) {
        await go(path);
        const height = await run('Math.ceil(document.documentElement.scrollHeight)');
        const overflow = await run('document.documentElement.scrollWidth > window.innerWidth');
        const shot = await page.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: { x: 0, y: 0, width: 390, height, scale: 1 } });
        await writeFile(`${OUT}/${name}-${theme}.png`, Buffer.from(shot.data, 'base64'));
        report.push(`${name}-${theme}: tinggi ${height}px, overflow horizontal: ${overflow}`);
    }
}
console.log(report.join('\n'));
chrome.kill();
```

Run: `node storage/app/visual/shoot.mjs`
Expected: 14 PNG di `docs/screenshots/redesign-guru/`, dan setiap baris laporan berisi `overflow horizontal: false`.

- [ ] **Step 8: Screenshot referensi desain**

```bash
mkdir -p storage/app/visual/design
python3 - <<'PY'
import re, json, base64, gzip
s = open("AbsenKU Guru — Alternatif PWA – Beranda.html", encoding="utf-8").read()
block = lambda t: re.search(r'<script type="__bundler/' + t + r'">(.*?)</script>', s, re.S).group(1)
manifest, template = json.loads(block('manifest')), json.loads(block('template'))
ext = {"text/javascript": "js", "font/woff2": "woff2"}
for uuid, entry in manifest.items():
    data = base64.b64decode(entry["data"])
    if str(entry.get("compressed")) == "True":
        data = gzip.decompress(data)
    name = f"{uuid}.{ext.get(entry['mime'], 'bin')}"
    open(f"storage/app/visual/design/{name}", "wb").write(data)
    template = template.replace(uuid, name)
open("storage/app/visual/design/index.html", "w").write(template)
PY
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless=new --window-size=390,1400 --hide-scrollbars \
  --screenshot="$PWD/docs/screenshots/redesign-guru/desain-referensi.png" "file://$PWD/storage/app/visual/design/index.html"
```

Expected: `docs/screenshots/redesign-guru/desain-referensi.png` menampilkan Beranda desain.

- [ ] **Step 9: Ronde cek visual 1**

Buka `beranda-light.png` berdampingan dengan `desain-referensi.png`, lalu bandingkan per bagian:
- header;
- baris tanggal;
- kartu hero: warna `#1E5A48`, jam Fraunces 44px, bilah progres amber, tombol putih;
- kartu rekap: tiga angka dan bilah bertumpuk;
- kartu kelas wali;
- daftar Layanan (baris 64px, ikon 40px);
- carousel Informasi;
- nav 4 tab dengan pil aktif.

Lalu periksa semua screenshot gelap untuk kontras, elemen yang hilang, dan teks yang terpotong. Catat semua cacat, perbaiki dalam **satu** batch, lalu jalankan ulang Step 5.

- [ ] **Step 10: Ronde cek visual 2 (konfirmasi, terakhir)**

Run: `node storage/app/visual/shoot.mjs`
Expected: semua cacat dari ronde 1 teratasi dan tidak ada overflow. Jangan membuka ronde ketiga; sisa temuan dicatat di laporan akhir.

- [ ] **Step 11: Hapus akun sementara dan commit**

```bash
php artisan tinker --execute='$u = App\Models\User::where("email", "visual.tmp@example.test")->first(); App\Models\HomeroomAssignment::where("teacher_id", $u?->id)->delete(); DB::table("sessions")->where("user_id", $u?->id)->delete(); $u?->delete(); echo "ok";'
git add public/offline.html public/sw.js tests/Feature/Guru/GuruCleanupTest.php docs/screenshots/redesign-guru public/build
git commit -m "feat(guru): halaman offline baru, pembersihan, dan bukti visual

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

---

## Self-Review

- **Cakupan spec:**
  - §2 → Task 1–2; §3 → Task 3 dan 5 (penghapusan skin serta layout duplikat);
  - §4.1 → Task 4; §4.2–4.3 → Task 5;
  - §5 → Task 6–12 (setiap berkas di tabel §5; `attendance/create.blade.php` dan tiga halaman kesiswaan ber-layout admin sengaja tidak disentuh), halaman offline → Task 13;
  - §6 → Task 6 (script tidak diubah, kantor terkunci, foto manual), Task 5 (ganti akun, PWA), Task 3 (tema);
  - §7 → Task 3–13 beserta Step 5–10 di Task 13.
- **Penyimpangan dari spec yang disengaja:**
  - daftar izin guru tanpa filter status (sudah dicatat di spec);
  - label hero diperbarui saat PWA dibuka kembali, bukan tiap 60 detik (spec diperbarui di Task 5 Step 7);
  - dua view mati dihapus (`bk/create`, `my-class/violation`).
- **Konsistensi nama:**
  - kelas CSS `g-*` di Task 1 dipakai persis di Task 2–12;
  - properti `TodayPresence` (`status`, `late`, `checkIn`, `checkOut`, `scheduleStart`, `scheduleEnd`, `progress`, `progressText`, `locationText`, `action`) konsisten antara Task 4 dan Task 5;
  - method `monthlyOnTime()` dan `monthlyRecorded()` konsisten antara Task 4 dan Task 5.
- **Review Focus:**
  - (1) hari kerja nol → BerandaTest "a day without a schedule";
  - (2) izin lintas bulan dan tumpang tindih → DashboardDataTest;
  - (3) tanpa jadwal atau tanpa tahun ajaran → TodayPresenceTest dan BerandaTest;
  - (4) jendela masuk ditutup → TodayPresenceTest;
  - (5) tema dengan key lama atau tanpa preferensi → TeacherAttendanceVisualSystemTest.
