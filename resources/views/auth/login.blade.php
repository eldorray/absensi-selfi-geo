<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7fbf5">
    <meta name="description" content="Masuk ke aplikasi presensi MI Daarul Hikmah.">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AbsenKu">
    <link rel="manifest" href="/manifest.json?v=4">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Masuk | AbsenKu</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem('welcome-theme');
            document.documentElement.dataset.theme = savedTheme === 'dark' ? 'dark' : 'light';
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
            --md-sys-color-error: #ba1a1a;
            --md-sys-color-error-container: #ffdad6;
            --md-sys-color-on-error-container: #410002;
            --md-sys-color-focus: #004d2e;
            --md-sys-color-shadow: rgba(23, 107, 67, 0.18);
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
            --md-sys-color-error: #ffb4ab;
            --md-sys-color-error-container: #93000a;
            --md-sys-color-on-error-container: #ffdad6;
            --md-sys-color-focus: #b7f2cd;
            --md-sys-color-shadow: rgba(0, 0, 0, 0.38);
            --device-stage: #0b100d;
            --phone-shell: #080d0a;
            --phone-shell-border: rgba(229, 239, 231, 0.14);
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { min-height: 100%; background: var(--device-stage); }

        body {
            min-width: 320px;
            min-height: 100svh;
            margin: 0;
            display: grid;
            place-items: center;
            overflow-x: hidden;
            background: var(--device-stage);
            color: var(--md-sys-color-on-surface);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            transition: background-color 180ms ease, color 180ms ease;
        }

        button, a, input { font: inherit; }

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
            padding: max(16px, env(safe-area-inset-top)) 16px max(16px, env(safe-area-inset-bottom));
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .app-bar { display: grid; grid-template-columns: 48px 1fr 48px; align-items: center; gap: 10px; }
        .app-title { min-width: 0; text-align: center; }
        .app-title strong { display: block; font-size: 0.9375rem; font-weight: 800; }
        .app-title span { display: block; margin-top: 2px; overflow: hidden; color: var(--md-sys-color-on-surface-variant); font-size: 0.6875rem; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }

        .icon-button {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 50%;
            background: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-on-surface);
            cursor: pointer;
            text-decoration: none;
            transition: background-color 150ms ease, transform 150ms ease;
        }

        .icon-button:hover { background: var(--md-sys-color-secondary-container); }
        .icon-button:active { transform: scale(0.94); }
        .icon-button svg { width: 22px; height: 22px; }
        .moon-icon, :root[data-theme='dark'] .sun-icon { display: none; }
        :root[data-theme='dark'] .moon-icon { display: block; }

        .login-main { flex: 1; display: grid; align-content: center; gap: 14px; }

        .intro {
            padding: 22px;
            border-radius: 30px 30px 30px 10px;
            background: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
        }

        .intro-mark {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            margin-bottom: 18px;
            border-radius: 18px 18px 18px 7px;
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
        }

        .intro-mark svg { width: 27px; height: 27px; }
        .intro h1 { margin: 0; font-size: 1.75rem; line-height: 1.08; letter-spacing: -0.035em; }
        .intro p { margin: 10px 0 0; font-size: 0.875rem; line-height: 1.55; }

        .form-panel {
            padding: 18px;
            border-radius: 26px 26px 10px 26px;
            background: var(--md-sys-color-surface-container-low);
        }

        .status-message, .error-message {
            margin: 0 0 14px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 0.8125rem;
            line-height: 1.45;
        }

        .status-message { background: var(--md-sys-color-secondary-container); color: var(--md-sys-color-on-secondary-container); }
        .error-message { background: var(--md-sys-color-error-container); color: var(--md-sys-color-on-error-container); }
        .form { display: grid; gap: 16px; }
        .field { display: grid; gap: 7px; }
        .field label { font-size: 0.75rem; font-weight: 700; }
        .input-wrap { position: relative; }

        .text-input {
            width: 100%;
            min-height: 56px;
            padding: 16px;
            border: 1px solid transparent;
            border-bottom-color: var(--md-sys-color-outline);
            border-radius: 16px 16px 5px 5px;
            outline: 0;
            background: var(--md-sys-color-surface-container-high);
            color: var(--md-sys-color-on-surface);
            font-size: 0.9375rem;
        }

        .text-input.has-trailing-action { padding-right: 58px; }
        .text-input::placeholder { color: var(--md-sys-color-on-surface-variant); opacity: 0.8; }
        .text-input:focus { border: 2px solid var(--md-sys-color-primary); padding: 15px; }
        .text-input.has-trailing-action:focus { padding-right: 57px; }

        .password-toggle {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--md-sys-color-on-surface-variant);
            cursor: pointer;
        }

        .password-toggle svg { width: 21px; height: 21px; }
        .eye-closed { display: none; }
        .password-toggle[data-visible='true'] .eye-open { display: none; }
        .password-toggle[data-visible='true'] .eye-closed { display: block; }
        .field-error { margin: 0; color: var(--md-sys-color-error); font-size: 0.75rem; line-height: 1.4; }

        .form-options { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .remember { min-height: 44px; display: inline-flex; align-items: center; gap: 9px; color: var(--md-sys-color-on-surface-variant); font-size: 0.75rem; cursor: pointer; }
        .remember input { width: 20px; height: 20px; margin: 0; accent-color: var(--md-sys-color-primary); }
        .forgot-link { min-height: 44px; display: inline-flex; align-items: center; color: var(--md-sys-color-primary); font-size: 0.75rem; font-weight: 800; text-decoration: none; }

        .submit-button {
            min-height: 56px;
            border: 0;
            border-radius: 999px;
            background: var(--md-sys-color-primary);
            color: var(--md-sys-color-on-primary);
            box-shadow: 0 8px 22px var(--md-sys-color-shadow);
            font-weight: 800;
            cursor: pointer;
            transition: box-shadow 150ms ease, transform 150ms ease;
        }

        .submit-button:hover { box-shadow: 0 10px 26px var(--md-sys-color-shadow); transform: translateY(-1px); }
        .submit-button:active { transform: scale(0.98); }
        footer { color: var(--md-sys-color-on-surface-variant); font-size: 0.6875rem; text-align: center; }

        :where(a, button, input):focus-visible { outline: 3px solid var(--md-sys-color-focus); outline-offset: 3px; }

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
            .intro { padding: 18px; }
            .form-panel { padding: 14px; }
            .form-options { align-items: flex-start; flex-direction: column; gap: 2px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body>
    <div class="phone-shell">
        <span class="dynamic-island" aria-hidden="true"></span>
        <div class="page">
            <header class="app-bar">
                <a class="icon-button" href="{{ route('home') }}" aria-label="Kembali ke beranda">
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"></path>
                    </svg>
                </a>

                <div class="app-title">
                    <strong>AbsenKu</strong>
                    <span>MI Daarul Hikmah</span>
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

            <main class="login-main">
                <section class="intro" aria-labelledby="login-heading">
                    <div class="intro-mark" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="3.5"></circle>
                            <path stroke-linecap="round" d="M5.5 20c.8-3.8 3-5.8 6.5-5.8s5.7 2 6.5 5.8"></path>
                        </svg>
                    </div>
                    <h1 id="login-heading">Masuk ke AbsenKu</h1>
                    <p>Gunakan akun yang diberikan administrator untuk melanjutkan presensi.</p>
                </section>

                <section class="form-panel" aria-label="Form masuk">
                    @if (session('status'))
                        <p class="status-message" role="status">{{ session('status') }}</p>
                    @endif

                    @if ($errors->any())
                        <p class="error-message" role="alert">Periksa kembali email dan password Anda.</p>
                    @endif

                    <form action="{{ route('login') }}" method="POST" class="form">
                        @csrf

                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" class="text-input" type="email" name="email" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id" autocomplete="email" autofocus required>
                            @error('email')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <div class="input-wrap">
                                <input id="password" class="text-input has-trailing-action" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                                <button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan password" aria-pressed="false">
                                    <svg class="eye-open" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                                        <circle cx="12" cy="12" r="2.8"></circle>
                                    </svg>
                                    <svg class="eye-closed" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="m3 3 18 18M10.6 6.1c.5-.1.9-.1 1.4-.1 6 0 9.5 6 9.5 6a15 15 0 0 1-2.2 2.8M6.2 6.2A15.2 15.2 0 0 0 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.4 4-1"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-options">
                            <label class="remember">
                                <input type="checkbox" name="remember" checked>
                                <span>Ingat saya (30 hari)</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="forgot-link" href="{{ route('password.request') }}">Lupa password?</a>
                            @endif
                        </div>

                        <button type="submit" class="submit-button">Masuk</button>
                    </form>
                </section>
            </main>

            <footer>&copy; {{ date('Y') }} YPDH Al Madani</footer>
        </div>
    </div>

    <script>
        (() => {
            const root = document.documentElement;
            const themeToggle = document.querySelector('[data-theme-toggle]');
            const themeColor = document.querySelector('meta[name="theme-color"]');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.querySelector('[data-password-toggle]');

            const synchronizeTheme = () => {
                const isDark = root.dataset.theme === 'dark';
                const label = isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap';
                themeToggle.setAttribute('aria-label', label);
                themeToggle.setAttribute('title', label);
                themeColor.setAttribute('content', isDark ? '#0f1712' : '#f7fbf5');
            };

            themeToggle.addEventListener('click', () => {
                root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('welcome-theme', root.dataset.theme);
                synchronizeTheme();
            });

            passwordToggle.addEventListener('click', () => {
                const isVisible = passwordInput.type === 'text';
                passwordInput.type = isVisible ? 'password' : 'text';
                passwordToggle.dataset.visible = isVisible ? 'false' : 'true';
                passwordToggle.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
                passwordToggle.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
            });

            synchronizeTheme();
        })();
    </script>
</body>
</html>
