<x-layouts.app>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Rekap Absensi Harian</h1>
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal</label>
                    <input type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}"
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
                    <a href="{{ route('admin.reports.daily.export-pdf', ['date' => $selectedDate->format('Y-m-d')]) }}"
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Pegawai</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_employees'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sudah Absen Masuk</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['checked_in'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sudah Absen Pulang</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['checked_out'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center py-4">
            <h2 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Daftar Hadir</h2>
            <p class="text-gray-500 dark:text-gray-400">{{ $selectedDate->translatedFormat('l, d F Y') }}</p>
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
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Jam Kerja</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Jam Masuk</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Foto Masuk</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Jam Pulang</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Foto Pulang</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Keterangan</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Denda</th>
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
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($data['work_schedule'])
                                        {{ $data['work_schedule']->start_time }} s/d
                                        {{ $data['work_schedule']->end_time }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($data['attendance'])
                                        <span class="text-green-600 dark:text-green-400 font-medium">
                                            {{ $data['attendance']->created_at->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($data['attendance'] && $data['attendance']->image_path)
                                        <button type="button" @click="$dispatch('open-photo-modal', { url: '{{ $data['attendance']->image_url }}', title: 'Foto Masuk - {{ $data['user']->name }}' })"
                                            class="inline-flex items-center px-2 py-1 bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300 text-xs rounded-lg hover:bg-pink-200 cursor-pointer border border-pink-200/50 dark:border-pink-800/40">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            Lihat
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($data['attendance'] && $data['attendance']->check_out_at)
                                        <span class="text-purple-600 dark:text-purple-400 font-medium">
                                            {{ $data['attendance']->check_out_at->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($data['attendance'] && $data['attendance']->check_out_image_path)
                                        <button type="button" @click="$dispatch('open-photo-modal', { url: '{{ $data['attendance']->check_out_image_url }}', title: 'Foto Pulang - {{ $data['user']->name }}' })"
                                            class="inline-flex items-center px-2 py-1 bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300 text-xs rounded-lg hover:bg-pink-200 cursor-pointer border border-pink-200/50 dark:border-pink-800/40">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            Lihat
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @switch($data['status'])
                                        @case('on_time')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Hadir
                                            </span>
                                        @break

                                        @case('late')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                                Hadir Terlambat
                                            </span>
                                        @break

                                        @case('absent')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Alpha
                                            </span>
                                        @break

                                        @case('no_schedule')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                Libur / Tidak Ada Jadwal
                                            </span>
                                        @break

                                        @default
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $data['status'] }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if ($data['fine'] > 0)
                                        <span class="font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($data['fine'], 0, ',', '.') }}</span>
                                        <div class="text-[10px] text-gray-400">telat {{ $data['late_minutes'] }} mnt</div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada data pegawai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($reportData->isNotEmpty())
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td colspan="8" class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Total Denda</td>
                                    <td class="px-4 py-3 text-right text-sm font-bold text-red-600 dark:text-red-400">Rp {{ number_format($stats['total_fine'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Photo Modal Overlay -->
        <div x-data="{ open: false, imageUrl: '', title: '' }"
             @open-photo-modal.window="open = true; imageUrl = $event.detail.url; title = $event.detail.title"
             x-show="open"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
             
             <!-- Modal Box -->
             <div class="relative bg-white dark:bg-gray-800 rounded-[28px] max-w-md w-full overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-800/80 p-5 text-left"
                  @click.away="open = false"
                  x-transition:enter="transition ease-out duration-300 transform"
                  x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                  x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                  x-transition:leave="transition ease-in duration-200 transform"
                  x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                  x-transition:leave-end="opacity-0 translate-y-4 scale-95">
                  
                  <!-- Header -->
                  <div class="flex items-center justify-between mb-4">
                      <h3 class="text-xs font-bold text-slate-850 dark:text-white font-display uppercase tracking-wider" x-text="title">Foto Absensi</h3>
                      <button @click="open = false" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-gray-700 text-slate-400 hover:text-slate-600 transition-colors">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                          </svg>
                      </button>
                  </div>
                  
                  <!-- Image Container -->
                  <div class="rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800/80 bg-slate-900 flex items-center justify-center aspect-square shadow-inner">
                      <img :src="imageUrl" alt="Foto Absensi" class="w-full h-full object-contain">
                  </div>
             </div>
        </div>
    </x-layouts.app>
