<x-layouts.app>
    <!-- Breadcrumbs -->
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">Perizinan</span>
    </div>

    <!-- Page Title -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Pengajuan Perizinan</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola pengajuan izin dan cuti karyawan</p>
        </div>
        @if ($pendingCount > 0)
            <span class="px-4 py-2 bg-amber-100 text-amber-800 rounded-full text-sm font-medium">
                {{ $pendingCount }} Menunggu
            </span>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form action="{{ route('admin.leaves.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div>
                <select name="status"
                    class="p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div>
                <select name="type"
                    class="p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Jenis</option>
                    <option value="izin" {{ request('type') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="cuti" {{ request('type') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                    <option value="sakit" {{ request('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl transition-colors">
                Filter
            </button>
            @if (request()->hasAny(['status', 'type']))
                <a href="{{ route('admin.leaves.index') }}"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl transition-colors">
                    Reset
                </a>
            @endif
        </form>
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

    <!-- Table -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Alasan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($leaves as $leave)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $leave->user->name }}
                                </div>
                                <div class="text-sm text-gray-500">{{ $leave->user->role?->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $leave->type_badge_class }}">
                                    {{ $leave->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $leave->start_date->format('d M') }}
                                @if ($leave->start_date != $leave->end_date)
                                    - {{ $leave->end_date->format('d M') }}
                                @endif
                                <span class="text-gray-400">({{ $leave->duration }}h)</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                {{ Str::limit($leave->reason, 50) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $leave->status_badge_class }}">
                                    {{ $leave->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.leaves.show', $leave) }}"
                                    class="text-sky-600 hover:text-sky-800 font-medium text-sm">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada pengajuan perizinan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leaves->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
