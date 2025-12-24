<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Absensi - Sistem Kehadiran Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%);
            min-height: 100vh;
        }

        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }
    </style>
</head>

<body>
    <div class="mobile-container min-h-screen flex flex-col">
        <!-- Main Content -->
        <div class="flex-1 flex flex-col items-center justify-center px-8">
            <!-- Logo/Icon -->
            <div class="mb-8">
                <div class="w-24 h-24 bg-white/20 rounded-3xl flex items-center justify-center pulse-animation">
                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-white text-center mb-3">Absensi Digital</h1>
            <p class="text-white/80 text-center mb-10">Sistem Kehadiran Modern dengan<br>Selfie & Geolokasi</p>

            <!-- Features -->
            <div class="w-full space-y-4 mb-10">
                <div class="flex items-center bg-white/10 rounded-2xl p-4">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Absensi Selfie</p>
                        <p class="text-white/60 text-sm">Verifikasi wajah otomatis</p>
                    </div>
                </div>
                <div class="flex items-center bg-white/10 rounded-2xl p-4">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Geolokasi</p>
                        <p class="text-white/60 text-sm">Validasi lokasi kehadiran</p>
                    </div>
                </div>
                <div class="flex items-center bg-white/10 rounded-2xl p-4">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Perizinan Online</p>
                        <p class="text-white/60 text-sm">Ajukan izin & cuti digital</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Actions -->
        <div class="px-8 pb-10">
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('attendance.dashboard') }}"
                    class="block w-full py-4 bg-white text-sky-600 font-semibold text-center rounded-2xl shadow-lg hover:bg-gray-50 transition-colors mb-3">
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="block w-full py-4 bg-white text-sky-600 font-semibold text-center rounded-2xl shadow-lg hover:bg-gray-50 transition-colors mb-3">
                    Masuk
                </a>
                <div class="text-center text-white/80 text-sm">
                    Belum punya akun? Silakan hubungi Admin
                </div>
            @endauth
        </div>
    </div>
</body>

</html>
