<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Absensi - AbsenKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f3f4f6;
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
    <div class="mobile-container safe-bottom">
        <div class="min-h-screen pb-20">
            <!-- Header -->
            <div class="px-5 pt-8 pb-6">
                <div class="flex items-center justify-between">
                    <a href="{{ route('attendance.dashboard') }}" class="p-2 -ml-2 text-white/70 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </a>
                    <h1 class="text-white font-semibold text-lg">Riwayat Absensi</h1>
                    <div class="w-10"></div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">

                @forelse($attendances as $attendance)
                    <div class="bg-white rounded-2xl shadow-md p-4 mb-3 flex items-center">
                        <!-- Selfie Thumbnail -->
                        <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0">
                            <img src="{{ $attendance->image_url }}" alt="Selfie" class="w-full h-full object-cover">
                        </div>

                        <!-- Info -->
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-800">
                                    {{ $attendance->created_at->locale('id')->isoFormat('dddd') }}</p>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $attendance->status->value === 'present' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $attendance->status->label() }}
                                </span>
                            </div>
                            <p class="text-gray-500 text-sm">{{ $attendance->created_at->format('d M Y') }}</p>
                            <div class="flex items-center mt-1 text-xs text-gray-400">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $attendance->created_at->format('H:i') }} WIB
                                <span class="mx-2">•</span>
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                </svg>
                                {{ number_format($attendance->distance_meters, 0) }}m
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-md p-8 text-center">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium">Belum Ada Riwayat</p>
                        <p class="text-gray-400 text-sm mt-1">Data absensi akan muncul di sini</p>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if ($attendances->hasPages())
                    <div class="mt-4 flex justify-center">
                        {{ $attendances->links('pagination::simple-tailwind') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
            <div class="max-w-[480px] mx-auto flex items-center justify-around h-20">
                <a href="{{ route('attendance.dashboard') }}" class="flex flex-col items-center text-gray-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    <span class="text-xs mt-1 font-medium">Beranda</span>
                </a>

                <!-- Masuk Button -->
                <a href="{{ route('attendance.selfie') }}" class="flex flex-col items-center -mt-4">
                    <div class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center bg-gray-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <span class="text-xs mt-1 font-medium text-gray-400">Masuk</span>
                </a>

                <!-- Pulang Button -->
                <a href="{{ route('attendance.checkout') }}" class="flex flex-col items-center -mt-4">
                    <div class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center bg-gray-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <span class="text-xs mt-1 font-medium text-gray-400">Pulang</span>
                </a>

                <a href="{{ route('attendance.index') }}" class="flex flex-col items-center text-sky-600">
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
