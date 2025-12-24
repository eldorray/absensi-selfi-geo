<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Persetujuan Perizinan - Absensi</title>
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
                    <h1 class="text-xl font-bold text-white">Persetujuan Perizinan</h1>
                </div>
                @if ($pendingCount > 0)
                    <span class="px-3 py-1 bg-amber-400 text-amber-900 rounded-full text-sm font-semibold">
                        {{ $pendingCount }} Menunggu
                    </span>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filter -->
            <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
                <a href="{{ route('approval.leaves.index') }}"
                    class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ !request('status') ? 'bg-sky-600 text-white' : 'bg-white text-gray-600' }}">
                    Semua
                </a>
                <a href="{{ route('approval.leaves.index', ['status' => 'pending']) }}"
                    class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ request('status') == 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-gray-600' }}">
                    Menunggu
                </a>
                <a href="{{ route('approval.leaves.index', ['status' => 'approved']) }}"
                    class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ request('status') == 'approved' ? 'bg-green-500 text-white' : 'bg-white text-gray-600' }}">
                    Disetujui
                </a>
                <a href="{{ route('approval.leaves.index', ['status' => 'rejected']) }}"
                    class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ request('status') == 'rejected' ? 'bg-red-500 text-white' : 'bg-white text-gray-600' }}">
                    Ditolak
                </a>
            </div>

            @forelse($leaves as $leave)
                <a href="{{ route('approval.leaves.show', $leave) }}"
                    class="block bg-white rounded-2xl shadow-lg p-4 mb-4 hover:shadow-xl transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-sky-100 flex items-center justify-center mr-3">
                                <span class="font-bold text-sky-600">{{ substr($leave->user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $leave->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $leave->user->role?->name ?? '-' }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $leave->status_badge_class }}">
                            {{ $leave->status_label }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $leave->type_badge_class }}">
                                {{ $leave->type_label }}
                            </span>
                            <span class="ml-2 text-sm text-gray-500">{{ $leave->duration }} hari</span>
                        </div>
                        <p class="text-sm text-gray-500">{{ $leave->start_date->format('d M') }}</p>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500">Tidak ada pengajuan</p>
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
