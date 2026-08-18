<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    @php
        $branding = \App\Models\ApplicationSetting::current();
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7fbf5">
    <meta name="description" content="Aplikasi presensi MI Daarul Hikmah dengan verifikasi swafoto dan lokasi.">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AbsenKu">
    <link rel="manifest" href="{{ route('manifest') }}">
    <link rel="icon" href="{{ $branding->iconUrl() }}">
    <link rel="apple-touch-icon" href="{{ $branding->iconUrl() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>AbsenKu | MI Daarul Hikmah</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem('welcome-theme');
            const theme = savedTheme === 'dark' ? 'dark' : 'light';
            document.documentElement.dataset.theme = theme;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            color-scheme: light;
            --md-sys-color-primary: #176b43;
            --md-sys-color-on-primary: #ffffff;
            --md-sys-color-primary-container: #b7f2cd;
            --md-sys-color-on-primary-container: #002113;
            --md-sys-color-secondary-container: #d4e8d9;
            --md-sys-color-on-secondary-container: #102018;
            --md-sys-color-surface: #f7fbf5;
            --md-sys-color-surface-container-low: #eef5ee;
            --md-sys-color-surface-container: #e8f0e8;
            --md-sys-color-surface-container-high: #dfe9e0;
            --md-sys-color-on-surface: #171d19;
            --md-sys-color-on-surface-variant: #3e4a42;
            --md-sys-color-outline: #6f7a72;
            --md-sys-color-outline-variant: #bec9c0;
            --md-sys-color-focus: #004d2e;
            --md-sys-color-shadow: rgba(23, 107, 67, 0.18);
            --page-accent: #075e38;
            --device-stage: #e8f0e8;
            --phone-shell: #d4ded5;
            --phone-shell-border: rgba(23, 29, 25, 0.12);
        }

        :root[data-theme='dark'] {
            color-scheme: dark;
            --md-sys-color-primary: #9bd5b5;
            --md-sys-color-on-primary: #003822;
            --md-sys-color-primary-container: #145236;
            --md-sys-color-on-primary-container: #b7f2cd;
            --md-sys-color-secondary-container: #344b3e;
            --md-sys-color-on-secondary-container: #d0e8d8;
            --md-sys-color-surface: #0f1712;
            --md-sys-color-surface-container-low: #141f18;
            --md-sys-color-surface-container: #19261e;
            --md-sys-color-surface-container-high: #223228;
            --md-sys-color-on-surface: #e5efe7;
            --md-sys-color-on-surface-variant: #bdc9bf;
            --md-sys-color-outline: #899e8e;
            --md-sys-color-outline-variant: #3d4b40;
            --md-sys-color-focus: #b7f2cd;
            --md-sys-color-shadow: rgba(0, 0, 0, 0.38);
            --page-accent: #b7f2cd;
            --device-stage: #0b100d;
            --phone-shell: #080d0a;
            --phone-shell-border: rgba(229, 239, 231, 0.14);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html { min-height: 100%; background: var(--md-sys-color-surface); }

        body {
            min-width: 320px;
            min-height: 100svh;
            margin: 0;
            overflow-x: hidden;
            display: grid;
            place-items: center;
            background: var(--device-stage);
            color: var(--md-sys-color-on-surface);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            transition: background-color 180ms ease, color 180ms ease;
        }

        button, a { font: inherit; }

        .phone-shell {
            position: relative;
            width: 100%;
            min-height: 100svh;
            background: var(--md-sys-color-surface);
        }

        .dynamic-island { display: none; }

        .page {
            width: 100%;
            min-height: 100svh;
            margin-inline: auto;
            padding: max(16px, env(safe-area-inset-top)) 16px max(16px, env(safe-area-inset-bottom));
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .app-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand { display: flex; align-items: center; gap: 12px; min-width: 0; }

        .brand-mark {
            width: 48px;
            height: 48px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 16px 16px 16px 6px;
            background: var(--md-sys-color-primary-container);
        }

        .brand-mark img { width: 30px; height: 30px; object-fit: contain; }
        .brand-copy { min-width: 0; }
        .brand-name { margin: 0; font-size: 1rem; font-weight: 800; letter-spacing: -0.02em; }
        .organization { margin: 2px 0 0; color: var(--md-sys-color-on-surface-variant); font-size: 0.75rem; font-weight: 600; }

        .icon-button {
            width: 48px;
            height: 48px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 50%;
            background: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-on-surface);
            cursor: pointer;
            transition: background-color 150ms ease, transform 150ms ease;
        }

        .icon-button:hover { background: var(--md-sys-color-secondary-container); }
        .icon-button:active { transform: scale(0.94); }
        .icon-button svg { width: 22px; height: 22px; }
        .moon-icon, :root[data-theme='dark'] .sun-icon { display: none; }
        :root[data-theme='dark'] .moon-icon { display: block; }

        .gateway {
            flex: 1;
            display: grid;
            align-content: center;
            gap: 12px;
        }

        .hero {
            position: relative;
            min-height: 300px;
            overflow: hidden;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 24px;
            border-radius: 32px 32px 32px 10px;
            background: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
        }

        .hero-copy { position: relative; z-index: 1; max-width: 690px; }
        .context-label { margin: 0 0 14px; color: var(--page-accent); font-size: 0.8rem; font-weight: 800; }
        .hero h1 { margin: 0; max-width: 760px; font-size: clamp(2rem, 10vw, 3rem); line-height: 1.02; letter-spacing: -0.045em; }
        .hero-description { max-width: 600px; margin: 16px 0 0; font-size: 0.9375rem; line-height: 1.55; }

        .camera-motif {
            width: 64px;
            height: 64px;
            align-self: flex-end;
            display: grid;
            place-items: center;
            border-radius: 22px 22px 22px 8px;
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
        }

        .camera-motif svg { width: 32px; height: 32px; }

        .action-panel {
            padding: 16px;
            display: grid;
            gap: 16px;
            border-radius: 24px 24px 10px 24px;
            background: var(--md-sys-color-surface-container-low);
        }

        .capabilities { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }

        .capability {
            min-height: 76px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 16px;
            background: var(--md-sys-color-secondary-container);
            color: var(--md-sys-color-on-secondary-container);
        }

        .capability svg { width: 24px; height: 24px; flex: 0 0 auto; }
        .capability span { font-size: 0.875rem; font-weight: 700; line-height: 1.25; }

        .welcome-back { margin: 0; color: var(--md-sys-color-on-surface-variant); font-size: 0.875rem; line-height: 1.5; }
        .welcome-back strong { color: var(--md-sys-color-on-surface); }

        .primary-action {
            min-height: 56px;
            padding: 14px 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            box-shadow: 0 8px 22px var(--md-sys-color-shadow);
            font-weight: 800;
            text-decoration: none;
            transition: box-shadow 150ms ease, transform 150ms ease;
        }

        .primary-action:hover { box-shadow: 0 10px 26px var(--md-sys-color-shadow); transform: translateY(-1px); }
        .primary-action:active { transform: scale(0.98); }

        :where(a, button):focus-visible {
            outline: 3px solid var(--md-sys-color-focus);
            outline-offset: 4px;
        }

        footer { color: var(--md-sys-color-on-surface-variant); font-size: 0.75rem; text-align: center; }

        @media (min-width: 800px) {
            .phone-shell {
                width: 395px;
                height: min(780px, calc(100svh - 32px));
                min-height: 0;
                padding: 10px;
                overflow: hidden;
                border: 1px solid var(--phone-shell-border);
                border-radius: 48px;
                background: var(--phone-shell);
                box-shadow: 0 28px 70px rgba(10, 28, 17, 0.22);
            }

            .dynamic-island {
                position: absolute;
                z-index: 2;
                top: 22px;
                left: 50%;
                width: 96px;
                height: 22px;
                display: block;
                border-radius: 999px;
                background: #050806;
                transform: translateX(-50%);
            }

            .page {
                height: 100%;
                min-height: 0;
                overflow-y: auto;
                padding: 28px 16px 16px;
                border-radius: 38px;
                background: var(--md-sys-color-surface);
                scrollbar-width: none;
            }

            .page::-webkit-scrollbar { display: none; }
        }

        @media (max-width: 359px) {
            .page { padding-inline: 12px; }
            .organization { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .hero { min-height: 280px; padding: 20px; }
            .hero h1 { font-size: 1.8rem; }
            .capabilities { grid-template-columns: 1fr; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body>
    <div class="phone-shell">
        <span class="dynamic-island" aria-hidden="true"></span>
        <div class="page">
        <header class="app-bar">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">
                    <img src="{{ $branding->logoUrl() ?? $branding->iconUrl() }}" alt="">
                </span>
                <div class="brand-copy">
                    <p class="brand-name">AbsenKu</p>
                    <p class="organization">MI Daarul Hikmah</p>
                </div>
            </div>

            <button type="button" class="icon-button" data-theme-toggle aria-label="Aktifkan tema gelap" title="Aktifkan tema gelap">
                <svg class="sun-icon" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
                </svg>
                <svg class="moon-icon" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.4 15.2A8.5 8.5 0 0 1 8.8 3.6 8.5 8.5 0 1 0 20.4 15.2Z"></path>
                </svg>
            </button>
        </header>

        <main class="gateway">
            <section class="hero" aria-labelledby="welcome-heading">
                <div class="hero-copy">
                    <p class="context-label">Presensi MI Daarul Hikmah</p>
                    <h1 id="welcome-heading">Presensi yang siap saat Anda tiba.</h1>
                    <p class="hero-description">AbsenKu memeriksa swafoto dan lokasi ketika Anda memulai presensi.</p>
                </div>

                <div class="camera-motif" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.8 6.2 5.2 7.2c-.4.1-.8.1-1.2.2A2.1 2.1 0 0 0 2.3 9.5V18c0 1.2 1 2.2 2.2 2.2h15c1.2 0 2.2-1 2.2-2.2V9.5c0-1-.7-1.9-1.8-2.1l-1.1-.2a2.3 2.3 0 0 1-1.6-1l-.9-1.4a2.2 2.2 0 0 0-1.7-1 48 48 0 0 0-5.2 0 2.2 2.2 0 0 0-1.8 1L6.8 6.2Z"></path>
                        <circle cx="12" cy="13" r="4.3"></circle>
                    </svg>
                </div>
            </section>

            <section class="action-panel" aria-label="Akses AbsenKu">
                <div class="capabilities">
                    <div class="capability">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="3.2"></circle>
                            <path stroke-linecap="round" d="M5.5 19c.8-3.2 3-5 6.5-5s5.7 1.8 6.5 5"></path>
                        </svg>
                        <span>Verifikasi swafoto</span>
                    </div>
                    <div class="capability">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 10c0 5-7 10-7 10S5 15 5 10a7 7 0 1 1 14 0Z"></path>
                            <circle cx="12" cy="10" r="2.2"></circle>
                        </svg>
                        <span>Verifikasi lokasi</span>
                    </div>
                </div>

                @auth
                    <p class="welcome-back">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong></p>
                    <a class="primary-action" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('attendance.dashboard') }}">Buka Dashboard</a>
                @else
                    <a class="primary-action" href="{{ route('login') }}">Masuk ke AbsenKu</a>
                @endauth
            </section>
        </main>

            <footer>&copy; {{ date('Y') }} YPDH Al Madani</footer>
        </div>
    </div>

    <script>
        (() => {
            const root = document.documentElement;
            const toggle = document.querySelector('[data-theme-toggle]');
            const themeColor = document.querySelector('meta[name="theme-color"]');

            const synchronizeTheme = () => {
                const isDark = root.dataset.theme === 'dark';
                const label = isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap';
                toggle.setAttribute('aria-label', label);
                toggle.setAttribute('title', label);
                themeColor.setAttribute('content', isDark ? '#0f1712' : '#f7fbf5');
            };

            toggle.addEventListener('click', () => {
                root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('welcome-theme', root.dataset.theme);
                synchronizeTheme();
            });

            synchronizeTheme();
        })();
    </script>
</body>
</html>
