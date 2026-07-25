<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#080710">
    <meta name="description" content="Aplikasi Absensi Selfie dengan Verifikasi GPS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AbsenKu">
    <link rel="manifest" href="/manifest.json?v=3">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png?v=2">
    <title>AbsenKu - {{ auth()->user()->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- PWA Install Script -->
    <script>
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallBanner();
        });
        window.addEventListener('appinstalled', () => {
            hideInstallBanner();
            deferredPrompt = null;
        });
        function showInstallBanner() {
            const banner = document.getElementById('pwa-install-banner');
            if (banner && !localStorage.getItem('pwaInstallDismissed')) {
                banner.classList.remove('hidden');
            }
        }
        function hideInstallBanner() {
            const banner = document.getElementById('pwa-install-banner');
            if (banner) {
                banner.classList.add('hidden');
            }
        }
        window.installPWA = async function() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            hideInstallBanner();
        }
        window.dismissInstallBanner = function() {
            hideInstallBanner();
            localStorage.setItem('pwaInstallDismissed', 'true');
        }
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => console.log('SW registered:', registration.scope))
                    .catch((error) => console.log('SW registration failed:', error));
            });
        }
    </script>
    
    <style>
        :root {
            /* Colors - Dark Theme (Default) */
            --bg-color: #0d0f14;
            --screen-bg: #12141b;
            --text-main: #dfe4ec;
            --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.12);
            --glass-shadow: 0 24px 80px -15px rgba(0, 0, 0, 0.5), inset 0 1px 1px 0 rgba(255, 255, 255, 0.1);
            --glass-card-bg: rgba(255, 255, 255, 0.04);
            --glass-card-border: rgba(255, 255, 255, 0.10);
            --menu-bg: #16151f;
            --menu-border: rgba(255, 255, 255, 0.10);
            --glass-card-shadow: inset 0 1px 0 0 rgba(255, 255, 255, 0.05);
            --grid-line-color: rgba(255, 255, 255, 0.012);
            --blob-opacity: 0.12;
            --phone-shell-bg: #0a0b0f;
            --phone-shell-border: rgba(255, 255, 255, 0.12);
            --footer-icon-color: #64748b;
            --active-nav-color: #22d3ee;
            
            /* Stats colors */
            --stats-present: #10b981;
            --stats-late: #f59e0b;
            --stats-total: #06b6d4;
            --stats-card-border: rgba(255, 255, 255, 0.08);
            
            /* Navigation colors */
            --nav-btn-inactive-bg: rgba(255, 255, 255, 0.04);
            --nav-btn-inactive-border: rgba(255, 255, 255, 0.05);
            --nav-btn-inactive-text: rgba(255, 255, 255, 0.25);
            
            /* Custom Icon Backgrounds */
            --icon-riwayat-bg: rgba(6, 182, 212, 0.1);
            --icon-profil-bg: rgba(168, 85, 247, 0.1);
            --icon-password-bg: rgba(245, 158, 11, 0.1);
            --icon-perizinan-bg: rgba(16, 185, 129, 0.1);
            
            /* Statuses */
            --status-ok-text: #10b981;
            --status-ok-bg: rgba(16, 185, 129, 0.10);
            --status-ok-border: rgba(16, 185, 129, 0.20);
            --status-late-text: #f59e0b;
            --status-late-bg: rgba(245, 158, 11, 0.10);
            --status-late-border: rgba(245, 158, 11, 0.20);
        }

        body.light-theme {
            /* Colors - Light Theme (Polished iOS style) */
            --bg-color: #e4e7ee;
            --screen-bg: #eceff5;
            --text-main: #1e293b;
            --text-muted: #5b6472;
            
            --glass-bg: rgba(255, 255, 255, 0.75);
            --glass-border: rgba(255, 255, 255, 0.85);
            --glass-shadow: 0 12px 36px -8px rgba(100, 116, 139, 0.06), inset 0 1px 1px 0 rgba(255, 255, 255, 0.95);
            
            --glass-card-bg: rgba(255, 255, 255, 0.72);
            --glass-card-border: rgba(255, 255, 255, 0.65);
            --menu-bg: #ffffff;
            --menu-border: rgba(15, 23, 42, 0.10);
            --glass-card-shadow: 0 2px 10px rgba(100, 116, 139, 0.01), inset 0 1px 0 0 rgba(255, 255, 255, 0.95);
            
            --grid-line-color: rgba(0, 0, 0, 0.015);
            --blob-opacity: 0.16;
            
            --phone-shell-bg: #d8dce6;
            --phone-shell-border: rgba(0, 0, 0, 0.04);
            
            --footer-icon-color: #94a3b8;
            --active-nav-color: #6366f1;
            
            /* Stats colors */
            --stats-present: #047857;
            --stats-late: #b45309;
            --stats-total: #4f46e5;
            --stats-card-border: rgba(255, 255, 255, 0.7);
            
            /* Navigation colors */
            --nav-btn-inactive-bg: rgba(0, 0, 0, 0.02);
            --nav-btn-inactive-border: rgba(0, 0, 0, 0.04);
            --nav-btn-inactive-text: #94a3b8;
            
            /* Custom Icon Backgrounds */
            --icon-riwayat-bg: rgba(99, 102, 241, 0.08);
            --icon-profil-bg: rgba(168, 85, 247, 0.08);
            --icon-password-bg: rgba(245, 158, 11, 0.08);
            --icon-perizinan-bg: rgba(16, 185, 129, 0.08);
            
            /* Statuses - Softer greens/oranges for readability */
            --status-ok-text: #047857;
            --status-ok-bg: rgba(52, 211, 153, 0.14);
            --status-ok-border: rgba(52, 211, 153, 0.25);
            --status-late-text: #b45309;
            --status-late-bg: rgba(245, 158, 11, 0.12);
            --status-late-border: rgba(245, 158, 11, 0.22);
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            overscroll-behavior: none;
            width: 100%;
            margin: 0;
            padding: 0;
            transition: background-color 0.5s ease;
        }

        .font-display {
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Mapping classes for Theme Transitions */
        .theme-bg {
            background-color: var(--bg-color);
            transition: background-color 0.5s ease;
        }
        
        .theme-text-main {
            color: var(--text-main);
            transition: color 0.5s ease;
        }

        .theme-text-muted {
            color: var(--text-muted);
            transition: color 0.5s ease;
        }

        .phone-shell {
            background-color: var(--phone-shell-bg);
            border-color: var(--phone-shell-border);
            transition: background-color 0.5s ease, border-color 0.5s ease, box-shadow 0.5s ease;
        }

        .screen-content {
            background-color: var(--screen-bg);
            transition: background-color 0.5s ease;
        }

        .bg-grid-overlay {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(var(--grid-line-color) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line-color) 1px, transparent 1px);
            background-size: 45px 45px;
            mask-image: radial-gradient(ellipse 60% 50% at 50% 50%, #000 70%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 60% 50% at 50% 50%, #000 70%, transparent 100%);
            transition: background-image 0.5s ease;
        }

        .glass-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            transition: background 0.5s ease, border-color 0.5s ease, box-shadow 0.5s ease;
        }

        .glass-card {
            background: var(--glass-card-bg);
            border: 1px solid var(--glass-card-border);
            box-shadow: var(--glass-card-shadow);
            transition: background 0.5s ease, border-color 0.5s ease, box-shadow 0.5s ease;
        }

        /* Opaque surface for popovers/modals — readable over the busy dashboard */
        .solid-panel {
            background: var(--menu-bg);
            border: 1px solid var(--menu-border);
            box-shadow: var(--glass-shadow);
            transition: background 0.5s ease, border-color 0.5s ease;
        }

        .footer-nav-item {
            color: var(--footer-icon-color);
            transition: color 0.5s ease;
        }

        .footer-nav-item.active {
            color: var(--active-nav-color);
        }

        /* Custom Theme Borders to prevent black-line specificity issues */
        .theme-border {
            border: 1px solid var(--glass-card-border) !important;
            transition: border-color 0.5s ease;
        }

        .theme-border-t {
            border-top: 1px solid var(--glass-card-border) !important;
            transition: border-top-color 0.5s ease;
        }

        /* Dynamic Icons mapping background */
        .theme-icon-riwayat {
            background-color: var(--icon-riwayat-bg);
            transition: background-color 0.5s ease;
        }
        .theme-icon-profil {
            background-color: var(--icon-profil-bg);
            transition: background-color 0.5s ease;
        }
        .theme-icon-password {
            background-color: var(--icon-password-bg);
            transition: background-color 0.5s ease;
        }
        .theme-icon-perizinan {
            background-color: var(--icon-perizinan-bg);
            transition: background-color 0.5s ease;
        }

        /* Inactive nav buttons shape styling */
        .theme-nav-inactive {
            background-color: var(--nav-btn-inactive-bg);
            border: 1px solid var(--nav-btn-inactive-border) !important;
            color: var(--nav-btn-inactive-text);
            transition: background-color 0.5s ease, border-color 0.5s ease, color 0.5s ease;
        }
        
        /* Statuses Theme Mapping */
        .theme-status-ok-text {
            color: var(--status-ok-text);
            transition: color 0.5s ease;
        }
        .theme-status-ok-card {
            background: var(--status-ok-bg) !important;
            border: 1px solid var(--status-ok-border) !important;
            transition: background 0.5s ease, border-color 0.5s ease;
        }
        .theme-status-late-text {
            color: var(--status-late-text);
            transition: color 0.5s ease;
        }
        .theme-status-late-card {
            background: var(--status-late-bg) !important;
            border: 1px solid var(--status-late-border) !important;
            transition: background 0.5s ease, border-color 0.5s ease;
        }

        /* Sun / Moon Toggle Button Icon display */
        .theme-toggle .sun-icon { display: block; }
        .theme-toggle .moon-icon { display: none; }
        
        body.light-theme .theme-toggle .sun-icon { display: none; }
        body.light-theme .theme-toggle .moon-icon { display: block; }

        .theme-toggle {
            transition: transform 0.3s ease, background 0.5s ease, border-color 0.5s ease;
        }

        /* Custom scrollbar inside mobile screen content container */
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 9px;
        }
        body.light-theme .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.05);
        }

        /* Animated background liquid blobs */
        @keyframes float-blob {
            0% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
            33% { transform: translate(30px, -45px) scale(1.1) rotate(120deg); }
            66% { transform: translate(-25px, 25px) scale(0.95) rotate(240deg); }
            100% { transform: translate(0px, 0px) scale(1) rotate(360deg); }
        }

        @keyframes float-blob-reverse {
            0% { transform: translate(0px, 0px) scale(1) rotate(360deg); }
            33% { transform: translate(-40px, 30px) scale(0.9) rotate(240deg); }
            66% { transform: translate(25px, -25px) scale(1.05) rotate(120deg); }
            100% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
        }

        .animate-blob-1 {
            animation: float-blob 28s infinite alternate ease-in-out;
        }

        .animate-blob-2 {
            animation: float-blob-reverse 22s infinite alternate ease-in-out;
        }

        .animate-blob-3 {
            animation: float-blob 19s infinite alternate-reverse ease-in-out;
        }

        @media (max-width: 640px) and (max-height: 760px) {
            .dashboard-panel {
                transform: scale(0.94);
                transform-origin: top center;
            }
        }
    </style>

