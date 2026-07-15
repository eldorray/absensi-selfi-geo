<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#080710">
    <meta name="description" content="Aplikasi absensi digital dengan selfie, GPS, dan pengajuan izin online.">
    <link rel="manifest" href="/manifest.json?v=2">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png?v=2">
    <title>Absensi Selfie Geo</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            /* Colors - Dark Theme (Default) */
            --bg-color: #050409;
            --screen-bg: #080711;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.12);
            --glass-shadow: 0 24px 80px -15px rgba(0, 0, 0, 0.5), inset 0 1px 1px 0 rgba(255, 255, 255, 0.1);
            --glass-card-bg: rgba(255, 255, 255, 0.02);
            --glass-card-border: rgba(255, 255, 255, 0.08);
            --glass-card-shadow: inset 0 1px 0 0 rgba(255, 255, 255, 0.05);
            --grid-line-color: rgba(255, 255, 255, 0.012);
            --blob-opacity: 0.12;
            --phone-shell-bg: #000000;
            --phone-shell-border: rgba(255, 255, 255, 0.12);
            --map-grid-color: rgba(255, 255, 255, 0.02);
            --footer-icon-color: #64748b;
            
            /* Viewfinder */
            --viewfinder-bg: linear-gradient(135deg, rgba(30, 27, 75, 0.7), rgba(15, 23, 42, 1));
            --viewfinder-border: rgba(255, 255, 255, 0.05);
            --scan-accent: #22d3ee;
            --scan-accent-dim: rgba(34, 211, 238, 0.25);
            --active-nav-color: #22d3ee;
            
            /* Buttons */
            --btn-accent-bg: #0f172a;
            --btn-accent-text: #ffffff;
            --btn-guest-bg: #ffffff;
            --btn-guest-text: #0f172a;
            --btn-guest-shadow: 0 12px 28px rgba(255, 255, 255, 0.15);
            
            /* Statuses */
            --status-ok-text: #34d399;
            --status-ok-bg: rgba(16, 185, 129, 0.1);
            --status-ok-border: rgba(16, 185, 129, 0.2);
            --stats-gps-text: #06b6d4;
            --stats-selfie-text: #10b981;
            --stats-izin-text: #f59e0b;
        }

        body.light-theme {
            /* Colors - Light Theme (Polished iOS style) */
            --bg-color: #edf0f5; /* Calming soft grey-blue page backdrop */
            --screen-bg: #f4f6fa; /* Clean light screen */
            --text-main: #475569; /* Soft grey-slate text for reading comfort */
            --text-muted: #64748b; /* Slate-500 */
            
            /* Clean White Glass panels with a very soft blur and gentle drop shadow */
            --glass-bg: rgba(255, 255, 255, 0.75);
            --glass-border: rgba(255, 255, 255, 0.85);
            --glass-shadow: 0 12px 36px -8px rgba(100, 116, 139, 0.06), inset 0 1px 1px 0 rgba(255, 255, 255, 0.95);
            
            --glass-card-bg: rgba(255, 255, 255, 0.55);
            --glass-card-border: rgba(255, 255, 255, 0.65);
            --glass-card-shadow: 0 2px 10px rgba(100, 116, 139, 0.01), inset 0 1px 0 0 rgba(255, 255, 255, 0.95);
            
            --grid-line-color: rgba(0, 0, 0, 0.015);
            --blob-opacity: 0.16;
            
            /* Mockup phone frame blends elegantly */
            --phone-shell-bg: #e2e4ed;
            --phone-shell-border: rgba(0, 0, 0, 0.04);
            
            --map-grid-color: rgba(0, 0, 0, 0.015);
            --footer-icon-color: #94a3b8;
            
            /* Viewfinder: Soft light indigo-tinted camera block instead of stark dark card */
            --viewfinder-bg: linear-gradient(135deg, rgba(255, 255, 255, 0.65), rgba(240, 244, 255, 0.45));
            --viewfinder-border: rgba(255, 255, 255, 0.85);
            
            --scan-accent: #6366f1; /* Soft Indigo */
            --scan-accent-dim: rgba(99, 102, 241, 0.15);
            --active-nav-color: #6366f1;
            
            /* Buttons */
            --btn-accent-bg: #6366f1;
            --btn-accent-text: #ffffff;
            --btn-guest-bg: #6366f1;
            --btn-guest-text: #ffffff;
            --btn-guest-shadow: 0 10px 24px rgba(99, 102, 241, 0.2);
            
            /* Statuses - Softer greens/oranges for readability */
            --status-ok-text: #047857;
            --status-ok-bg: rgba(52, 211, 153, 0.14);
            --status-ok-border: rgba(52, 211, 153, 0.25);
            --stats-gps-text: #4f46e5;
            --stats-selfie-text: #059669;
            --stats-izin-text: #ca8a04;
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

        .viewfinder-canvas {
            background: var(--viewfinder-bg);
            border: 1px solid var(--viewfinder-border) !important;
            transition: background 0.5s ease, border-color 0.5s ease;
        }

        .map-grid {
            background-image: radial-gradient(var(--map-grid-color) 1px, transparent 1px);
            background-size: 8px 8px;
            transition: background-image 0.5s ease;
        }

        .footer-nav-item {
            color: var(--footer-icon-color);
            transition: color 0.5s ease;
        }

        .footer-nav-item.active {
            color: var(--active-nav-color);
        }

        /* Active Scanner Theme Elements */
        .theme-scanline {
            background-color: var(--scan-accent);
            box-shadow: 0 0 12px var(--scan-accent);
            transition: background-color 0.5s ease, box-shadow 0.5s ease;
        }
        .theme-guideline {
            border-color: var(--scan-accent-dim);
            transition: border-color 0.5s ease;
        }
        .theme-radar-color {
            color: var(--scan-accent-dim);
            transition: color 0.5s ease;
        }
        .theme-brackets {
            border-color: var(--scan-accent);
            transition: border-color 0.5s ease;
        }

        /* Buttons Theme Mapping */
        .theme-btn-accent {
            background-color: var(--btn-accent-bg);
            color: var(--btn-accent-text);
            transition: background-color 0.5s ease, color 0.5s ease;
        }
        .theme-btn-guest {
            background-color: var(--btn-guest-bg);
            color: var(--btn-guest-text);
            box-shadow: var(--btn-guest-shadow);
            transition: background-color 0.5s ease, color 0.5s ease, box-shadow 0.5s ease;
        }

        /* Statuses Theme Mapping */
        .theme-status-ok-text {
            color: var(--status-ok-text);
            transition: color 0.5s ease;
        }
        .theme-status-ok-card {
            background-color: var(--status-ok-bg);
            border-color: var(--status-ok-border) !important;
            transition: background-color 0.5s ease, border-color 0.5s ease;
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

        /* Sun / Moon Toggle Button Icon display */
        .theme-toggle .sun-icon { display: block; }
        .theme-toggle .moon-icon { display: none; }
        
        body.light-theme .theme-toggle .sun-icon { display: none; }
        body.light-theme .theme-toggle .moon-icon { display: block; }

        .theme-toggle {
            transition: transform 0.3s ease, background 0.5s ease, border-color 0.5s ease;
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

        /* Viewfinder Animations */
        @keyframes scanline {
            0%, 100% { top: 12%; opacity: 0.8; }
            50% { top: 82%; opacity: 0.8; }
        }

        @keyframes pulse-ring {
            0% { transform: translate(-50%, -50%) scale(0.4); opacity: 0.8; }
            100% { transform: translate(-50%, -50%) scale(2.2); opacity: 0; }
        }

        @keyframes radar-rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .animate-scanline {
            animation: scanline 3s ease-in-out infinite;
        }

        .animate-pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }

        .animate-radar {
            animation: radar-rotate 4.5s linear infinite;
        }

        @media (max-width: 640px) and (max-height: 760px) {
            .attendance-preview {
                transform: scale(0.78);
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

            <!-- Phone Screen Content (Full viewport on mobile, scroll locked) -->
            <div class="screen-content relative h-full w-full sm:rounded-[38px] overflow-hidden flex flex-col justify-between p-5 border border-transparent sm:border-white/5">
                
                <!-- Internal Screen Mesh Gradient -->
                <div class="absolute inset-0 pointer-events-none -z-10 bg-gradient-to-b from-indigo-500/10 via-transparent to-cyan-500/5"></div>
                <div class="absolute top-[20%] right-[-10%] w-36 h-36 rounded-full bg-[#FF2D55]/6 blur-[40px]"></div>

                <!-- App Header -->
                <header class="relative z-10 flex items-center justify-between mt-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group" aria-label="Absensi Selfie Geo">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl glass-card theme-border shadow-[0_4px_12px_rgba(0,0,0,0.02)]">
                            <img src="/images/icons/icon-192.png?v=2" alt="" class="h-5.5 w-5.5">
                        </span>
                        <span class="leading-none text-left">
                            <span class="block text-xs font-black tracking-tight theme-text-main font-display">Absensi</span>
                            <span class="block text-[8px] font-bold tracking-wider text-cyan-500 font-outfit uppercase">Selfie Geo</span>
                        </span>
                    </a>

                    <!-- Instansi & Theme Toggle Container -->
                    <div class="flex items-center gap-2">
                        <div class="text-right hidden xs:block">
                            <p class="text-[8px] theme-text-muted">Instansi</p>
                            <p class="text-[9px] font-bold theme-text-main font-outfit">MI Daarul Hikmah</p>
                        </div>
                        
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

                <!-- Welcome Header / User Profile greeting -->
                <div class="relative z-10 text-left mt-3">
                    @auth
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-cyan-400 to-emerald-400 p-[1.5px] shadow-[0_4px_12px_rgba(6,182,212,0.15)]">
                                <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center text-[12px] font-bold text-white font-outfit">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                            </div>
                            <div>
                                <p class="text-[9px] theme-text-muted font-outfit">Selamat datang,</p>
                                <p class="text-sm font-bold theme-text-main font-display leading-tight">{{ auth()->user()->name }}</p>
                            </div>
                        </div>
                    @else
                        <div>
                            <p class="text-[9px] text-cyan-500 font-bold uppercase tracking-wider font-outfit">Presensi Digital</p>
                            <p class="text-lg font-black theme-text-main font-display leading-tight mt-0.5">Sistem Absensi Selfie</p>
                        </div>
                    @endauth
                </div>

                <!-- Main Viewfinder / Simulator -->
                <div class="relative flex-1 rounded-[24px] overflow-hidden glass-card theme-border p-2 flex flex-col justify-between my-3 min-h-[180px]">
                    <!-- Camera Viewport Canvas -->
                    <div class="viewfinder-canvas relative flex-1 rounded-[18px] overflow-hidden flex items-center justify-center">
                        
                        <!-- Scanning Sweeper Line -->
                        <div class="theme-scanline absolute left-2.5 right-2.5 h-[1.5px] rounded animate-scanline"></div>
                        
                        <!-- Face Guideline Circles -->
                        <div class="absolute inset-4 border border-dashed border-slate-500/10 rounded-full flex items-center justify-center">
                            <div class="theme-guideline w-[85%] h-[85%] border border-dashed rounded-full"></div>
                        </div>

                        <!-- Rotating Radar Scanner Vector -->
                        <svg class="theme-radar-color absolute inset-0 w-full h-full animate-radar" viewBox="0 0 100 100" fill="none">
                            <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="1" stroke-dasharray="8 200" stroke-linecap="round"/>
                        </svg>

                        <!-- Central Active Scanner Logo -->
                        <div class="relative z-10 flex flex-col items-center" style="color: var(--scan-accent); transition: color 0.5s ease;">
                            <svg class="w-9 h-9 filter drop-shadow-[0_0_8px_rgba(34,211,238,0.2)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z"/>
                            </svg>
                            <span class="text-[8px] tracking-widest uppercase mt-2.5 font-black font-outfit">Deteksi Aktif</span>
                        </div>
                        
                        <!-- Frame Brackets -->
                        <div class="theme-brackets absolute top-2.5 left-2.5 w-3.5 h-3.5 border-t border-l rounded-tl-sm"></div>
                        <div class="theme-brackets absolute top-2.5 right-2.5 w-3.5 h-3.5 border-t border-r rounded-tr-sm"></div>
                        <div class="theme-brackets absolute bottom-2.5 left-2.5 w-3.5 h-3.5 border-b border-l rounded-bl-sm"></div>
                        <div class="theme-brackets absolute bottom-2.5 right-2.5 w-3.5 h-3.5 border-b border-r rounded-br-sm"></div>
                    </div>

                    <!-- Simulator camera status footer -->
                    <div class="flex items-center justify-between mt-2 px-1 text-[10px] font-semibold theme-text-muted font-outfit">
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_#10b981]"></span>
                            Kamera Terkalibrasi
                        </span>
                        <span>{{ now()->locale('id')->isoFormat('dddd') }}</span>
                    </div>
                </div>

                <!-- GPS Status / Map Card -->
                <div class="rounded-[20px] glass-card theme-border p-3 flex items-center justify-between gap-3 relative overflow-hidden mb-2">
                    <div class="map-grid absolute inset-0 -z-10"></div>
                    
                    <div class="flex items-center gap-2.5">
                        <div class="relative w-8 h-8 flex items-center justify-center flex-none">
                            <span class="absolute inset-0 rounded-full bg-emerald-400/20 animate-pulse-ring"></span>
                            <div class="theme-status-ok-card w-8 h-8 rounded-lg border flex items-center justify-center theme-status-ok-text">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="leading-tight text-left">
                            <p class="text-[11px] font-bold theme-text-main">Validasi GPS</p>
                            <p class="text-[8px] theme-text-muted font-outfit">24 m dari koordinat instansi</p>
                        </div>
                    </div>
                    <span class="theme-status-ok-card theme-status-ok-text rounded-full border px-2 py-0.5 text-[8px] font-bold font-outfit">
                        TERKUNCI
                    </span>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-3 gap-2 text-center mb-3">
                    <div class="rounded-xl theme-border py-1.5 glass-card">
                        <p class="text-[10px] font-bold font-outfit" style="color: var(--stats-gps-text); transition: color 0.5s ease;">GPS</p>
                        <p class="text-[7.5px] theme-text-muted uppercase tracking-wider font-outfit mt-0.5">Valid</p>
                    </div>
                    <div class="rounded-xl theme-border py-1.5 glass-card">
                        <p class="text-[10px] font-bold font-outfit" style="color: var(--stats-selfie-text); transition: color 0.5s ease;">Selfie</p>
                        <p class="text-[7.5px] theme-text-muted uppercase tracking-wider font-outfit mt-0.5">Aktif</p>
                    </div>
                    <div class="rounded-xl theme-border py-1.5 glass-card">
                        <p class="text-[10px] font-bold font-outfit" style="color: var(--stats-izin-text); transition: color 0.5s ease;">Izin</p>
                        <p class="text-[7.5px] theme-text-muted uppercase tracking-wider font-outfit mt-0.5">Online</p>
                    </div>
                </div>

                <!-- Call-to-Action Buttons -->
                <div class="relative z-10 w-full mb-1">
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('attendance.dashboard') }}"
                            class="group relative flex w-full items-center justify-between overflow-hidden rounded-[1.4rem] bg-gradient-to-r from-cyan-500 to-emerald-500 p-[1px] font-bold text-white shadow-[0_12px_28px_rgba(6,182,212,0.25)] transition-all duration-300 hover:scale-[1.01]">
                            <span class="theme-btn-accent flex w-full items-center justify-center gap-1.5 rounded-[1.35rem] px-4 py-3.5 text-xs font-semibold tracking-wider uppercase transition-all duration-300 font-outfit">
                                Buka Dashboard
                                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="theme-btn-guest group relative flex w-full items-center justify-center overflow-hidden rounded-[1.4rem] px-4 py-3.5 text-xs font-bold tracking-wider uppercase transition-all duration-300 hover:scale-[1.01]">
                            Masuk Sekarang
                            <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @endauth
                </div>

                <!-- Mock App Bottom Navigation Bar -->
                <footer class="theme-border-t relative z-10 flex items-center justify-between pt-3 pb-1 mt-1">
                    <button class="footer-nav-item active flex-1 flex flex-col items-center gap-0.5">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        <span class="text-[7.5px] font-bold font-outfit">Beranda</span>
                    </button>
                    <button class="footer-nav-item flex-1 flex flex-col items-center gap-0.5 hover:text-slate-400 transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[7.5px] font-semibold font-outfit">Riwayat</span>
                    </button>
                    <button class="footer-nav-item flex-1 flex flex-col items-center gap-0.5 hover:text-slate-400 transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"/></svg>
                        <span class="text-[7.5px] font-semibold font-outfit">Izin</span>
                    </button>
                    <button class="footer-nav-item flex-1 flex flex-col items-center gap-0.5 hover:text-slate-400 transition-colors">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <span class="text-[7.5px] font-semibold font-outfit">Profil</span>
                    </button>
                </footer>

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
