@props([
    'title' => 'Absensi',
    'backUrl' => null,
    'activeTab' => null,
    'showNav' => true,
])

@php
    $user = auth()->user();
    // Resolve today's attendance status if not passed
    $todayAttendance = $todayAttendance ?? ($user ? \App\Models\Attendance::where('user_id', $user->id)
        ->whereDate('created_at', today())
        ->first() : null);
    $canCheckout = $todayAttendance && !$todayAttendance->hasCheckedOut();
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#080710">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Aplikasi Absensi Selfie dengan Verifikasi GPS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AbsenKu">
    <link rel="manifest" href="/manifest.json?v=2">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png?v=2">
    <title>AbsenKu - {{ $title }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
            --glass-card-shadow: inset 0 1px 0 0 rgba(255, 255, 255, 0.05);
            /* Solid (opaque) surface for menus/modals — not glass */
            --menu-bg: #16151f;
            --menu-border: rgba(255, 255, 255, 0.10);
            --grid-line-color: rgba(255, 255, 255, 0.012);
            --blob-opacity: 0.12;
            --phone-shell-bg: #0a0b0f;
            --phone-shell-border: rgba(255, 255, 255, 0.12);
            --footer-icon-color: #64748b;
            --active-nav-color: #22d3ee;
            
            /* Inputs */
            --input-bg: rgba(255, 255, 255, 0.03);
            --input-border: rgba(255, 255, 255, 0.1);
            --input-focus-bg: rgba(255, 255, 255, 0.05);
            
            /* Buttons */
            --btn-accent-bg: #ffffff;
            --btn-accent-text: #050409;
            --btn-accent-shadow: 0 12px 28px rgba(255, 255, 255, 0.15);
            
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
            --glass-card-shadow: 0 2px 10px rgba(100, 116, 139, 0.01), inset 0 1px 0 0 rgba(255, 255, 255, 0.95);
            /* Solid (opaque) surface for menus/modals — not glass */
            --menu-bg: #ffffff;
            --menu-border: rgba(15, 23, 42, 0.10);
            
            --grid-line-color: rgba(0, 0, 0, 0.015);
            --blob-opacity: 0.16;
            
            --phone-shell-bg: #d8dce6;
            --phone-shell-border: rgba(0, 0, 0, 0.04);
            
            --footer-icon-color: #94a3b8;
            --active-nav-color: #6366f1;
            
            /* Inputs */
            --input-bg: rgba(255, 255, 255, 0.5);
            --input-border: rgba(0, 0, 0, 0.08);
            --input-focus-bg: #ffffff;
            
            /* Buttons */
            --btn-accent-bg: #6366f1;
            --btn-accent-text: #ffffff;
            --btn-accent-shadow: 0 10px 24px rgba(99, 102, 241, 0.2);
            
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

        /* Forms inputs theme mapping */
        .theme-input {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border) !important;
            color: var(--text-main);
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        .theme-input:focus {
            background-color: var(--input-focus-bg);
            border-color: var(--active-nav-color) !important;
            outline: none;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.08);
        }

        /* Buttons Theme Mapping */
        .theme-btn-submit {
            background-color: var(--btn-accent-bg);
            color: var(--btn-accent-text);
            box-shadow: var(--btn-accent-shadow);
            transition: background-color 0.5s ease, color 0.5s ease, box-shadow 0.5s ease, transform 0.2s ease;
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
            .mobile-panel {
                transform: scale(0.94);
                transform-origin: top center;
            }
        }
    </style>

    <!-- Theme Restore (Prevents Flash of Unthemed Content) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('welcome-theme');
            if (savedTheme === 'light') {
                document.body.classList.add('light-theme');
            }
        })();
    </script>
</head>

<body class="antialiased theme-bg flex items-center justify-center">

    <!-- ════════ Background Liquid Layer ════════ -->
    <div class="fixed inset-0 -z-20 overflow-hidden pointer-events-none">
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
                            <svg class="moon-icon w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                            </svg>
                        </button>
                    </div>
                </header>

                <!-- 2. Scrollable Content Body -->
                <div class="flex-1 overflow-y-auto custom-scroll px-5 pb-24 pt-1 space-y-4 mobile-panel">
                    {{ $slot }}
                </div>

                <!-- 3. Bottom Navigation Bar (Fixed at bottom) -->
                @if($showNav)
                    <nav class="theme-border-t relative z-10 flex items-center justify-between pt-3 pb-1 mt-1 w-full bg-transparent px-3">
                        <!-- Beranda -->
                        <a href="{{ route('attendance.dashboard') }}" class="footer-nav-item @if($activeTab === 'beranda') active @endif flex-1 flex flex-col items-center gap-0.5">
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
                                <div class="w-10.5 h-10.5 rounded-full flex items-center justify-center bg-gradient-to-r from-green-400 to-emerald-400 shadow-[0_6px_16px_rgba(16,185,129,0.25)] hover:scale-105 active:scale-95 transition-transform duration-300 @if($activeTab === 'masuk') ring-2 ring-emerald-500 ring-offset-2 ring-offset-slate-900 @endif">
                                    <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                </div>
                                <span class="text-[7.5px] font-bold theme-status-ok-text mt-1 font-outfit">Masuk</span>
                            @endif
                        </a>

                        <!-- Pulang (Checkout) -->
                        <a href="{{ route('attendance.checkout') }}" class="flex-1 flex flex-col items-center -mt-4">
                            @if ($canCheckout)
                                <!-- Ready to Check Out -->
                                <div class="w-10.5 h-10.5 rounded-full flex items-center justify-center bg-gradient-to-r from-amber-400 to-orange-400 shadow-[0_6px_16px_rgba(245,158,11,0.25)] hover:scale-105 active:scale-95 transition-transform duration-300 @if($activeTab === 'pulang') ring-2 ring-amber-500 ring-offset-2 ring-offset-slate-900 @endif">
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
                        <a href="{{ route('attendance.index') }}" class="footer-nav-item @if($activeTab === 'riwayat') active @endif flex-1 flex flex-col items-center gap-0.5 hover:text-slate-400 transition-colors">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span class="text-[7.5px] font-bold font-outfit">Riwayat</span>
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