</head>

<body class="antialiased theme-bg flex items-center justify-center">
    <!-- Theme Restore (runs as body's first child: document.body exists here, unlike in <head>) -->
    <script>
        (function() {
            if (localStorage.getItem('welcome-theme') === 'light') {
                document.body.classList.add('light-theme');
            }
        })();
    </script>


    <!-- ════════ Background Liquid Layer ════════ -->
    <div class="fixed inset-0 -z-20 overflow-hidden pointer-events-none">
        <!-- Tech grid overlay -->
        <div class="bg-grid-overlay"></div>
        
        <!-- Organic Blobs (Google Colors, Apple Blurs) -->
        <div class="absolute top-[10%] left-[10%] w-[50vw] h-[50vw] min-w-[320px] rounded-full bg-[#4285F4]/12 blur-[80px] animate-blob-1" style="opacity: var(--blob-opacity); transition: opacity 0.5s ease;"></div>
        <div class="absolute top-[30%] right-[10%] w-[45vw] h-[45vw] min-w-[300px] rounded-full bg-[#FF2D55]/10 blur-[90px] animate-blob-2" style="opacity: var(--blob-opacity); transition: opacity 0.5s ease;"></div>
        <div class="absolute bottom-[10%] left-[25%] w-[40vw] h-[40vw] min-w-[280px] rounded-full bg-[#34A853]/10 blur-[85px] animate-blob-3" style="opacity: var(--blob-opacity); transition: opacity 0.5s ease;"></div>
    </div>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner"
        class="hidden fixed bottom-24 left-4 right-4 z-50 p-4 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg max-w-[340px] mx-auto">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="flex-shrink-0 w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-white">
                    <p class="font-bold text-[12px] leading-tight">Install AbsenKu</p>
                    <p class="text-[9px] text-white/80">Akses lebih cepat & offline</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button onclick="installPWA()"
                    class="px-2.5 py-1 bg-white text-indigo-600 font-bold text-[10px] rounded-md hover:bg-gray-100 font-outfit">
                    Install
                </button>
                <button onclick="dismissInstallBanner()" class="p-1 text-white/80 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ════════ Center Viewport Container ════════ -->
    <main class="w-full h-[100svh] sm:w-[375px] sm:h-[780px] flex items-center justify-center p-0 sm:p-2.5">

        <!-- Phone Outer Shell (Only displays on screens >= sm) -->
        <div class="phone-shell relative w-full h-full sm:rounded-[48px] sm:p-2.5 sm:shadow-[0_30px_70px_-15px_rgba(0,0,0,0.15)] sm:border flex flex-col justify-between">
            
            <!-- Dynamic Island (Desktop only) -->
            <div class="hidden sm:flex absolute top-6.5 left-1/2 -translate-x-1/2 w-24 h-5.5 bg-black rounded-full z-50 items-center justify-end pr-3.5">
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400/90 shadow-[0_0_8px_#22d3ee]"></span>
            </div>

            <!-- Speaker Ear Piece (Desktop only) -->
            <div class="hidden sm:block absolute top-4 left-1/2 -translate-x-1/2 w-10 h-0.75 bg-neutral-950 rounded-full z-50"></div>

            <!-- Phone Screen Content (Full viewport on mobile, scroll locked container) -->
            <div class="screen-content relative h-full w-full sm:rounded-[38px] overflow-hidden flex flex-col justify-between p-0 border border-transparent sm:border-white/5">
                
                <!-- Internal Screen Mesh Gradient -->
                <div class="absolute inset-0 pointer-events-none -z-10 bg-gradient-to-b from-indigo-500/10 via-transparent to-cyan-500/5"></div>
                <div class="absolute top-[20%] right-[-10%] w-36 h-36 rounded-full bg-[#FF2D55]/5 blur-[40px]"></div>

                <!-- 1. Header (Fixed at top) -->
                <header class="relative z-10 flex items-center justify-between px-5 pt-5 pb-3">
                    <!-- User Info Layout -->
                    <div class="flex items-center gap-2.5">
                        <div class="relative w-9 h-9 rounded-full bg-gradient-to-tr from-cyan-400 to-emerald-400 p-[1px] shadow-sm">
                            <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center overflow-hidden border border-white/10">
                                @if (auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="Profile"
                                        class="w-full h-full object-cover">
                                @else
                                    <span class="text-white text-[10px] font-bold font-outfit">{{ auth()->user()->initials() }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="leading-none text-left">
                            <h2 class="text-xs font-bold theme-text-main font-display truncate max-w-[140px]">{{ auth()->user()->name }}</h2>
                            <p class="text-[9px] theme-text-muted mt-0.5">{{ auth()->user()->office?->name ?? 'MI Daarul Hikmah' }}</p>
                        </div>
                    </div>

                    <!-- Actions Layout: Theme Toggle & Logout -->
                    <div class="flex items-center gap-2.5">
                        @if ($linkedAccounts->isNotEmpty())
                            <div x-data="{ open: false, confirmOpen: false, target: null }">
                                <button type="button" @click="open = !open"
                                    class="theme-toggle w-7.5 h-7.5 rounded-lg glass-card theme-border flex items-center justify-center theme-text-muted hover:theme-text-main hover:scale-105 active:scale-95 transition-all duration-300"
                                    aria-label="Ganti Akun">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </button>

                                <!-- Dropdown: solid (opaque) surface, anchored to the header's right edge -->
                                <div x-show="open" x-cloak @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                    class="solid-panel absolute right-0 top-14 w-64 rounded-2xl p-2 z-40 text-left">
                                    <p class="px-2 py-1 text-[9px] uppercase tracking-wider theme-text-muted font-outfit">Ganti Akun</p>
                                    @foreach ($linkedAccounts as $account)
                                        <button type="button"
                                            @click="target = { id: {{ $account->id }}, name: @js($account->name) }; open = false; confirmOpen = true"
                                            class="w-full flex items-center gap-2 rounded-xl px-2 py-2 text-xs theme-text-main hover:bg-cyan-500/10 transition-colors">
                                            <span class="w-7 h-7 flex-none rounded-full bg-gradient-to-tr from-cyan-400 to-emerald-400 flex items-center justify-center text-[9px] font-bold text-slate-950">
                                                {{ $account->initials() }}
                                            </span>
                                            <span class="min-w-0 flex-1 truncate text-left">
                                                {{ $account->name }}
                                                <span class="block text-[9px] theme-text-muted">{{ $account->office?->name ?? 'Tanpa kantor' }}</span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>

                                <!-- Confirmation modal (teleported to body so header stacking/overflow can't clip it) -->
                                <template x-teleport="body">
                                    <div x-show="confirmOpen" x-cloak @keydown.escape.window="confirmOpen = false"
                                        class="fixed inset-0 z-[60] flex items-center justify-center p-5"
                                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                        <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px]" @click="confirmOpen = false"></div>
                                        <div class="solid-panel relative w-full max-w-xs rounded-3xl p-6 text-center"
                                            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                                            <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-cyan-500/15 flex items-center justify-center text-cyan-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-base font-black font-display theme-text-main">Ganti Akun</h3>
                                            <p class="mt-1 text-xs theme-text-muted">Berpindah ke akun
                                                <span class="font-bold theme-text-main" x-text="target?.name"></span>?
                                                Sesi akun ini akan diganti.
                                            </p>
                                            <div class="mt-5 flex gap-2.5">
                                                <button type="button" @click="confirmOpen = false"
                                                    class="flex-1 py-3 rounded-xl glass-card theme-border theme-text-main text-xs font-bold uppercase tracking-wider font-outfit hover:scale-[1.02] active:scale-95 transition-transform">
                                                    Batal
                                                </button>
                                                <form method="POST" action="{{ route('account.switch') }}" class="flex-1">
                                                    @csrf
                                                    <input type="hidden" name="target_id" :value="target?.id">
                                                    <button type="submit"
                                                        class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-400 to-emerald-400 text-slate-950 text-xs font-bold uppercase tracking-wider font-outfit shadow-[0_8px_20px_rgba(6,182,212,0.28)] hover:scale-[1.02] active:scale-95 transition-transform">
                                                        Ganti
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endif
                        <!-- Theme Toggle Button -->
                        <button onclick="toggleTheme()" class="theme-toggle w-7.5 h-7.5 rounded-lg glass-card theme-border flex items-center justify-center text-amber-500 hover:scale-105 active:scale-95 transition-all duration-300" aria-label="Toggle Theme">
                            <!-- Sun Icon (for dark mode) -->
                            <svg class="sun-icon w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                            </svg>
                            <!-- Moon Icon (for light mode) -->
                            <svg class="moon-icon w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                            </svg>
                        </button>
                        
                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                            @csrf
                            <button type="submit" class="theme-toggle w-7.5 h-7.5 rounded-lg glass-card theme-border flex items-center justify-center theme-text-muted hover:theme-text-main hover:scale-105 active:scale-95 transition-all duration-300" aria-label="Keluar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </header>

                <!-- 2. Scrollable Content Body -->
                <div class="flex-1 overflow-y-auto custom-scroll px-5 pb-24 pt-1 space-y-4">
                    
                    <!-- App Logo / Title Card -->
                    <div class="text-center py-2 relative">
                        <div class="flex items-center justify-center">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-cyan-400 to-emerald-400 p-[1px] shadow-sm mr-2.5">
                                <div class="w-full h-full rounded-xl bg-slate-950 flex items-center justify-center">
                                    <img src="/images/icons/icon-192.png?v=2" alt="" class="h-5.5 w-5.5">
                                </div>
                            </span>
                            <span class="text-2xl font-black theme-text-main font-display">absen<span class="text-cyan-500 font-extrabold">KU</span></span>
                        </div>
                    </div>

                    <!-- Today Status check-in badge (Floating notification style if checked in) -->
                    @if ($todayAttendance)
                        <div class="rounded-2xl p-3.5 relative overflow-hidden {{ $todayAttendance->status->value === 'late' ? 'theme-status-late-card theme-status-late-text' : 'theme-status-ok-card theme-status-ok-text' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl overflow-hidden border border-white/20 flex-none bg-slate-900">
                                    <img src="{{ $todayAttendance->image_url }}" alt="Selfie" class="w-full h-full object-cover">
                                </div>
                                <div class="text-left flex-1 leading-tight">
                                    <p class="text-[9px] theme-text-muted uppercase font-outfit">Status Absen Anda</p>
                                    <p class="text-sm font-black font-display {{ $todayAttendance->status->value === 'late' ? 'theme-status-late-text' : 'theme-status-ok-text' }}">{{ $todayAttendance->status->label() }}</p>
                                    <p class="text-[9px] theme-text-muted mt-0.5">Masuk pukul {{ $todayAttendance->created_at->format('H:i') }} WIB</p>
                                </div>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white/10 flex-none {{ $todayAttendance->status->value === 'late' ? 'theme-status-late-text' : 'theme-status-ok-text' }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Schedule Card -->
                    <div class="glass-card theme-border rounded-[22px] p-4.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8.5 h-8.5 rounded-lg bg-cyan-500/15 flex items-center justify-center text-cyan-500">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    @if ($todaySchedule)
                                        <p class="text-[9px] theme-text-muted font-outfit uppercase">Jadwal Hari Ini</p>
                                        <p class="font-black text-xs theme-text-main font-outfit mt-0.5">
                                            {{ \Carbon\Carbon::parse($todaySchedule->check_in_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($todaySchedule->check_out_time)->format('H:i') }}
                                        </p>
                                    @else
                                        <p class="text-[9px] theme-text-muted font-outfit uppercase">Tidak Ada Jadwal</p>
                                        <p class="font-bold text-xs theme-status-late-text font-outfit mt-0.5">Hari Libur</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right leading-tight">
                                <p class="font-black text-xs theme-text-main font-display capitalize">{{ now()->locale('id')->isoFormat('dddd') }}</p>
                                <p class="text-[9px] theme-text-muted font-outfit mt-0.5">{{ now()->format('d M Y') }}</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-between mt-3.5 pt-3.5 theme-border-t">
                            <div class="text-left">
                                <p class="text-[9px] theme-text-muted uppercase">Masuk :</p>
                                @if ($todayAttendance)
                                    <p class="font-bold text-xs mt-0.5 {{ $todayAttendance->status->value === 'late' ? 'theme-status-late-text' : 'theme-status-ok-text' }} font-outfit">
                                        {{ $todayAttendance->created_at->format('H:i') }}
                                        @if ($todayAttendance->status->value === 'late')
                                            <span class="text-[9px] theme-status-late-text opacity-80 font-normal">(Terlambat)</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="font-bold text-xs theme-text-muted mt-0.5 font-outfit">-</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] theme-text-muted uppercase">Pulang :</p>
                                @if ($todayAttendance && $todayAttendance->check_out_at)
                                    <p class="font-bold text-xs theme-status-ok-text mt-0.5 font-outfit">
                                        {{ $todayAttendance->check_out_at->format('H:i') }}
                                    </p>
                                @else
                                    <p class="font-bold text-xs theme-text-muted mt-0.5 font-outfit">-</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Summary Card -->
                    <div class="glass-card theme-border rounded-[22px] p-4.5">
                        <h3 class="font-black text-xs theme-text-main font-display uppercase tracking-wider mb-3.5 text-left">Rekap Absensi Bulan Ini</h3>
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Hadir -->
                            <div class="text-center rounded-xl py-2" style="border: 1px solid var(--stats-card-border);">
                                <p class="text-[8px] theme-text-muted font-outfit uppercase">Hadir</p>
                                <p class="text-2xl font-black font-display mt-0.5" style="color: var(--stats-present); transition: color 0.5s ease;">{{ $monthlyPresent }}</p>
                                <p class="text-[8px] theme-text-muted font-outfit">Hari</p>
                            </div>
                            <!-- Terlambat -->
                            <div class="text-center rounded-xl py-2" style="border: 1px solid var(--stats-card-border);">
                                <p class="text-[8px] theme-text-muted font-outfit uppercase">Telat</p>
                                <p class="text-2xl font-black font-display mt-0.5" style="color: var(--stats-late); transition: color 0.5s ease;">{{ $monthlyLate }}</p>
                                <p class="text-[8px] theme-text-muted font-outfit">Hari</p>
                            </div>
                            <!-- Total -->
                            <div class="text-center rounded-xl py-2" style="border: 1px solid var(--stats-card-border);">
                                <p class="text-[8px] theme-text-muted font-outfit uppercase">Total</p>
                                <p class="text-2xl font-black font-display mt-0.5" style="color: var(--stats-total); transition: color 0.5s ease;">{{ $totalAttendance }}</p>
                                <p class="text-[8px] theme-text-muted font-outfit">Hari</p>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Utama -->
                    <div class="text-left">
                        <h3 class="font-black text-xs theme-text-main font-display uppercase tracking-wider mb-3">Menu Utama</h3>
                        <div class="grid grid-cols-4 gap-2.5">
                            <!-- Riwayat -->
                            <a href="{{ route('attendance.index') }}"
                                class="glass-card theme-border rounded-[20px] p-3 text-center hover:scale-102 transition-transform duration-300">
                                <div class="theme-icon-riwayat w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-cyan-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                <p class="text-[9px] font-bold theme-text-main font-outfit truncate">Riwayat</p>
                            </a>
                            <!-- Profil -->
                            <a href="{{ route('attendance.profile') }}"
                                class="glass-card theme-border rounded-[20px] p-3 text-center hover:scale-102 transition-transform duration-300">
                                <div class="theme-icon-profil w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-purple-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <p class="text-[9px] font-bold theme-text-main font-outfit truncate">Profil</p>
                            </a>
                            <!-- Password -->
                            <a href="{{ route('attendance.password') }}"
                                class="glass-card theme-border rounded-[20px] p-3 text-center hover:scale-102 transition-transform duration-300">
                                <div class="theme-icon-password w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-amber-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <p class="text-[9px] font-bold theme-text-main font-outfit truncate">Password</p>
                            </a>
                            <!-- Perizinan -->
                            <a href="{{ route('attendance.leaves.index') }}"
                                class="glass-card theme-border rounded-[20px] p-3 text-center hover:scale-102 transition-transform duration-300">
                                <div class="theme-icon-perizinan w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-green-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-[9px] font-bold theme-text-main font-outfit truncate">Izin</p>
                            </a>
                        </div>
                    </div>

                    <!-- Informasi (swipeable cards, admin-managed) -->
                    @if ($announcements->isNotEmpty())
                        <style>
                            .info-scroll::-webkit-scrollbar { display: none; }
                            .info-scroll { -ms-overflow-style: none; scrollbar-width: none; }
                        </style>
                        <div class="pt-1">
                            <h3 class="font-black text-xs theme-text-main font-display uppercase tracking-wider mb-3">Informasi</h3>
                            <div class="info-scroll flex gap-3 overflow-x-auto snap-x snap-mandatory -mx-1 px-1 pb-1">
                                @foreach ($announcements as $info)
                                    <a href="{{ route('attendance.information.show', $info) }}"
                                        class="snap-start shrink-0 w-[78%] glass-card theme-border rounded-[20px] overflow-hidden hover:scale-[1.01] transition-transform duration-300">
                                        <div class="relative aspect-[16/9] w-full overflow-hidden">
                                            @if ($info->image_url)
                                                <img src="{{ $info->image_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center p-4">
                                                    <span class="text-white font-bold text-sm text-center line-clamp-2">{{ $info->title }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="p-3">
                                            <p class="font-bold text-[11px] theme-text-main font-outfit line-clamp-1">{{ $info->title }}</p>
                                            @if ($info->summary)
                                                <p class="text-[9px] theme-text-main opacity-60 font-outfit mt-0.5 line-clamp-2">{{ $info->summary }}</p>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Menu Persetujuan (Kepala Sekolah only) -->
                    @if (auth()->user()->role?->slug === 'kepala-sekolah')
                        <div class="pt-1">
                            <a href="{{ route('approval.leaves.index') }}"
                                class="flex items-center justify-between bg-gradient-to-r from-indigo-500 to-purple-600 rounded-[22px] shadow-md p-4 text-white hover:scale-[1.01] transition-transform duration-300">
                                <div class="flex items-center text-left">
                                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mr-3 flex-none">
                                        <svg class="w-5.5 h-5.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="leading-tight">
                                        <p class="font-bold text-xs">Persetujuan Perizinan</p>
                                        <p class="text-white/70 text-[9px] font-outfit mt-0.5">Kelola pengajuan izin & cuti guru</p>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    @endif

                </div>

                <!-- 3. Bottom Navigation Bar (Fixed at bottom) -->
                <nav class="theme-border-t relative z-10 flex items-center justify-between pt-3 pb-1 mt-1 w-full bg-transparent px-3">
                    <!-- Beranda (Active) -->
                    <a href="{{ route('attendance.dashboard') }}" class="footer-nav-item active flex-1 flex flex-col items-center gap-0.5">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                        </svg>
                        <span class="text-[7.5px] font-bold font-outfit">Beranda</span>
                    </a>

                    <!-- Masuk (Selfie) -->
                    <a href="{{ route('attendance.selfie') }}" class="flex-1 flex flex-col items-center -mt-4">
                        @if ($todayAttendance)
                            <!-- Already Checked In -->
                            <div class="theme-nav-inactive w-10.5 h-10.5 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <span class="text-[7.5px] font-bold theme-text-muted mt-1 font-outfit">Masuk</span>
                        @else
                            <!-- Ready to Check In -->
                            <div class="w-10.5 h-10.5 rounded-full flex items-center justify-center bg-gradient-to-r from-green-400 to-emerald-400 shadow-[0_6px_16px_rgba(16,185,129,0.25)] hover:scale-105 active:scale-95 transition-transform duration-300">
                                <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <span class="text-[7.5px] font-bold theme-status-ok-text mt-1 font-outfit">Masuk</span>
                        @endif
                    </a>

                    <!-- Pulang (Checkout) -->
                    <a href="{{ route('attendance.checkout') }}" class="flex-1 flex flex-col items-center -mt-4">
                        @php
                            $canCheckout = $todayAttendance && !$todayAttendance->hasCheckedOut() && ($checkoutTimeReached ?? false);
                        @endphp
                        @if ($canCheckout)
                            <!-- Ready to Check Out -->
                            <div class="w-10.5 h-10.5 rounded-full flex items-center justify-center bg-gradient-to-r from-amber-400 to-orange-400 shadow-[0_6px_16px_rgba(245,158,11,0.25)] hover:scale-105 active:scale-95 transition-transform duration-300">
                                <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <span class="text-[7.5px] font-bold theme-status-late-text mt-1 font-outfit">Pulang</span>
                        @else
                            <!-- Cannot Check Out -->
                            <div class="theme-nav-inactive w-10.5 h-10.5 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <span class="text-[7.5px] font-bold theme-text-muted mt-1 font-outfit">Pulang</span>
                        @endif
                    </a>

                    <!-- Riwayat -->
                    <a href="{{ route('attendance.index') }}" class="footer-nav-item flex-1 flex flex-col items-center gap-0.5 hover:text-slate-400 transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span class="text-[7.5px] font-bold font-outfit">Riwayat</span>
                    </a>
                </nav>

            </div>
        </div>

    </main>

    <!-- Theme Toggle Logic Script -->
    <script>
        function toggleTheme() {
            document.body.classList.toggle('light-theme');
            const isLight = document.body.classList.contains('light-theme');
            localStorage.setItem('welcome-theme', isLight ? 'light' : 'dark');
        }
    </script>
</body>

</html>
