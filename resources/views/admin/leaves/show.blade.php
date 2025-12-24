<x-layouts.app>
    <!-- Breadcrumbs -->
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.leaves.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">Perizinan</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">Detail</span>
    </div>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Leave Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Detail Pengajuan</h2>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $leave->type_badge_class }}">
                            {{ $leave->type_label }}
                        </span>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $leave->status_badge_class }}">
                            {{ $leave->status_label }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Mulai</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $leave->start_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Selesai</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $leave->end_date->format('d M Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Durasi</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $leave->duration }} hari</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Diajukan</p>
                        <p class="font-medium text-gray-800 dark:text-gray-200">
                            {{ $leave->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Alasan</p>
                    <p class="text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700 p-4 rounded-xl">
                        {{ $leave->reason }}
                    </p>
                </div>
            </div>

            <!-- Attachment -->
            @if ($leave->attachment)
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Lampiran</h2>
                    <a href="{{ $leave->attachment_url }}" target="_blank">
                        <img src="{{ $leave->attachment_url }}" alt="Lampiran"
                            class="w-full max-h-96 object-contain rounded-xl">
                    </a>
                </div>
            @endif

            <!-- Approval Actions -->
            @if ($leave->isPending())
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Tindakan</h2>

                    <div class="flex gap-4">
                        <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit"
                                class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors"
                                onclick="return confirm('Setujui pengajuan ini?')">
                                ✓ Setujui
                            </button>
                        </form>
                    </div>

                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                        <form action="{{ route('admin.leaves.reject', $leave) }}" method="POST">
                            @csrf
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tolak dengan alasan:
                            </label>
                            <textarea name="rejection_reason" rows="2"
                                class="w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white mb-3"
                                placeholder="Masukkan alasan penolakan..."></textarea>
                            <button type="submit"
                                class="w-full py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-colors"
                                onclick="return confirm('Tolak pengajuan ini?')">
                                ✕ Tolak
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Approval Info -->
            @if (!$leave->isPending())
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">
                        {{ $leave->isApproved() ? 'Disetujui' : 'Ditolak' }}
                    </h2>
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center mr-4">
                            <span class="font-medium text-gray-600 dark:text-gray-300">
                                {{ $leave->approver ? substr($leave->approver->name, 0, 1) : '?' }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">
                                {{ $leave->approver?->name ?? '-' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $leave->approved_at?->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    @if ($leave->isRejected() && $leave->rejection_reason)
                        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl">
                            <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-1">Alasan Penolakan:</p>
                            <p class="text-sm text-red-700 dark:text-red-400">{{ $leave->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Sidebar - Employee Info -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Info Karyawan</h2>

                <div class="text-center mb-4">
                    <div
                        class="w-20 h-20 mx-auto rounded-full bg-sky-100 dark:bg-sky-900 flex items-center justify-center mb-3">
                        <span class="text-2xl font-bold text-sky-600 dark:text-sky-400">
                            {{ $leave->user->initials() }}
                        </span>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">{{ $leave->user->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $leave->user->email }}</p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Role</span>
                        <span
                            class="font-medium text-gray-800 dark:text-gray-200">{{ $leave->user->role?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Kantor</span>
                        <span
                            class="font-medium text-gray-800 dark:text-gray-200">{{ $leave->user->office?->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.leaves.index') }}"
                class="block w-full py-3 text-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-colors">
                ← Kembali ke Daftar
            </a>
        </div>
    </div>
</x-layouts.app>
