<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <meta name="description" content="Aplikasi absensi digital dengan selfie, GPS, dan pengajuan izin online.">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-512x512.svg">
    <title>Absensi Selfie Geo</title>
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
                radial-gradient(circle at 85% 10%, rgba(14, 165, 233, 0.24), transparent 30rem),
                linear-gradient(160deg, #0f172a 0%, #164e63 46%, #f8fafc 46.15%, #f8fafc 100%);
        }

        .mobile-shell {
            max-width: 1120px;
            margin: 0 auto;
        }

        @media (max-width: 640px) and (max-height: 760px) {
            .attendance-preview {
                transform: scale(0.72);
                transform-origin: top center;
            }
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-900">
    <main class="mobile-shell h-[100svh] overflow-hidden px-5 py-5 sm:px-8 sm:py-8">
        <section class="flex h-full min-h-0 flex-col">
            <header class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Absensi Selfie Geo">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/12 ring-1 ring-white/20">
                        <img src="/images/icons/icon-512x512.svg" alt="" class="h-7 w-7">
                    </span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold text-white">Absensi</span>
                        <span class="block text-xs text-cyan-100">Selfie Geo</span>
                    </span>
                </a>

                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('attendance.dashboard') }}"
                        class="rounded-full bg-white/12 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/18">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-950 shadow-sm transition hover:bg-cyan-50">
                        Masuk
                    </a>
                @endauth
            </header>

            <div class="grid min-h-0 flex-1 items-center gap-4 py-4 lg:grid-cols-[0.9fr_1.1fr] lg:gap-12 lg:py-12">
                <div>
                    <p
                        class="inline-flex rounded-full bg-cyan-300/18 px-3 py-1 text-xs font-semibold text-cyan-50 ring-1 ring-cyan-100/20">
                        Presensi MI Daarul Hikmah
                    </p>

                    <div class="mt-4">
                        @auth
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('attendance.dashboard') }}"
                                class="flex w-full items-center justify-center rounded-2xl bg-white px-5 py-4 text-base font-semibold text-slate-950 shadow-lg shadow-slate-950/20 transition hover:bg-cyan-50 sm:w-fit sm:min-w-52">
                                Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="flex w-full items-center justify-center rounded-2xl bg-white px-5 py-4 text-base font-semibold text-slate-950 shadow-lg shadow-slate-950/20 transition hover:bg-cyan-50 sm:w-fit sm:min-w-52">
                                Masuk Sekarang
                            </a>
                            <p
                                class="mt-3 rounded-2xl bg-slate-950/30 px-4 py-3 text-center text-sm text-cyan-50 ring-1 ring-white/10 backdrop-blur sm:text-left">
                                Akun dibuat dan dikelola oleh admin instansi.
                            </p>
                        @endauth
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                            <p class="text-xl font-bold text-slate-950">3</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">Langkah absen</p>
                        </div>
                        <div class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                            <p class="text-xl font-bold text-slate-950">GPS</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">Lokasi akurat</p>
                        </div>
                        <div class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200">
                            <p class="text-xl font-bold text-slate-950">PDF</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">Laporan siap</p>
                        </div>
                    </div>
                </div>

                <div class="attendance-preview relative mx-auto w-full max-w-[480px] lg:max-w-[520px]">
                    <div class="rounded-[2rem] bg-white p-3 shadow-2xl shadow-slate-950/20 ring-1 ring-slate-200">
                        <div class="rounded-[1.4rem] bg-slate-950 p-4 text-white">
                            <div class="mb-5 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-400">Hari ini</p>
                                    <p class="text-lg font-semibold">{{ now()->locale('id')->isoFormat('dddd') }}</p>
                                </div>
                                <span
                                    class="rounded-full bg-emerald-400/16 px-3 py-1 text-xs font-semibold text-emerald-200">
                                    Siap absen
                                </span>
                            </div>

                            <div class="grid grid-cols-[1fr_auto] gap-4">
                                <div class="rounded-2xl bg-slate-900 p-4 ring-1 ring-white/10">
                                    <div class="mb-4 flex items-center gap-3">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/16">
                                            <svg class="h-6 w-6 text-cyan-200" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold">Selfie masuk</p>
                                            <p class="text-xs text-slate-400">Kamera aktif</p>
                                        </div>
                                    </div>
                                    <div
                                        class="h-28 rounded-2xl bg-gradient-to-br from-cyan-200 via-slate-200 to-amber-100 p-3">
                                        <div
                                            class="flex h-full items-end justify-center rounded-xl border border-white/60 bg-white/20">
                                            <div
                                                class="mb-3 h-14 w-14 rounded-full bg-slate-900/70 ring-4 ring-white/60">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3">
                                    <div class="h-16 w-16 rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                                        <svg class="h-full w-full text-emerald-200" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div class="h-16 w-16 rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                                        <svg class="h-full w-full text-amber-200" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                <div class="rounded-2xl bg-white/8 p-3">
                                    <p class="text-lg font-bold text-emerald-200">GPS</p>
                                    <p class="text-[11px] text-slate-400">Tervalidasi</p>
                                </div>
                                <div class="rounded-2xl bg-white/8 p-3">
                                    <p class="text-lg font-bold text-cyan-200">08:00</p>
                                    <p class="text-[11px] text-slate-400">Masuk</p>
                                </div>
                                <div class="rounded-2xl bg-white/8 p-3">
                                    <p class="text-lg font-bold text-amber-200">Izin</p>
                                    <p class="text-[11px] text-slate-400">Online</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>
</body>

</html>
