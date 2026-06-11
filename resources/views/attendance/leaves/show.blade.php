<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Perizinan - Absensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html,
        body {
            background:
                radial-gradient(circle at 85% 10%, rgba(14, 165, 233, 0.24), transparent 28rem),
                linear-gradient(160deg, #0f172a 0%, #164e63 48%, #f8fafc 48.15%, #f8fafc 100%);
            height: 100%;
            width: 100%;
            overflow: hidden;
            overscroll-behavior: none;
        }

        .mobile-container {
            max-width: 560px;
            margin: 0 auto;
            height: 100svh;
            overflow: hidden;
            background: transparent;
        }

        .mobile-container > .min-h-screen {
            height: 100%;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }

        .mobile-container > .min-h-screen > .bg-gray-100 {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>

<body>
    <div class="mobile-container min-h-screen pb-6">
        <!-- Header -->
        <div class="px-5 pt-8 pb-6">
            <div class="flex items-center">
                <a href="{{ route('attendance.leaves.index') }}" class="text-white/80 hover:text-white mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-white">Detail Perizinan</h1>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">
            <!-- Status Card -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $leave->type_badge_class }}">
                            {{ $leave->type_label }}
                        </span>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $leave->status_badge_class }}">
                        {{ $leave->status_label }}
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span>
                            {{ $leave->start_date->format('d M Y') }}
                            @if ($leave->start_date != $leave->end_date)
                                - {{ $leave->end_date->format('d M Y') }}
                            @endif
                            <span class="text-gray-400">({{ $leave->duration }} hari)</span>
                        </span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Diajukan {{ $leave->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Reason -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                <h3 class="font-semibold text-gray-800 mb-3">Alasan</h3>
                <p class="text-gray-600">{{ $leave->reason }}</p>
            </div>

            <!-- Attachment -->
            @if ($leave->attachment)
                <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                    <h3 class="font-semibold text-gray-800 mb-3">Lampiran</h3>
                    <img src="{{ $leave->attachment_url }}" alt="Lampiran" class="w-full rounded-xl">
                </div>
            @endif

            <!-- Approval Info -->
            @if (!$leave->isPending())
                <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                    <h3 class="font-semibold text-gray-800 mb-3">
                        {{ $leave->isApproved() ? 'Disetujui Oleh' : 'Ditolak Oleh' }}
                    </h3>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                            <span class="font-medium text-gray-600">
                                {{ $leave->approver ? substr($leave->approver->name, 0, 1) : '?' }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $leave->approver?->name ?? '-' }}</p>
                            <p class="text-sm text-gray-500">{{ $leave->approved_at?->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    @if ($leave->isRejected() && $leave->rejection_reason)
                        <div class="mt-4 p-3 bg-red-50 rounded-xl">
                            <p class="text-sm font-medium text-red-800 mb-1">Alasan Penolakan:</p>
                            <p class="text-sm text-red-700">{{ $leave->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>

</html>
