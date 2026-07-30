<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#080710">
    <meta name="description" content="Aplikasi absensi digital dengan selfie, GPS, dan pengajuan izin online.">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AbsenKu">
    <link rel="manifest" href="/manifest.json?v=4">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png?v=2">
    <title>Absensi Selfie Geo - MI Daarul Hikmah</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Dark Theme Variables */
            --bg-color: #06070d;
            --screen-bg: #090a14;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.5), inset 0 1px 1px 0 rgba(255, 255, 255, 0.08);
            --glass-card-bg: rgba(255, 255, 255, 0.025);
            --glass-card-border: rgba(255, 255, 255, 0.07);
            --grid-line-color: rgba(255, 255, 255, 0.015);
            --blob-opacity: 0.16;
            --phone-shell-bg: #000000;
            --phone-shell-border: rgba(255, 255, 255, 0.12);
            --footer-icon-color: #64748b;
            --active-nav-color: #38bdf8;

            --viewfinder-bg: linear-gradient(145deg, rgba(15, 23, 42, 0.8), rgba(30, 27, 75, 0.5));
            --viewfinder-border: rgba(255, 255, 255, 0.08);

            --btn-accent-bg: linear-gradient(135deg, #0284c7, #4f46e5);
            --btn-accent-text: #ffffff;
            --btn-guest-bg: linear-gradient(135deg, #38bdf8, #6366f1);
            --btn-guest-text: #ffffff;
            --btn-shadow: 0 10px 24px -4px rgba(56, 189, 248, 0.3);
        }

        body.light-theme {
            /* Light Theme Variables */
            --bg-color: #e2e8f0;
            --screen-bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.95);
            --glass-shadow: 0 16px 40px -8px rgba(100, 116, 139, 0.08), inset 0 1px 1px 0 rgba(255, 255, 255, 1);
            --glass-card-bg: rgba(255, 255, 255, 0.7);
            --glass-card-border: rgba(226, 232, 240, 0.9);
            --grid-line-color: rgba(0, 0, 0, 0.02);
            --blob-opacity: 0.22;
            --phone-shell-bg: #cbd5e1;
            --phone-shell-border: rgba(0, 0, 0, 0.06);
            --footer-icon-color: #94a3b8;
            --active-nav-color: #4f46e5;

            --viewfinder-bg: linear-gradient(145deg, rgba(241, 245, 249, 0.9), rgba(238, 242, 255, 0.7));
            --viewfinder-border: rgba(226, 232, 240, 0.9);

            --btn-accent-bg: linear-gradient(135deg, #4f46e5, #0284c7);
            --btn-accent-text: #ffffff;
            --btn-guest-bg: linear-gradient(135deg, #4f46e5, #0284c7);
            --btn-guest-text: #ffffff;
            --btn-shadow: 0 10px 24px -4px rgba(79, 70, 229, 0.25);
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            overscroll-behavior: none;
            width: 100%;
            margin: 0;
            padding: 0;
            transition: background-color 0.4s ease;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
        }

        .font-display {
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        .phone-shell {
            background-color: var(--phone-shell-bg);
            border-color: var(--phone-shell-border);
            transition: all 0.4s ease;
        }

        .screen-content {
            background-color: var(--screen-bg);
            transition: background-color 0.4s ease;
        }

        .bg-grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(var(--grid-line-color) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line-color) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, #000 60%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, #000 60%, transparent 100%);
        }

        .glass-card {
            background: var(--glass-card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-card-border);
            transition: all 0.3s ease;
        }

        .viewfinder-canvas {
            background: var(--viewfinder-bg);
            border: 1px solid var(--viewfinder-border);
            transition: all 0.4s ease;
        }

        .footer-nav-item {
            color: var(--footer-icon-color);
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .footer-nav-item.active {
            color: var(--active-nav-color);
        }

        .theme-toggle .sun-icon {
            display: block;
        }

        .theme-toggle .moon-icon {
            display: none;
        }

        body.light-theme .theme-toggle .sun-icon {
            display: none;
        }

        body.light-theme .theme-toggle .moon-icon {
            display: block;
        }

        /* Floating Blobs Animation */
        @keyframes float-blob {

            0%,
            100% {
                transform: translate(0px, 0px) scale(1);
            }

            50% {
                transform: translate(25px, -35px) scale(1.08);
            }
        }

        @keyframes float-blob-reverse {

            0%,
            100% {
                transform: translate(0px, 0px) scale(1);
            }

            50% {
                transform: translate(-25px, 25px) scale(0.95);
            }
        }

        .animate-blob-1 {
            animation: float-blob 24s infinite ease-in-out;
        }

        .animate-blob-2 {
            animation: float-blob-reverse 20s infinite ease-in-out;
        }

        /* Camera Scanner Ring Animation */
        @keyframes pulse-ring {

            0%,
            100% {
                transform: scale(0.92);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.06);
                opacity: 0.3;
            }
        }

        .animate-ring-pulse {
            animation: pulse-ring 3.5s ease-in-out infinite;
        }
    </style>

    <!-- Theme Restore -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('welcome-theme');
            if (savedTheme === 'light') {
                document.body.classList.add('light-theme');
            }
        })();
    </script>
