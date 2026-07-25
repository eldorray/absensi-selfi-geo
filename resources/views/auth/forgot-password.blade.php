<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#080710">
    <meta name="description" content="Halaman reset password aplikasi absensi digital MI Daarul Hikmah.">
    <link rel="manifest" href="/manifest.json?v=3">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png?v=2">
    <title>Lupa Password - Absensi</title>
    
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
            --active-nav-color: #22d3ee;
            
            /* Inputs */
            --input-bg: rgba(255, 255, 255, 0.03);
            --input-border: rgba(255, 255, 255, 0.1);
            --input-focus-bg: rgba(255, 255, 255, 0.05);
            
            /* Buttons */
            --btn-accent-bg: #ffffff;
            --btn-accent-text: #050409;
            --btn-accent-shadow: 0 12px 28px rgba(255, 255, 255, 0.15);
        }

        body.light-theme {
            /* Colors - Light Theme (Polished iOS style) */
            --bg-color: #edf0f5;
            --screen-bg: #f4f6fa;
            --text-main: #475569;
            --text-muted: #64748b;
            
            --glass-bg: rgba(255, 255, 255, 0.75);
            --glass-border: rgba(255, 255, 255, 0.85);
            --glass-shadow: 0 12px 36px -8px rgba(100, 116, 139, 0.06), inset 0 1px 1px 0 rgba(255, 255, 255, 0.95);
            
            --glass-card-bg: rgba(255, 255, 255, 0.55);
            --glass-card-border: rgba(255, 255, 255, 0.65);
            --glass-card-shadow: 0 2px 10px rgba(100, 116, 139, 0.01), inset 0 1px 0 0 rgba(255, 255, 255, 0.95);
            
            --grid-line-color: rgba(0, 0, 0, 0.015);
            --blob-opacity: 0.16;
            
            --phone-shell-bg: #e2e4ed;
            --phone-shell-border: rgba(0, 0, 0, 0.04);
            
            --active-nav-color: #6366f1;
            
            /* Inputs */
            --input-bg: rgba(255, 255, 255, 0.5);
            --input-border: rgba(0, 0, 0, 0.08);
            --input-focus-bg: #ffffff;
            
            /* Buttons */
            --btn-accent-bg: #6366f1;
            --btn-accent-text: #ffffff;
            --btn-accent-shadow: 0 10px 24px rgba(99, 102, 241, 0.2);
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

        /* Borders Theme Mapping */
        .theme-border {
            border: 1px solid var(--glass-card-border) !important;
            transition: border-color 0.5s ease;
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

        @media (max-width: 640px) and (max-height: 760px) {
            .login-panel {
                transform: scale(0.92);
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
                    <!-- Back button to login screen -->
                    <a href="{{ route('login') }}" class="theme-toggle w-7.5 h-7.5 rounded-lg glass-card theme-border flex items-center justify-center theme-text-main hover:scale-105 active:scale-95 transition-all duration-300" aria-label="Kembali ke login">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </a>

                    <!-- App Title -->
                    <div class="text-center">
                        <span class="block text-xs font-black tracking-tight theme-text-main font-display">Absensi</span>
                        <span class="block text-[8px] font-bold tracking-wider text-cyan-500 font-outfit uppercase">Selfie Geo</span>
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
                </header>

                <!-- Login panel inner wrapper -->
                <div class="login-panel flex-1 flex flex-col justify-center min-h-0 py-4">
                    
                    <!-- Decorative Lock Icon -->
                    <div class="mb-4 flex justify-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl glass-card theme-border shadow-md">
                            <svg class="h-6.5 w-6.5" style="color: var(--active-nav-color); transition: color 0.5s ease;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Title Header -->
                    <div class="text-center mb-5">
                        <h2 class="text-lg font-black theme-text-main font-display">Lupa Password</h2>
                        <p class="text-[9px] theme-text-muted font-outfit uppercase tracking-wider mt-0.5">Kirim link reset password ke email Anda</p>
                    </div>

                    <!-- Form card -->
                    <div class="rounded-[24px] glass-card theme-border p-4.5 sm:p-5 shadow-xl">
                        @if (session('status'))
                            <div class="mb-3.5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs theme-status-ok-text">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- Email Field -->
                            <div>
                                <label class="mb-1.5 block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" autofocus
                                    class="theme-input w-full rounded-2xl px-4.5 py-3 text-[14px] shadow-sm"
                                    placeholder="Masukkan email terdaftar" required>
                                @error('email')
                                    <p class="mt-1.5 text-xs text-red-500 font-medium leading-normal">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                class="theme-btn-submit flex w-full items-center justify-center rounded-[1.4rem] px-5 py-3.5 text-xs font-bold tracking-wider uppercase hover:scale-[1.01] active:scale-[0.99] font-outfit">
                                Kirim Link Reset
                            </button>
                        </form>
                    </div>

                    <!-- Bottom back to login Link -->
                    <div class="text-center mt-6">
                        <a href="{{ route('login') }}" class="text-xs font-bold font-outfit uppercase tracking-wider hover:opacity-80 transition-opacity" style="color: var(--active-nav-color)">
                            Kembali ke Login
                        </a>
                    </div>
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
