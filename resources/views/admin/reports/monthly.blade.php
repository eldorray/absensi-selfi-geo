<x-layouts.app>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Rekap Absensi</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    @if ($activeYear)
                        Tahun Ajaran {{ $activeYear->name }}
                    @else
                        <span class="text-amber-600">Belum ada tahun ajaran aktif</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        Refresh
                    </button>
                    <a href="{{ route('admin.reports.monthly.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Export PDF
                    </a>
                </div>
            </form>
        </div>

        <!-- Title -->
        <div class="text-center py-4">
            <h2 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rekap Kehadiran</h2>
            <p class="text-gray-500 dark:text-gray-400">
                {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} -
                {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
            </p>
            <p class="text-sm text-gray-400 dark:text-gray-500">Total {{ $workDays }} Hari Kerja</p>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                No.</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Nama</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Hari Kerja</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Total Hadir</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Tepat Waktu</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Terlambat</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Alpha</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($reportData as $index => $data)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $data['user']->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $data['user']->role?->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ $data['work_days'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="font-semibold text-green-600 dark:text-green-400">{{ $data['total_present'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $data['total_on_time'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                        {{ $data['total_late'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        {{ max(0, $data['total_alpha']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $rate = $data['attendance_rate'];
                                        $colorClass =
                                            $rate >= 90
                                                ? 'text-green-600 dark:text-green-400'
                                                : ($rate >= 75
                                                    ? 'text-amber-600 dark:text-amber-400'
                                                    : 'text-red-600 dark:text-red-400');
                                    @endphp
                                    <span class="font-semibold {{ $colorClass }}">{{ $rate }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data pegawai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
