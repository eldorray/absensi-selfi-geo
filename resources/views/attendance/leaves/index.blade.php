<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perizinan - Absensi</title>
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
    </style>
</head>

<body>
    <div class="mobile-container min-h-screen pb-6">
        <!-- Header -->
        <div class="px-5 pt-8 pb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('attendance.dashboard') }}" class="text-white/80 hover:text-white mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold text-white">Perizinan Saya</h1>
                </div>
                <a href="{{ route('attendance.leaves.create') }}"
                    class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-white text-sm font-medium transition-colors">
                    + Ajukan
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($leaves as $leave)
                <a href="{{ route('attendance.leaves.show', $leave) }}"
                    class="block bg-white rounded-2xl shadow-lg p-4 mb-4 hover:shadow-xl transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $leave->type_badge_class }}">
                                {{ $leave->type_label }}
                            </span>
                            <span
                                class="ml-2 px-2 py-1 text-xs font-semibold rounded-full {{ $leave->status_badge_class }}">
                                {{ $leave->status_label }}
                            </span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </div>
                    <p class="text-gray-800 font-medium mb-1 line-clamp-1">{{ $leave->reason }}</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{ $leave->start_date->format('d M') }}
                        @if ($leave->start_date != $leave->end_date)
                            - {{ $leave->end_date->format('d M Y') }}
                        @else
                            {{ $leave->start_date->format('Y') }}
                        @endif
                        <span class="ml-2 text-gray-400">({{ $leave->duration }} hari)</span>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-gray-500 mb-4">Belum ada pengajuan perizinan</p>
                    <a href="{{ route('attendance.leaves.create') }}"
                        class="inline-block px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-xl transition-colors">
                        Ajukan Sekarang
                    </a>
                </div>
            @endforelse

            @if ($leaves->hasPages())
                <div class="mt-4">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
    </div>
</body>

</html>
