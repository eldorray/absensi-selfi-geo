@props([
    'title' => 'Absensi',
    'backUrl' => null,
    'activeTab' => null,
    'showNav' => true,
    // Halaman modal: kartu sheet ala iOS, naik dari bawah, tombol tutup, tanpa nav bawah.
    'isSheet' => false,
])

@php
    $branding = \App\Models\ApplicationSetting::current();
    $isSheet = filter_var($isSheet, FILTER_VALIDATE_BOOL);
    $showNav = filter_var($showNav, FILTER_VALIDATE_BOOL) && ! $isSheet;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="color-scheme" content="light dark">
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

    <script>
        // Transisi halaman gaya iOS: halaman asal mencatat jenisnya saat tautan diketuk
        // ('push' | 'pop' | 'tab' | 'sheet' | 'dismiss'), lalu kedua halaman memasangnya
        // sebagai jenis view transition (CSS di guru.css).
        const guruIsSheet = @js($isSheet);
        const guruNavType = () => {
            try { return sessionStorage.getItem('guruNav'); } catch (err) { return null; }
        };
        document.addEventListener('click', (e) => {
            const a = e.target.closest?.('a[href]');
            if (! a || e.defaultPrevented || a.target || a.origin !== location.origin) return;
            const type = a.dataset.nav ?? (a.closest('.g-nav') ? 'tab' : 'push');
            try { sessionStorage.setItem('guruNav', type); } catch (err) {}
        });
        // Halaman asal: jenis dipasang sebelum cuplikan lama diambil, supaya nav ikut tertutup sheet.
        // Meninggalkan sheet tanpa mengetuk tautan (kirim form, tombol kembali sistem) = 'dismiss'.
        window.addEventListener('pageswap', (e) => {
            let type = guruNavType();
            if (! type && guruIsSheet) {
                type = 'dismiss';
                try { sessionStorage.setItem('guruNav', type); } catch (err) {}
            }
            if (type) e.viewTransition?.types.add(type);
        });
        window.addEventListener('pagereveal', (e) => {
            let type = guruNavType();
            try { sessionStorage.removeItem('guruNav'); } catch (err) {}
            if (! e.viewTransition) return;
            // ponytail: mundur dan maju riwayat sama-sama 'pop'; PWA terpasang tak punya tombol maju.
            if (performance.getEntriesByType('navigation')[0]?.type === 'back_forward' && type !== 'dismiss') type = 'pop';
            // Sheet yang tampil lagi (galat validasi) tidak menutup dirinya sendiri.
            if (guruIsSheet && type === 'dismiss') type = null;
            // Tanpa jenis (mis. redirect setelah kirim form): silang pudar bawaan.
            if (type) e.viewTransition.types.add(type);
        });
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @view-transition { navigation: auto; }
    </style>
</head>

<body class="guru" data-teacher-ui="absenku-guru">
    <div class="g-statusbar" aria-hidden="true"></div>
    <div @class(['g-app', 'g-app--sheet' => $isSheet])>
        @isset($header)
            {{ $header }}
        @else
            {{-- Akar tab (tanpa backUrl) tidak punya tombol kembali: pindahnya lewat nav bawah. --}}
            <header @class(['g-header', 'g-header--root' => ! $backUrl]) data-region="top-app-bar">
                @if ($backUrl)
                    <a href="{{ $backUrl }}" class="g-iconbtn" aria-label="{{ $isSheet ? 'Tutup' : 'Kembali' }}" data-nav="{{ $isSheet ? 'dismiss' : 'pop' }}">
                        <x-guru.icon :name="$isSheet ? 'x' : 'back'" />
                    </a>
                @endif
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
            @php($activeIndex = array_search($activeTab, array_column($tabs, 'key'), true))
            <nav class="g-nav" aria-label="Navigasi utama" data-region="navigation-bar" style="--g-tabs: {{ count($tabs) }}">
                @if ($activeIndex !== false)
                    <span class="g-nav__ind" style="--g-tab: {{ $activeIndex }}" aria-hidden="true"></span>
                @endif
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
