<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Pengajuan - Absensi</title>
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
            <div class="flex items-center">
                <a href="{{ route('approval.leaves.index') }}" class="text-white/80 hover:text-white mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-white">Detail Pengajuan</h1>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Employee Info -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                <div class="flex items-center">
                    <div class="w-14 h-14 rounded-full bg-sky-100 flex items-center justify-center mr-4">
                        <span class="text-xl font-bold text-sky-600">{{ $leave->user->initials() }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $leave->user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $leave->user->role?->name ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $leave->user->office?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Leave Detail -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $leave->type_badge_class }}">
                        {{ $leave->type_label }}
                    </span>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $leave->status_badge_class }}">
                        {{ $leave->status_label }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500">Tanggal Mulai</p>
                        <p class="font-medium text-gray-800">{{ $leave->start_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tanggal Selesai</p>
                        <p class="font-medium text-gray-800">{{ $leave->end_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Durasi</p>
                        <p class="font-medium text-gray-800">{{ $leave->duration }} hari</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Diajukan</p>
                        <p class="font-medium text-gray-800">{{ $leave->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-1">Alasan</p>
                    <p class="text-gray-700 bg-gray-50 p-3 rounded-xl text-sm">{{ $leave->reason }}</p>
                </div>
            </div>

            <!-- Attachment -->
            @if ($leave->attachment)
                <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                    <p class="text-xs text-gray-500 mb-2">Lampiran</p>
                    <a href="{{ $leave->attachment_url }}" target="_blank">
                        <img src="{{ $leave->attachment_url }}" alt="Lampiran" class="w-full rounded-xl">
                    </a>
                </div>
            @endif

            <!-- Approval Actions -->
            @if ($leave->isPending())
                <div class="space-y-3">
                    <form action="{{ route('approval.leaves.approve', $leave) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-2xl transition-colors flex items-center justify-center"
                            onclick="return confirm('Setujui pengajuan ini?')">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Setujui Pengajuan
                        </button>
                    </form>

                    <div class="bg-white rounded-2xl shadow-lg p-5">
                        <form action="{{ route('approval.leaves.reject', $leave) }}" method="POST">
                            @csrf
                            <p class="text-sm font-medium text-gray-700 mb-2">Tolak dengan alasan:</p>
                            <textarea name="rejection_reason" rows="2" class="w-full p-3 rounded-xl border-gray-300 shadow-sm text-sm mb-3"
                                placeholder="Masukkan alasan penolakan..."></textarea>
                            @error('rejection_reason')
                                <p class="text-red-600 text-xs mb-2">{{ $message }}</p>
                            @enderror
                            <button type="submit"
                                class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-colors"
                                onclick="return confirm('Tolak pengajuan ini?')">
                                Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- Approval Info -->
                <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                    <p class="text-xs text-gray-500 mb-2">
                        {{ $leave->isApproved() ? 'Disetujui Oleh' : 'Ditolak Oleh' }}</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                            <span class="font-medium text-gray-600">
                                {{ $leave->approver ? substr($leave->approver->name, 0, 1) : '?' }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $leave->approver?->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $leave->approved_at?->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    @if ($leave->isRejected() && $leave->rejection_reason)
                        <div class="mt-4 p-3 bg-red-50 rounded-xl">
                            <p class="text-xs font-medium text-red-800 mb-1">Alasan Penolakan:</p>
                            <p class="text-sm text-red-700">{{ $leave->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>

</html>
