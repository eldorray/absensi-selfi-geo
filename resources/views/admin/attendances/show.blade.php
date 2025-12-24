<x-layouts.app>
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.attendances.index') }}" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detail Absensi</h1>
        </div>

        <!-- Selfie Image -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden mb-6">
            <img src="{{ $attendance->image_url }}" alt="Selfie Absensi" class="w-full aspect-video object-cover">
        </div>

        <!-- Details -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <dl class="space-y-4">
                <div class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Karyawan</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $attendance->user->name }}</dd>
                </div>
                <div class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $attendance->user->email }}</dd>
                </div>
                <div class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kantor</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $attendance->user->office?->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $attendance->status->badgeClass() }}">
                            {{ $attendance->status->label() }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Koordinat</dt>
                    <dd class="text-sm text-gray-900 dark:text-white font-mono">
                        {{ number_format($attendance->check_in_lat, 8) }}, {{ number_format($attendance->check_in_long, 8) }}
                    </dd>
                </div>
                <div class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jarak dari Kantor</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ number_format($attendance->distance_meters, 0) }} meter</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Waktu Check-in</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $attendance->created_at->format('d M Y H:i:s') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-layouts.app>
