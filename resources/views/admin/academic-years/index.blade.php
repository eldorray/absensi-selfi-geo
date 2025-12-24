<x-layouts.app>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tahun Ajaran</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kelola periode tahun ajaran untuk sistem absensi
                </p>
            </div>
            <a href="{{ route('admin.academic-years.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Tahun Ajaran
            </a>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Nama</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Tanggal Mulai</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Tanggal Selesai</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($academicYears as $year)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $year->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $year->start_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $year->end_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($year->is_active)
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        @if (!$year->is_active)
                                            <form action="{{ route('admin.academic-years.activate', $year) }}"
                                                method="POST"
                                                onsubmit="return confirm('Aktifkan tahun ajaran ini? Jadwal kerja akan di-reset.')">
                                                @csrf
                                                <button type="submit"
                                                    class="p-2 text-gray-600 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 transition-colors"
                                                    title="Aktifkan">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.academic-years.edit', $year) }}"
                                            class="p-2 text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        @if (!$year->is_active)
                                            <form action="{{ route('admin.academic-years.destroy', $year) }}"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada tahun ajaran. Silakan tambahkan tahun ajaran baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($academicYears->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $academicYears->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
