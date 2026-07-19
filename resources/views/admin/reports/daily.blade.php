<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Rekap Absensi Harian" :description="$activeYear ? 'Tahun Ajaran ' . $activeYear->name : null">
            @unless ($activeYear)
                <span class="admin-status-warning px-3 py-1.5 text-xs">Belum ada tahun ajaran aktif</span>
            @endunless
        </x-admin.page-header>

        <!-- Filters -->
        <div class="admin-glass-panel p-6">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="filter-date" class="admin-label">Tanggal</label>
                    <input id="filter-date" type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}"
                        class="admin-field p-2.5">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="admin-button-primary flex flex-1 items-center justify-center gap-2 px-4 py-2.5 text-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        Refresh
                    </button>
                    <a href="{{ route('admin.reports.daily.export-pdf', ['date' => $selectedDate->format('Y-m-d')]) }}"
                        class="admin-button-danger flex items-center gap-2 px-4 py-2.5 text-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <x-admin.stat-card tone="indigo" label="Jumlah Pegawai" :value="$stats['total_employees']">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-admin.stat-card>

            <x-admin.stat-card tone="emerald" label="Sudah Absen Masuk" :value="$stats['checked_in']">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-admin.stat-card>

            <x-admin.stat-card tone="violet" label="Sudah Absen Pulang" :value="$stats['checked_out']">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </x-admin.stat-card>
        </div>

        <!-- Table -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="admin-panel-header">
                <span class="admin-label">Daftar Hadir</span>
                <span class="admin-chip admin-chip-time">{{ $selectedDate->translatedFormat('l, d F Y') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left">No.</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Jam Kerja</th>
                            <th class="px-4 py-3 text-left">Jam Masuk</th>
                            <th class="px-4 py-3 text-center">Foto Masuk</th>
                            <th class="px-4 py-3 text-left">Jam Pulang</th>
                            <th class="px-4 py-3 text-center">Foto Pulang</th>
                            <th class="px-4 py-3 text-center">Keterangan</th>
                            <th class="px-4 py-3 text-right">Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData as $index => $data)
                            <tr>
                                <td class="admin-muted px-4 py-3 text-sm">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="text-sm font-bold">{{ $data['user']->name }}</div>
                                    <div class="admin-muted text-xs">{{ $data['user']->role?->name ?? '-' }}</div>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-4 py-3 text-sm"
                                    style="font-variant-numeric: tabular-nums">
                                    @if ($data['work_schedule'])
                                        {{ $data['work_schedule']->start_time }} s/d
                                        {{ $data['work_schedule']->end_time }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($data['attendance'])
                                        <span class="admin-chip admin-chip-time">
                                            {{ $data['attendance']->created_at->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="admin-muted">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($data['attendance'] && $data['attendance']->image_path)
                                        <button type="button"
                                            @click="$dispatch('open-photo-modal', { url: '{{ $data['attendance']->image_url }}', title: 'Foto Masuk - {{ $data['user']->name }}' })"
                                            class="admin-chip inline-flex items-center gap-1 text-xs">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            Lihat
                                        </button>
                                    @else
                                        <span class="admin-muted">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($data['attendance'] && $data['attendance']->check_out_at)
                                        <span class="admin-chip admin-chip-time">
                                            {{ $data['attendance']->check_out_at->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="admin-muted">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($data['attendance'] && $data['attendance']->check_out_image_path)
                                        <button type="button"
                                            @click="$dispatch('open-photo-modal', { url: '{{ $data['attendance']->check_out_image_url }}', title: 'Foto Pulang - {{ $data['user']->name }}' })"
                                            class="admin-chip inline-flex items-center gap-1 text-xs">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            Lihat
                                        </button>
                                    @else
                                        <span class="admin-muted">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @switch($data['status'])
                                        @case('on_time')
                                            <span class="admin-status-success px-2.5 py-1 text-xs">Hadir</span>
                                        @break

                                        @case('late')
                                            <span class="admin-status-warning px-2.5 py-1 text-xs">Hadir Terlambat</span>
                                        @break

                                        @case('absent')
                                            <span class="admin-status-danger px-2.5 py-1 text-xs">Alpha</span>
                                        @break

                                        @case('no_schedule')
                                            <span class="admin-status-neutral px-2.5 py-1 text-xs">Libur / Tidak Ada
                                                Jadwal</span>
                                        @break

                                        @default
                                            <span
                                                class="admin-status-neutral px-2.5 py-1 text-xs">{{ $data['status'] }}</span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if ($data['fine'] > 0)
                                        <span
                                            class="admin-text-danger font-semibold">{{ 'Rp ' . number_format($data['fine'], 0, ',', '.') }}</span>
                                        <div class="admin-muted text-[10px]">telat {{ $data['late_minutes'] }} mnt
                                        </div>
                                    @else
                                        <span class="admin-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <x-admin.empty-state icon="fas-users" title="Tidak ada data pegawai"
                                            hint="Pegawai aktif akan tampil di rekap harian ini." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($reportData->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="8" class="px-4 py-3 text-right text-sm font-semibold">Total Denda</td>
                                    <td class="admin-text-danger px-4 py-3 text-right text-sm font-bold">
                                        {{ 'Rp ' . number_format($stats['total_fine'], 0, ',', '.') }}</td>
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
            x-show="open" x-cloak class="admin-modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <!-- Modal Box -->
            <div class="admin-glass-modal relative w-full max-w-md overflow-hidden p-5 text-left"
                @click.away="open = false" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95">

                <!-- Header -->
                <div class="mb-4 flex items-center justify-between gap-3">
                    <span class="admin-label" style="margin-bottom: 0" x-text="title">Foto Absensi</span>
                    <button @click="open = false" class="admin-button-secondary admin-icon-action size-11 p-0"
                        aria-label="Tutup pratinjau foto">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Image Container -->
                <div class="flex aspect-square items-center justify-center overflow-hidden rounded-2xl bg-slate-900">
                    <img :src="imageUrl" alt="Foto Absensi" class="h-full w-full object-contain">
                </div>
            </div>
        </div>
    </x-layouts.app>
