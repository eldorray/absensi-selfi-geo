<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    @php
        $branding = \App\Models\ApplicationSetting::current();
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f1712">
    <meta name="description" content="Aplikasi Absensi Selfie dengan Verifikasi GPS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AbsenKu">
    <link rel="manifest" href="{{ route('manifest') }}">
    <link rel="icon" href="{{ $branding->iconUrl() }}">
    <link rel="apple-touch-icon" href="{{ $branding->iconUrl() }}">
    <title>AbsenKu - {{ auth()->user()->name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
            /* Colors - Dark Theme (Android Green Palette) */
            --bg-color: #0F1A11;
            --screen-bg: #152218;
            --text-main: #E8F5E9;
            --text-muted: #93B897;
            --glass-bg: rgba(255, 255, 255, 0.06);
            --glass-border: rgba(255, 255, 255, 0.10);
            --glass-shadow: none;
            --glass-card-bg: rgba(255, 255, 255, 0.06);
            --glass-card-border: rgba(255, 255, 255, 0.10);
            --menu-bg: #152218;
            --menu-border: rgba(255, 255, 255, 0.10);
            --glass-card-shadow: none;
            --grid-line-color: rgba(255, 255, 255, 0.012);
            --blob-opacity: 0.20;
            --phone-shell-bg: #0F1A11;
            --phone-shell-border: rgba(255, 255, 255, 0.10);
            --footer-icon-color: #93B897;
            --active-nav-color: #A5D6A7;
            
            /* Stats colors (Android Green) */
            --stats-present: #A5D6A7;
            --stats-late: #D9A24A;
            --stats-total: #A5D6A7;
            --stats-card-border: rgba(255, 255, 255, 0.10);
            
            /* Navigation colors */
            --nav-btn-inactive-bg: rgba(255, 255, 255, 0.06);
            --nav-btn-inactive-border: rgba(255, 255, 255, 0.10);
            --nav-btn-inactive-text: #93B897;
            
            /* Custom Icon Backgrounds (Green tones) */
            --icon-riwayat-bg: rgba(165, 214, 167, 0.12);
            --icon-profil-bg: rgba(165, 214, 167, 0.12);
            --icon-password-bg: rgba(217, 162, 74, 0.12);
            --icon-perizinan-bg: rgba(165, 214, 167, 0.12);
            
            /* Statuses (Android specs) */
            --status-ok-text: #A5D6A7;
            --status-ok-bg: rgba(165, 214, 167, 0.12);
            --status-ok-border: rgba(165, 214, 167, 0.30);
            --status-late-text: #D9A24A;
            --status-late-bg: rgba(217, 162, 74, 0.12);
            --status-late-border: rgba(217, 162, 74, 0.30);
        }

        body.light-theme {
            /* Colors - Light Theme (Android Green Palette) */
            --bg-color: #E8F5E9;
            --screen-bg: #F1F8F2;
            --text-main: #1B5E20;
            --text-muted: #5B7A60;
            
            --glass-bg: rgba(255, 255, 255, 0.72);
            --glass-border: rgba(27, 94, 32, 0.10);
            --glass-shadow: none;
            
            --glass-card-bg: rgba(255, 255, 255, 0.72);
            --glass-card-border: rgba(27, 94, 32, 0.10);
            --menu-bg: #ffffff;
            --menu-border: rgba(27, 94, 32, 0.10);
            --glass-card-shadow: none;
            
            --grid-line-color: rgba(27, 94, 32, 0.04);
            --blob-opacity: 0.32;
            
            --phone-shell-bg: #E8F5E9;
            --phone-shell-border: rgba(27, 94, 32, 0.08);
            
            --footer-icon-color: #5B7A60;
            --active-nav-color: #66BB6A;
            
            /* Stats colors (Android Green) */
            --stats-present: #2E7D32;
            --stats-late: #C0843A;
            --stats-total: #66BB6A;
            --stats-card-border: rgba(27, 94, 32, 0.10);
            
            /* Navigation colors */
            --nav-btn-inactive-bg: rgba(27, 94, 32, 0.04);
            --nav-btn-inactive-border: rgba(27, 94, 32, 0.08);
            --nav-btn-inactive-text: #5B7A60;
            
            /* Custom Icon Backgrounds (Green tones) */
            --icon-riwayat-bg: rgba(102, 187, 106, 0.12);
            --icon-profil-bg: rgba(102, 187, 106, 0.12);
            --icon-password-bg: rgba(192, 132, 58, 0.12);
            --icon-perizinan-bg: rgba(46, 125, 50, 0.12);
            
            /* Statuses (Android specs) */
            --status-ok-text: #2E7D32;
            --status-ok-bg: rgba(46, 125, 50, 0.10);
            --status-ok-border: rgba(46, 125, 50, 0.25);
            --status-late-text: #C0843A;
            --status-late-bg: rgba(192, 132, 58, 0.10);
            --status-late-border: rgba(192, 132, 58, 0.25);
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            overscroll-behavior: none;
            width: 100%;
            margin: 0;
            padding: 0;
            transition: background-color 300ms ease-out;
        }
        
        /* Android Motion Tokens */
        .dur-fast { transition-duration: 150ms; }
        .dur-med { transition-duration: 300ms; }
        .dur-slow { transition-duration: 450ms; }
        
        .ease-out { transition-timing-function: cubic-bezier(0.05, 0.7, 0.1, 1); }
        .ease-in-out { transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Spring Press Animation (Android: damping 0.65, stiffness 420) */
        .spring-press {
            transition: transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        .spring-press:active {
            transform: scale(0.96);
        }
        
        /* Staggered Entrance Animation */
        .stagger-0 { animation-delay: 0ms; }
        .stagger-40 { animation-delay: 40ms; }
        .stagger-80 { animation-delay: 80ms; }
        .stagger-120 { animation-delay: 120ms; }
        .stagger-160 { animation-delay: 160ms; }
        .stagger-200 { animation-delay: 200ms; }
        
        @keyframes stagger-enter {
            0% {
                opacity: 0;
                transform: translateY(calc(1/16 * 100vh)) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .animate-stagger {
            animation-name: stagger-enter;
            animation-duration: 300ms;
            animation-fill-mode: both;
            animation-timing-function: cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .font-display {
            font-family: "Inter", ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        .font-outfit {
            font-family: "Inter", ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        body {
            font-family: "Inter", ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        /* Mapping classes for Theme Transitions (Android Motion) */
        .theme-bg {
            background-color: var(--bg-color);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        
        .theme-text-main {
            color: var(--text-main);
            transition: color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .theme-text-muted {
            color: var(--text-muted);
            transition: color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .phone-shell {
            background-color: var(--phone-shell-bg);
            border-color: var(--phone-shell-border);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        box-shadow 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .screen-content {
            background-color: var(--screen-bg);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
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
            transition: background-image 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .glass-panel {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            transition: background 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .glass-card {
            background: var(--glass-card-bg);
            border: 1px solid var(--glass-card-border);
            box-shadow: var(--glass-card-shadow);
            transition: background 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        /* Opaque surface for popovers/modals — readable over the busy dashboard */
        .solid-panel {
            background: var(--menu-bg);
            border: 1px solid var(--menu-border);
            box-shadow: var(--glass-shadow);
            transition: background 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .footer-nav-item {
            color: var(--footer-icon-color);
            transition: color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .footer-nav-item.active {
            color: var(--active-nav-color);
        }

        /* Reference-style bottom navigation bar (elevated center actions) */
        .footer-nav {
            background: #152218;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.28);
        }
        body.light-theme .footer-nav {
            background: #ffffff;
            border-top: 1px solid rgba(27, 94, 32, 0.08);
            box-shadow: 0 -8px 24px rgba(27, 94, 32, 0.08);
        }
        .nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            text-decoration: none;
        }
        .nav-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px 18px;
            border-radius: 14px;
            color: var(--footer-icon-color);
            transition: background 150ms cubic-bezier(0.4, 0, 0.2, 1), 
                        color 150ms cubic-bezier(0.4, 0, 0.2, 1), 
                        transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-pill:active {
            transform: scale(0.96);
        }
        .nav-item.is-active .nav-pill {
            background: rgba(165, 214, 167, 0.15);
            color: var(--active-nav-color);
        }
        body.light-theme .nav-item.is-active .nav-pill {
            background: rgba(102, 187, 106, 0.15);
        }
        .nav-label {
            font-size: 8px;
            font-weight: 800;
            font-family: "Inter", ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            letter-spacing: 0.02em;
            color: var(--footer-icon-color);
        }
        .nav-item.is-active .nav-label {
            color: var(--active-nav-color);
        }
        .nav-fab {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 9999px;
            border: 3px solid #152218;
            margin-top: -20px;
            transition: transform 150ms cubic-bezier(0.4, 0, 0.2, 1), 
                        box-shadow 150ms cubic-bezier(0.4, 0, 0.2, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        body.light-theme .nav-fab {
            border-color: #ffffff;
        }
        .nav-fab svg {
            width: 22px;
            height: 22px;
        }
        .nav-fab:active {
            transform: scale(0.96);
        }
        /* Android Check-in Gradient: #66BB6A → #1B5E20 */
        .nav-fab-masuk {
            background: linear-gradient(135deg, #66BB6A, #1B5E20);
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(102, 187, 106, 0.45);
        }
        /* Android Check-out Gradient: #CD7F5F → #A24E38 */
        .nav-fab-pulang {
            background: linear-gradient(135deg, #CD7F5F, #A24E38);
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(205, 127, 95, 0.42);
        }

        /* Custom Theme Borders to prevent black-line specificity issues */
        .theme-border {
            border: 1px solid var(--glass-card-border) !important;
            transition: border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .theme-border-t {
            border-top: 1px solid var(--glass-card-border) !important;
            transition: border-top-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        /* Dynamic Icons mapping background */
        .theme-icon-riwayat {
            background-color: var(--icon-riwayat-bg);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        .theme-icon-profil {
            background-color: var(--icon-profil-bg);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        .theme-icon-password {
            background-color: var(--icon-password-bg);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        .theme-icon-perizinan {
            background-color: var(--icon-perizinan-bg);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        /* Inactive nav buttons shape styling */
        .theme-nav-inactive {
            background-color: var(--nav-btn-inactive-bg);
            border: 1px solid var(--nav-btn-inactive-border) !important;
            color: var(--nav-btn-inactive-text);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        
        /* Statuses Theme Mapping */
        .theme-status-ok-text {
            color: var(--status-ok-text);
            transition: color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        .theme-status-ok-card {
            background: var(--status-ok-bg) !important;
            border: 1px solid var(--status-ok-border) !important;
            transition: background 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        .theme-status-late-text {
            color: var(--status-late-text);
            transition: color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        .theme-status-late-card {
            background: var(--status-late-bg) !important;
            border: 1px solid var(--status-late-border) !important;
            transition: background 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        /* Sun / Moon Toggle Button Icon display */
        .theme-toggle .sun-icon { display: block; }
        .theme-toggle .moon-icon { display: none; }
        
        body.light-theme .theme-toggle .sun-icon { display: none; }
        body.light-theme .theme-toggle .moon-icon { display: block; }

        .theme-toggle {
            transition: transform 150ms cubic-bezier(0.4, 0, 0.2, 1), 
                        background 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        border-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }
        .theme-toggle:active {
            transform: scale(0.96);
        }

        /* Custom scrollbar inside mobile screen content container */
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(165, 214, 167, 0.20);
            border-radius: 9px;
        }
        body.light-theme .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(27, 94, 32, 0.12);
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
    @include('partials.pwa-material3')

</head>

<body class="pwa-m3 antialiased theme-bg flex items-center justify-center" data-attendance-ui="material-3">
    <!-- Theme Restore (runs as body's first child: document.body exists here, unlike in <head>) -->
    <script>
        (function() {
            if (localStorage.getItem('welcome-theme') === 'light') {
                document.body.classList.add('light-theme');
            }
        })();
    </script>


    <!-- ════════ Background Liquid Layer ════════ -->
    <div class="pwa-decoration fixed inset-0 -z-20 overflow-hidden pointer-events-none">
        <!-- Tech grid overlay -->
        <div class="bg-grid-overlay"></div>
        
        <!-- Organic Blobs (Android Green tones) -->
        <div class="absolute top-[10%] left-[10%] w-[50vw] h-[50vw] min-w-[320px] rounded-full bg-[#66BB6A]/25 blur-[80px] animate-blob-1" style="opacity: var(--blob-opacity); transition: opacity 300ms cubic-bezier(0.05, 0.7, 0.1, 1);"></div>
        <div class="absolute top-[30%] right-[10%] w-[45vw] h-[45vw] min-w-[300px] rounded-full bg-[#2E7D32]/25 blur-[90px] animate-blob-2" style="opacity: var(--blob-opacity); transition: opacity 300ms cubic-bezier(0.05, 0.7, 0.1, 1);"></div>
        <div class="absolute bottom-[10%] left-[25%] w-[40vw] h-[40vw] min-w-[280px] rounded-full bg-[#A5D6A7]/20 blur-[85px] animate-blob-3" style="opacity: var(--blob-opacity); transition: opacity 300ms cubic-bezier(0.05, 0.7, 0.1, 1);"></div>
    </div>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner"
        class="hidden fixed bottom-24 left-4 right-4 z-50 p-4 bg-gradient-to-r from-green-600 to-emerald-700 rounded-2xl shadow-lg max-w-[340px] mx-auto">
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
                    class="px-2.5 py-1 bg-white text-green-700 font-bold text-[10px] rounded-md hover:bg-gray-100 font-outfit">
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
                <span class="w-1.5 h-1.5 rounded-full bg-green-400/90 shadow-[0_0_8px_#66BB6A]"></span>
            </div>

            <!-- Speaker Ear Piece (Desktop only) -->
            <div class="hidden sm:block absolute top-4 left-1/2 -translate-x-1/2 w-10 h-0.75 bg-neutral-950 rounded-full z-50"></div>

            <!-- Phone Screen Content (Full viewport on mobile, scroll locked container) -->
            <div class="screen-content relative h-full w-full sm:rounded-[38px] overflow-hidden flex flex-col justify-between p-0 border border-transparent sm:border-white/5">
                
                <!-- Internal Screen Mesh Gradient (Android Green) -->
                <div class="absolute inset-0 pointer-events-none -z-10 bg-gradient-to-b from-green-500/10 via-transparent to-emerald-500/5"></div>
                <div class="absolute top-[20%] right-[-10%] w-36 h-36 rounded-full bg-[#66BB6A]/5 blur-[40px]"></div>

                <!-- 1. Header (Fixed at top) -->
                <header class="relative z-10 flex items-center justify-between px-5 pt-5 pb-3" data-m3-region="top-app-bar">
                    <!-- User Info Layout -->
                    <a href="{{ route('attendance.profile') }}"
                        data-profile-link="teacher-identity"
                        aria-label="Buka profil {{ auth()->user()->name }}"
                        class="-m-1 flex min-h-12 items-center gap-2.5 rounded-2xl p-1 transition-colors hover:bg-emerald-500/10 focus-visible:bg-emerald-500/10">
                        <div class="relative w-9 h-9 rounded-full bg-gradient-to-tr from-green-400 to-emerald-500 p-[1px] shadow-sm">
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
                    </a>

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
                                            class="w-full flex items-center gap-2 rounded-xl px-2 py-2 text-xs theme-text-main hover:bg-green-500/10 transition-colors">
                                            <span class="w-7 h-7 flex-none rounded-full bg-gradient-to-tr from-green-400 to-emerald-500 flex items-center justify-center text-[9px] font-bold text-slate-950">
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
                                            <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-green-500/15 flex items-center justify-center text-green-500">
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
                                                        class="w-full py-3 rounded-xl bg-gradient-to-r from-green-400 to-emerald-500 text-slate-950 text-xs font-bold uppercase tracking-wider font-outfit shadow-[0_8px_20px_rgba(102,187,106,0.28)] hover:scale-[1.02] active:scale-95 transition-transform">
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
                            <svg class="moon-icon w-4 h-4 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                <div class="flex-1 overflow-y-auto custom-scroll px-5 pb-24 pt-1 space-y-4 mobile-panel" data-m3-region="content">
                    
                    <!-- App Logo / Title Card -->
                    <div class="text-center py-2 relative">
                        <div class="flex items-center justify-center">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-green-400 to-emerald-500 p-[1px] shadow-sm mr-2.5">
                                <div class="w-full h-full rounded-xl bg-slate-950 flex items-center justify-center">
                                    <img src="/images/icons/icon-192.png?v=2" alt="" class="h-5.5 w-5.5">
                                </div>
                            </span>
                            <span class="text-2xl font-black theme-text-main font-display">absen<span class="text-green-500 font-extrabold">KU</span></span>
                        </div>
                    </div>

                    <!-- Today Status check-in badge -->
                    @if ($todayAttendance)
                        <div x-data="{
                                show: localStorage.getItem('statusBannerSeen') !== '{{ $todayAttendance->id }}',
                                dismiss() { this.show = false; localStorage.setItem('statusBannerSeen', '{{ $todayAttendance->id }}'); },
                            }"
                            x-init="if (show) setTimeout(() => dismiss(), 10000)" x-show="show" x-cloak
                            @click="dismiss()" x-transition.opacity.duration.500ms
                            class="animate-stagger stagger-0 rounded-2xl p-3.5 relative overflow-hidden cursor-pointer {{ $todayAttendance->status->value === 'late' ? 'theme-status-late-card theme-status-late-text' : 'theme-status-ok-card theme-status-ok-text' }}">
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
                    <div class="animate-stagger stagger-40 glass-card theme-border rounded-[22px] p-4.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8.5 h-8.5 rounded-lg bg-green-500/15 flex items-center justify-center text-green-500">
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
                    <div class="animate-stagger stagger-80 glass-card theme-border rounded-[22px] p-4.5">
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
                    <div class="animate-stagger stagger-120 text-left">
                        <h3 class="font-black text-xs theme-text-main font-display uppercase tracking-wider mb-3">Menu Utama</h3>
                        <div class="grid grid-cols-4 gap-2">
                            <!-- Riwayat -->
                            <a href="{{ route('attendance.index') }}"
                                class="interactive-card glass-card theme-border rounded-[20px] p-3 text-center transition-all duration-300">
                                <div class="theme-icon-riwayat w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-green-500 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                <p class="text-[9px] font-bold theme-text-main font-outfit truncate">Riwayat</p>
                            </a>
                            <!-- Profil -->
                            <a href="{{ route('attendance.profile') }}"
                                class="interactive-card glass-card theme-border rounded-[20px] p-3 text-center transition-all duration-300">
                                <div class="theme-icon-profil w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-purple-500 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <p class="text-[9px] font-bold theme-text-main font-outfit truncate">Profil</p>
                            </a>
                            <!-- Password -->
                            <a href="{{ route('attendance.password') }}"
                                class="interactive-card glass-card theme-border rounded-[20px] p-3 text-center transition-all duration-300">
                                <div class="theme-icon-password w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-amber-500 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <p class="text-[9px] font-bold theme-text-main font-outfit truncate">Password</p>
                            </a>
                            <!-- Perizinan (Izin) -->
                            <a href="{{ route('attendance.leaves.index') }}"
                                class="interactive-card glass-card theme-border rounded-[20px] p-3 text-center transition-all duration-300 relative group">
                                <div class="theme-icon-perizinan w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-green-500 shadow-sm group-hover:scale-105 transition-transform duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-[9px] font-bold theme-text-main font-outfit truncate">Izin</p>
                            </a>
                            @if (auth()->user()->canAccessBk() && ! auth()->user()->isAdmin())
                                <a href="{{ route('attendance.bk.index') }}"
                                    class="interactive-card glass-card theme-border rounded-[20px] p-3 text-center transition-all duration-300">
                                    <div class="theme-icon-riwayat w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center text-emerald-500 shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h8M8 10h8m-8 4h5m-7 7 3-3h9a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2v3Z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-[9px] font-bold theme-text-main font-outfit truncate">BK</p>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Informasi (swipeable cards, admin-managed) -->
                    @if ($announcements->isNotEmpty())
                        <style>
                            .info-scroll::-webkit-scrollbar { display: none; }
                            .info-scroll { -ms-overflow-style: none; scrollbar-width: none; }
                        </style>
                        <div class="pt-1 animate-stagger stagger-160">
                            <h3 class="font-black text-xs theme-text-main font-display uppercase tracking-wider mb-3">Informasi</h3>
                            <div class="info-scroll flex gap-3 overflow-x-auto snap-x snap-mandatory -mx-1 px-1 pb-1">
                                @foreach ($announcements as $info)
                                    <a href="{{ route('attendance.information.show', $info) }}"
                                        class="snap-start shrink-0 w-[78%] glass-card theme-border rounded-[20px] overflow-hidden hover:scale-[1.01] transition-transform duration-300">
                                        <div class="relative aspect-[16/9] w-full overflow-hidden">
                                            @if ($info->image_url)
                                                <img src="{{ $info->image_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center p-4">
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
                        <div class="pt-1 animate-stagger stagger-200">
                            <a href="{{ route('approval.leaves.index') }}"
                                class="flex items-center justify-between bg-gradient-to-r from-green-500 to-emerald-700 rounded-[22px] shadow-md p-4 text-white hover:scale-[1.01] transition-transform duration-300">
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
                <nav class="footer-nav relative z-10 flex items-end justify-between w-full px-2 pt-2 pb-1 mt-1" aria-label="Navigasi utama" data-m3-region="navigation-bar">
                    <!-- Beranda (Active) -->
                    <a href="{{ route('attendance.dashboard') }}" class="nav-item is-active">
                        <span class="nav-pill">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                            </svg>
                        </span>
                        <span class="nav-label">Beranda</span>
                    </a>

                    <!-- Masuk (Selfie) -->
                    <a href="{{ route('attendance.selfie') }}" class="nav-item">
                        <span class="nav-fab nav-fab-masuk">
                            <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </span>
                        <span class="nav-label">Masuk</span>
                    </a>

                    <!-- Pulang (Checkout) -->
                    <a href="{{ route('attendance.checkout') }}" class="nav-item">
                        <span class="nav-fab nav-fab-pulang">
                            <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </span>
                        <span class="nav-label">Pulang</span>
                    </a>

                    <!-- Riwayat -->
                    <a href="{{ route('attendance.index') }}" class="nav-item">
                        <span class="nav-pill">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </span>
                        <span class="nav-label">Riwayat</span>
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
