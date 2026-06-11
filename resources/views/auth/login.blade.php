<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <title>Masuk - Absensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
            overscroll-behavior: none;
            width: 100%;
        }

        body {
            background:
                radial-gradient(circle at 85% 10%, rgba(14, 165, 233, 0.24), transparent 28rem),
                linear-gradient(160deg, #0f172a 0%, #164e63 48%, #f8fafc 48.15%, #f8fafc 100%);
        }

        .mobile-shell {
            max-width: 560px;
            margin: 0 auto;
        }

        @media (max-width: 640px) and (max-height: 760px) {
            .login-panel {
                transform: scale(0.92);
                transform-origin: top center;
            }
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-900">
    <main class="mobile-shell h-[100svh] overflow-hidden px-5 py-5 sm:px-8 sm:py-8">
        <section class="flex h-full min-h-0 flex-col">
            <header class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Kembali ke beranda">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/12 ring-1 ring-white/20">
                        <img src="/images/icons/icon-512x512.svg" alt="" class="h-7 w-7">
                    </span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold text-white">Absensi</span>
                        <span class="block text-xs text-cyan-100">Selfie Geo</span>
                    </span>
                </a>

                <h1 class="text-sm font-semibold text-white/90">Masuk</h1>
            </header>

            <div class="login-panel flex min-h-0 flex-1 items-center justify-center py-4">
                <div class="w-full">
                    <div class="mb-4 flex justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white ring-4 ring-white/15 shadow-lg">
                            <svg class="h-8 w-8 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] bg-white p-4 shadow-2xl shadow-slate-950/15 ring-1 ring-slate-200 sm:p-5">
                        @if (session('status'))
                            <div class="mb-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST" class="space-y-3">
                            @csrf

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" autofocus
                                    class="w-full rounded-2xl border-slate-300 bg-slate-50 px-4 py-3 text-[15px] shadow-sm focus:border-sky-500 focus:bg-white focus:ring-sky-500"
                                    placeholder="Masukkan email Anda">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                                <input type="password" name="password"
                                    class="w-full rounded-2xl border-slate-300 bg-slate-50 px-4 py-3 text-[15px] shadow-sm focus:border-sky-500 focus:bg-white focus:ring-sky-500"
                                    placeholder="Masukkan password">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <input type="checkbox" name="remember"
                                        class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                    <span>Ingat saya</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-sky-700">
                                        Lupa password?
                                    </a>
                                @endif
                            </div>

                            <button type="submit"
                                class="flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3.5 text-base font-semibold text-white shadow-lg shadow-slate-950/20 transition hover:bg-slate-800">
                                Masuk
                            </button>
                        </form>
                    </div>

                    <p class="mt-3 rounded-2xl bg-slate-950/30 px-4 py-3 text-center text-sm text-cyan-50 ring-1 ring-white/10 backdrop-blur">
                        Akun dibuat dan dikelola oleh admin instansi.
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