</head>

<body class="antialiased flex items-center justify-center">

    <!-- ════════ Ambient Background Liquid Layer ════════ -->
    <div class="fixed inset-0 -z-20 overflow-hidden pointer-events-none">
        <div class="bg-grid-overlay"></div>

        <!-- Soft Color Blobs -->
        <div class="absolute top-[12%] left-[15%] w-[45vw] h-[45vw] min-w-[300px] rounded-full bg-sky-500/15 blur-[90px] animate-blob-1"
            style="opacity: var(--blob-opacity);"></div>
        <div class="absolute bottom-[15%] right-[15%] w-[40vw] h-[40vw] min-w-[280px] rounded-full bg-indigo-500/15 blur-[90px] animate-blob-2"
            style="opacity: var(--blob-opacity);"></div>
    </div>

    <!-- ════════ Center Phone Viewport Container ════════ -->
    <main class="w-full h-[100svh] sm:w-[380px] sm:h-[780px] flex items-center justify-center p-0 sm:p-3">

        <!-- Phone Outer Shell (Desktop mockup frame) -->
        <div
            class="phone-shell relative w-full h-full sm:rounded-[48px] sm:p-2.5 sm:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] sm:border flex flex-col justify-between">

            <!-- Dynamic Island (Desktop mockup) -->
            <div
                class="hidden sm:flex absolute top-6.5 left-1/2 -translate-x-1/2 w-24 h-5 bg-black rounded-full z-50 items-center justify-end pr-3">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-400 shadow-[0_0_8px_#38bdf8]"></span>
            </div>

            <!-- Speaker Ear Piece (Desktop mockup) -->
            <div
                class="hidden sm:block absolute top-4 left-1/2 -translate-x-1/2 w-10 h-0.75 bg-neutral-900 rounded-full z-50">
            </div>

            <!-- Phone Screen Content -->
            <div
                class="screen-content relative h-full w-full sm:rounded-[38px] overflow-hidden flex flex-col justify-between p-5 border border-transparent sm:border-white/5">

                <!-- Internal Screen Gradient Accent -->
                <div
                    class="absolute inset-0 pointer-events-none -z-10 bg-gradient-to-b from-sky-500/5 via-transparent to-indigo-500/5">
                </div>

                <!-- 1. App Header -->
                <header class="relative z-10 flex items-center justify-between mt-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group"
                        aria-label="Absensi Selfie Geo">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl glass-card shadow-sm group-hover:scale-105 transition-transform duration-300">
                            <img src="/images/icons/icon-192.png?v=2" alt="Logo" class="h-5.5 w-5.5 object-contain">
                        </span>
                        <span class="leading-none text-left">
                            <span
                                class="block text-xs font-bold tracking-tight font-display text-[var(--text-main)]">Absensi</span>
                            <span
                                class="block text-[8px] font-bold tracking-wider text-sky-400 font-outfit uppercase">Selfie
                                Geo</span>
                        </span>
                    </a>

                    <!-- Instansi & Theme Toggle -->
                    <div class="flex items-center gap-2">
                        <div class="text-right hidden xs:block">
                            <p class="text-[8px] text-[var(--text-muted)] font-outfit">Instansi</p>
                            <p class="text-[9px] font-bold text-[var(--text-main)] font-outfit">MI Daarul Hikmah</p>
                        </div>

                        <!-- Theme Toggle Button -->
                        <button onclick="toggleTheme()"
                            class="theme-toggle w-8 h-8 rounded-xl glass-card flex items-center justify-center text-amber-400 hover:scale-105 active:scale-95 transition-all duration-300"
                            aria-label="Toggle Theme">
                            <svg class="sun-icon w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                            </svg>
                            <svg class="moon-icon w-4 h-4 text-indigo-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                        </button>
                    </div>
                </header>

                <!-- 2. Greeting Section -->
                <div class="relative z-10 text-left mt-3">
                    @auth
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-full bg-gradient-to-tr from-sky-400 to-indigo-500 p-[1.5px] shadow-sm">
                                <div
                                    class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center text-[11px] font-bold text-white font-outfit">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                            </div>
                            <div>
                                <p class="text-[9px] text-[var(--text-muted)] font-outfit">Selamat datang kembali,</p>
                                <p class="text-sm font-bold text-[var(--text-main)] font-display leading-tight">
                                    {{ auth()->user()->name }}</p>
                            </div>
                        </div>
                    @else
                        <div>
                            <div
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full glass-card text-[9px] font-bold font-outfit text-sky-400 border border-sky-500/20 mb-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>MI Daarul Hikmah</span>
                            </div>
                            <h2 class="text-lg font-extrabold text-[var(--text-main)] font-display leading-tight">Presensi
                                Digital Selfie</h2>
                        </div>
                    @endauth
                </div>

                <!-- 3. Clean Viewfinder Camera Simulation -->
                <div
                    class="relative flex-1 rounded-[22px] overflow-hidden glass-card p-2 flex flex-col justify-between my-3 min-h-[200px]">
                    <div
                        class="viewfinder-canvas relative flex-1 rounded-[16px] overflow-hidden flex flex-col items-center justify-center p-4">

                        <!-- Pulsating Face Guide Frame Ring -->
                        <div
                            class="absolute w-32 h-32 rounded-full border border-sky-400/30 animate-ring-pulse flex items-center justify-center">
                            <div class="w-24 h-24 rounded-full border border-indigo-400/20"></div>
                        </div>

                        <!-- Central Active Camera Avatar -->
                        <div
                            class="relative z-10 w-14 h-14 rounded-2xl bg-sky-500/10 border border-sky-400/30 flex items-center justify-center text-sky-400 shadow-md">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                        </div>

                        <span
                            class="relative z-10 text-[9px] font-bold tracking-widest uppercase text-sky-400 font-outfit mt-3">
                            Selfie & GPS Siap
                        </span>
                    </div>

                    <!-- Viewfinder Footer Info -->
                    <div
                        class="flex items-center justify-between mt-2 px-1 text-[10px] font-semibold text-[var(--text-muted)] font-outfit">
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            GPS Geofencing Terkalibrasi
                        </span>
                        <span>{{ now()->locale('id')->isoFormat('dddd') }}</span>
                    </div>
                </div>

                <!-- 4. Clean Quick Highlights Grid -->
                <div class="grid grid-cols-3 gap-2 text-center mb-3">
                    <div class="rounded-xl glass-card py-2 px-1">
                        <p class="text-[10px] font-bold font-outfit text-sky-400">GPS</p>
                        <p class="text-[8px] text-[var(--text-muted)] uppercase tracking-wider font-outfit mt-0.5">
                            Terkunci</p>
                    </div>
                    <div class="rounded-xl glass-card py-2 px-1">
                        <p class="text-[10px] font-bold font-outfit text-emerald-400">Swafoto</p>
                        <p class="text-[8px] text-[var(--text-muted)] uppercase tracking-wider font-outfit mt-0.5">Aktif
                        </p>
                    </div>
                    <div class="rounded-xl glass-card py-2 px-1">
                        <p class="text-[10px] font-bold font-outfit text-amber-400">Izin</p>
                        <p class="text-[8px] text-[var(--text-muted)] uppercase tracking-wider font-outfit mt-0.5">
                            Online</p>
                    </div>
                </div>

                <!-- 5. Primary Action Button -->
                <div class="relative z-10 w-full mb-1">
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('attendance.dashboard') }}"
                            class="group relative flex w-full items-center justify-center overflow-hidden rounded-2xl py-3.5 px-4 font-bold text-white shadow-md hover:scale-[1.01] transition-all duration-300"
                            style="background: var(--btn-accent-bg); box-shadow: var(--btn-shadow);">
                            <span class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase font-outfit">
                                Buka Dashboard
                                <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"
                                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="group relative flex w-full items-center justify-center overflow-hidden rounded-2xl py-3.5 px-4 font-bold text-white shadow-md hover:scale-[1.01] transition-all duration-300"
                            style="background: var(--btn-guest-bg); box-shadow: var(--btn-shadow);">
                            <span class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase font-outfit">
                                Masuk Sistem
                                <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"
                                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                        </a>
                    @endauth
                </div>

                <!-- Screen Footer -->
                <div class="relative z-10 text-center pt-2 pb-1 border-t border-[var(--glass-card-border)]">
                    <p class="text-[9px] font-semibold text-[var(--text-muted)] font-outfit">
                        &copy; {{ date('Y') }} YPDH Al Madani • Absensi Selfie Geo
                    </p>
                </div>


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
