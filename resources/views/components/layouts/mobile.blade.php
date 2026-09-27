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
