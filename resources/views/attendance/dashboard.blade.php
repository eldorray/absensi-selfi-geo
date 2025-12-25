<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="description" content="Aplikasi Absensi Selfie dengan Verifikasi GPS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AbsenKu">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-512x512.svg">
    <title>AbsenKu - {{ auth()->user()->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%);
            min-height: 100vh;
        }

        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%);
            min-height: 100vh;
        }

        .safe-bottom {
            padding-bottom: calc(80px + env(safe-area-inset-bottom));
        }
    </style>
</head>

<body>
    <!-- PWA Install Banner -->
    <div id="pwa-install-banner"
        class="hidden fixed bottom-24 left-4 right-4 z-50 p-4 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-white">
                    <p class="font-semibold text-sm">Install AbsenKu</p>
                    <p class="text-xs text-white/80">Akses lebih cepat & offline</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="installPWA()"
                    class="px-3 py-1.5 bg-white text-indigo-600 font-semibold text-sm rounded-lg hover:bg-gray-100">
                    Install
                </button>
                <button onclick="dismissInstallBanner()" class="p-1.5 text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="mobile-container safe-bottom">
        <div class="min-h-screen pb-20">
            <!-- Header with User Profile -->
            <div class="px-5 pt-8 pb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-full bg-white/20 border-2 border-white/50 overflow-hidden flex items-center justify-center">
                            @if ($todayAttendance)
                                <img src="{{ $todayAttendance->image_url }}" alt="Profile"
                                    class="w-full h-full object-cover">
                            @else
                                <span class="text-white text-lg font-bold">{{ auth()->user()->initials() }}</span>
                            @endif
                        </div>
                        <div class="ml-3">
                            <h2 class="text-white font-semibold text-lg">{{ auth()->user()->name }}</h2>
                            <p class="text-white/70 text-sm">{{ auth()->user()->office?->name ?? 'Karyawan' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-white/70 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- App Logo -->
                <div class="text-center mt-6">
                    <div class="flex items-center justify-center">
                        <svg class="w-10 h-10 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                        </svg>
                        <span class="text-3xl font-bold text-white ml-2">absen<span
                                class="text-amber-400">KU</span></span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">

                <!-- Schedule Card -->
                <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-sky-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                @if ($todaySchedule)
                                    <p class="text-gray-500 text-sm">Jadwal Hari Ini</p>
                                    <p class="font-semibold text-gray-800">
                                        {{ \Carbon\Carbon::parse($todaySchedule->check_in_time)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($todaySchedule->check_out_time)->format('H:i') }}
                                    </p>
                                @else
                                    <p class="text-gray-500 text-sm">Tidak Ada Jadwal</p>
                                    <p class="font-semibold text-amber-600">Hari Libur</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">{{ now()->locale('id')->isoFormat('dddd') }}</p>
                            <p class="text-gray-500 text-sm">{{ now()->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex justify-between mt-4 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-gray-500 text-sm">Masuk :</p>
                            @if ($todayAttendance)
                                <p
                                    class="font-semibold {{ $todayAttendance->status->value === 'late' ? 'text-amber-500' : 'text-green-600' }}">
                                    {{ $todayAttendance->created_at->format('H:i') }}
                                    @if ($todayAttendance->status->value === 'late')
                                        <span class="text-xs">(Terlambat)</span>
                                    @endif
                                </p>
                            @else
                                <p class="font-semibold text-gray-400">-</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-gray-500 text-sm">Pulang :</p>
                            @if ($todayAttendance && $todayAttendance->check_out_at)
                                <p class="font-semibold text-green-600">
                                    {{ $todayAttendance->check_out_at->format('H:i') }}</p>
                            @else
                                <p class="font-semibold text-gray-400">-</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Monthly Summary -->
                <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Rekap Absensi Bulan ini</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-gray-500 text-sm mb-1">HADIR</p>
                            <p class="text-2xl font-bold text-green-600">{{ $monthlyPresent }}</p>
                            <p class="text-xs text-gray-400">Hari</p>
                        </div>
                        <div class="text-center border-x border-gray-100">
                            <p class="text-gray-500 text-sm mb-1">TERLAMBAT</p>
                            <p class="text-2xl font-bold text-amber-500">{{ $monthlyLate }}</p>
                            <p class="text-xs text-gray-400">Hari</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-500 text-sm mb-1">TOTAL</p>
                            <p class="text-2xl font-bold text-sky-600">{{ $totalAttendance }}</p>
                            <p class="text-xs text-gray-400">Hari</p>
                        </div>
                    </div>
                </div>

                <!-- Menu Utama -->
                <div class="mb-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Menu Utama</h3>
                    <div class="grid grid-cols-4 gap-4">
                        <a href="{{ route('attendance.index') }}"
                            class="bg-white rounded-2xl shadow-md p-4 text-center hover:shadow-lg transition-shadow">
                            <div class="w-12 h-12 mx-auto mb-2 rounded-xl bg-sky-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-gray-700">Riwayat</p>
                        </a>
                        <a href="{{ route('attendance.profile') }}"
                            class="bg-white rounded-2xl shadow-md p-4 text-center hover:shadow-lg transition-shadow">
                            <div
                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-purple-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-gray-700">Profil</p>
                        </a>
                        <a href="{{ route('attendance.password') }}"
                            class="bg-white rounded-2xl shadow-md p-4 text-center hover:shadow-lg transition-shadow">
                            <div
                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-amber-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-gray-700">Password</p>
                        </a>
                        <a href="{{ route('attendance.leaves.index') }}"
                            class="bg-white rounded-2xl shadow-md p-4 text-center hover:shadow-lg transition-shadow">
                            <div
                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-green-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-gray-700">Perizinan</p>
                        </a>
                    </div>
                </div>

                @if (auth()->user()->role?->slug === 'kepala-sekolah')
                    <!-- Menu Persetujuan (Kepala Sekolah only) -->
                    <div class="mb-5">
                        <a href="{{ route('approval.leaves.index') }}"
                            class="flex items-center justify-between bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-lg p-5 text-white hover:shadow-xl transition-shadow">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Persetujuan Perizinan</p>
                                    <p class="text-white/70 text-sm">Kelola pengajuan izin & cuti</p>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-white/70" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                @endif

                <!-- Today Status -->
                @if ($todayAttendance)
                    <div
                        class="bg-gradient-to-r {{ $todayAttendance->status->value === 'late' ? 'from-amber-500 to-orange-500' : 'from-green-500 to-emerald-600' }} rounded-2xl shadow-lg p-5 text-white mb-5">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-full border-2 border-white/50 overflow-hidden">
                                <img src="{{ $todayAttendance->image_url }}" alt="Selfie"
                                    class="w-full h-full object-cover">
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-white/80 text-sm">Status Hari Ini</p>
                                <p class="text-xl font-bold">{{ $todayAttendance->status->label() }}</p>
                                <p class="text-white/70 text-sm">Check-in
                                    {{ $todayAttendance->created_at->format('H:i') }} WIB</p>
                            </div>
                            <svg class="w-12 h-12 text-white/30" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
            <div class="max-w-[480px] mx-auto flex items-center justify-around h-20">
                <a href="{{ route('attendance.dashboard') }}" class="flex flex-col items-center text-sky-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">Beranda</span>
                </a>

                <!-- Masuk Button -->
                <a href="{{ route('attendance.selfie') }}" class="flex flex-col items-center -mt-4">
                    <div
                        class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center {{ $todayAttendance ? 'bg-gray-300' : 'bg-gradient-to-r from-green-500 to-emerald-500' }}">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <span
                        class="text-xs mt-1 font-medium {{ $todayAttendance ? 'text-gray-400' : 'text-green-600' }}">Masuk</span>
                </a>

                <!-- Pulang Button -->
                <a href="{{ route('attendance.checkout') }}" class="flex flex-col items-center -mt-4">
                    @php
                        $canCheckout = $todayAttendance && !$todayAttendance->hasCheckedOut();
                    @endphp
                    <div
                        class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center {{ $canCheckout ? 'bg-gradient-to-r from-amber-500 to-orange-500' : 'bg-gray-300' }}">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <span
                        class="text-xs mt-1 font-medium {{ $canCheckout ? 'text-amber-600' : 'text-gray-400' }}">Pulang</span>
                </a>

                <a href="{{ route('attendance.index') }}" class="flex flex-col items-center text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                    <span class="text-xs mt-1 font-medium">Riwayat</span>
                </a>
            </div>
        </nav>
    </div>
</body>

</html>
