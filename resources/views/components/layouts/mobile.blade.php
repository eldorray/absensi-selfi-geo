@props([
    'title' => 'Absensi',
    'backUrl' => null,
    'activeTab' => null,
    'showNav' => true,
    'isSheet' => false,
])

<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f1712">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Aplikasi Absensi Selfie dengan Verifikasi GPS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AbsenKu">
    <link rel="manifest" href="/manifest.json?v=4">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png?v=2">
    <title>AbsenKu - {{ $title }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
            --glass-card-shadow: none;
            /* Solid (opaque) surface for menus/modals — not glass */
            --menu-bg: #152218;
            --menu-border: rgba(255, 255, 255, 0.10);
            --grid-line-color: rgba(255, 255, 255, 0.012);
            --blob-opacity: 0.20;
            --phone-shell-bg: #0F1A11;
            --phone-shell-border: rgba(255, 255, 255, 0.10);
            --footer-icon-color: #93B897;
            --active-nav-color: #A5D6A7;
            
            /* Inputs */
            --input-bg: rgba(255, 255, 255, 0.06);
            --input-border: rgba(255, 255, 255, 0.10);
            --input-focus-bg: rgba(255, 255, 255, 0.08);
            
            /* Buttons */
            --btn-accent-bg: #A5D6A7;
            --btn-accent-text: #0F1A11;
            --btn-accent-shadow: 0 12px 28px rgba(165, 214, 167, 0.25);
            
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
            --glass-card-shadow: none;
            /* Solid (opaque) surface for menus/modals — not glass */
            --menu-bg: #ffffff;
            --menu-border: rgba(27, 94, 32, 0.10);
            
            --grid-line-color: rgba(27, 94, 32, 0.04);
            --blob-opacity: 0.32;
            
            --phone-shell-bg: #E8F5E9;
            --phone-shell-border: rgba(27, 94, 32, 0.08);
            
            --footer-icon-color: #5B7A60;
            --active-nav-color: #66BB6A;
            
            /* Inputs */
            --input-bg: rgba(255, 255, 255, 0.72);
            --input-border: rgba(27, 94, 32, 0.10);
            --input-focus-bg: #ffffff;
            
            /* Buttons */
            --btn-accent-bg: #1B5E20;
            --btn-accent-text: #ffffff;
            --btn-accent-shadow: 0 10px 24px rgba(27, 94, 32, 0.2);
            
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
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
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
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
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

        /* Forms inputs theme mapping */
        .theme-input {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border) !important;
            color: var(--text-main);
            transition: background-color 150ms cubic-bezier(0.4, 0, 0.2, 1), 
                        border-color 150ms cubic-bezier(0.4, 0, 0.2, 1), 
                        color 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .theme-input:focus {
            background-color: var(--input-focus-bg);
            border-color: var(--active-nav-color) !important;
            outline: none;
            box-shadow: 0 0 0 2px rgba(165, 214, 167, 0.15);
        }

        /* Buttons Theme Mapping */
        .theme-btn-submit {
            background-color: var(--btn-accent-bg);
            color: var(--btn-accent-text);
            box-shadow: var(--btn-accent-shadow);
            transition: background-color 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        color 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        box-shadow 300ms cubic-bezier(0.05, 0.7, 0.1, 1), 
                        transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        .theme-btn-submit:active {
            transform: scale(0.96);
        }

        .footer-nav-item {
            color: var(--footer-icon-color);
            transition: color 300ms cubic-bezier(0.05, 0.7, 0.1, 1);
        }

        .footer-nav-item.active {
            color: var(--active-nav-color);
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
            transition: background 0.25s ease, color 0.25s ease;
        }
        .nav-item.is-active .nav-pill {
            background: rgba(34, 211, 238, 0.15);
            color: var(--active-nav-color);
        }
        body.light-theme .nav-item.is-active .nav-pill {
            background: rgba(102, 187, 106, 0.15);
        }
        .nav-label {
            font-size: 8px;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
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
        .nav-fab.is-ring {
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.2), 0 0 0 3px rgba(255, 255, 255, 0.45);
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
            .mobile-panel {
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
        
        <!-- Organic Blobs (Google Colors, Apple Blurs) -->
        <div class="absolute top-[10%] left-[10%] w-[50vw] h-[50vw] min-w-[320px] rounded-full bg-[#4285F4]/12 blur-[80px] animate-blob-1" style="opacity: var(--blob-opacity); transition: opacity 0.5s ease;"></div>
        <div class="absolute top-[30%] right-[10%] w-[45vw] h-[45vw] min-w-[300px] rounded-full bg-[#FF2D55]/10 blur-[90px] animate-blob-2" style="opacity: var(--blob-opacity); transition: opacity 0.5s ease;"></div>
        <div class="absolute bottom-[10%] left-[25%] w-[40vw] h-[40vw] min-w-[280px] rounded-full bg-[#34A853]/10 blur-[85px] animate-blob-3" style="opacity: var(--blob-opacity); transition: opacity 0.5s ease;"></div>
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
                
                <!-- Internal Screen Mesh Gradient -->
                <div class="absolute inset-0 pointer-events-none -z-10 bg-gradient-to-b from-green-500/10 via-transparent to-emerald-500/5"></div>
                <div class="absolute top-[20%] right-[-10%] w-36 h-36 rounded-full bg-[#FF2D55]/5 blur-[40px]"></div>

                <!-- 1. Header (Fixed at top) -->
                <header class="relative z-10 flex flex-col px-5 pt-4 pb-3" data-m3-region="top-app-bar">
                    @if($isSheet)
                        <div class="w-full pb-2 flex justify-center">
                            <div class="sheet-handle"></div>
                        </div>
                    @endif
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <!-- Back button -->
                            @if($backUrl)
                                <a href="{{ $backUrl }}" class="theme-toggle w-7.5 h-7.5 rounded-lg glass-card theme-border flex items-center justify-center theme-text-main hover:scale-105 active:scale-95 transition-all duration-300" aria-label="Kembali">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('attendance.dashboard') }}" class="theme-toggle w-7.5 h-7.5 rounded-lg glass-card theme-border flex items-center justify-center theme-text-main hover:scale-105 active:scale-95 transition-all duration-300" aria-label="Dashboard">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                    </svg>
                                </a>
                            @endif
                            
                            <!-- Title -->
                            <h2 class="text-xs font-black theme-text-main font-display truncate max-w-[150px]">{{ $title }}</h2>
                        </div>

                        <!-- Actions Layout: Theme Toggle & Custom slot -->
                        <div class="flex items-center gap-2">
                            @if(isset($headerAction))
                                {{ $headerAction }}
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
                        </div>
                    </div>
                </header>

                <!-- 2. Scrollable Content Body -->
                <div class="flex-1 overflow-y-auto custom-scroll px-5 pb-24 pt-1 space-y-4 mobile-panel {{ $isSheet ? 'sheet-slide-up' : '' }}" data-m3-region="content">
                    {{ $slot }}
                </div>

                <!-- 3. Bottom Navigation Bar (Fixed at bottom) -->
                @if($showNav)
                    <nav class="footer-nav relative z-10 flex items-end justify-between w-full px-2 pt-2 pb-1 mt-1" aria-label="Navigasi utama" data-m3-region="navigation-bar">
                        <!-- Beranda -->
                        <a href="{{ route('attendance.dashboard') }}" class="nav-item @if($activeTab === 'beranda') is-active @endif">
                            <span class="nav-pill">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                                </svg>
                            </span>
                            <span class="nav-label">Beranda</span>
                        </a>

                        <!-- Masuk (Selfie) -->
                        <a href="{{ route('attendance.selfie') }}" class="nav-item">
                            <span class="nav-fab nav-fab-masuk @if($activeTab === 'masuk') is-ring @endif">
                                <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </span>
                            <span class="nav-label">Masuk</span>
                        </a>

                        <!-- Pulang (Checkout) -->
                        <a href="{{ route('attendance.checkout') }}" class="nav-item">
                            <span class="nav-fab nav-fab-pulang @if($activeTab === 'pulang') is-ring @endif">
                                <svg fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </span>
                            <span class="nav-label">Pulang</span>
                        </a>

                        <!-- Riwayat -->
                        <a href="{{ route('attendance.index') }}" class="nav-item @if($activeTab === 'riwayat') is-active @endif">
                            <span class="nav-pill">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </span>
                            <span class="nav-label">Riwayat</span>
                        </a>
                    </nav>
                @endif

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

    @include('partials.pwa-update')
</body>

</html>
