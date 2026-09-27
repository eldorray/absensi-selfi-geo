@php
    $isToday = $selectedDate->isToday();
    $absentLabel = $isToday ? 'Belum absen' : 'Alpha';
    $rupiah = fn (int $amount): string => 'Rp '.number_format($amount, 0, ',', '.');
    $dayLink = fn (int $offset): string => route('admin.reports.daily', array_filter([
        'date' => $selectedDate->copy()->addDays($offset)->format('Y-m-d'),
        'office_id' => $officeId,
    ]));
@endphp

<x-layouts.app title="Rekap Harian">
    <div class="space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Rekap Harian" :description="$activeYear ? 'Tahun ajaran '.$activeYear->name : null">
            @unless ($activeYear)
                <span class="admin-status-warning px-3 py-1.5 text-xs">Belum ada tahun ajaran aktif</span>
            @endunless
            <a href="{{ route('admin.reports.daily.export-pdf', ['date' => $selectedDate->format('Y-m-d'), 'office_id' => $officeId]) }}"
                class="admin-button-secondary px-4 text-sm">
                <x-admin.icon name="download" />
                Export PDF
            </a>
        </x-admin.page-header>

        <!-- Filters -->
        <form method="GET" class="admin-glass-panel flex flex-wrap items-end gap-4 px-6 py-5">
            <div>
                <label for="filter-date" class="admin-label">Tanggal</label>
                <div class="flex gap-1.5">
                    <a href="{{ $dayLink(-1) }}" class="admin-button-secondary size-11 p-0" aria-label="Hari sebelumnya">
                        <x-admin.icon name="chevron-left" size="16" />
                    </a>
                    <input id="filter-date" type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}"
                        class="admin-field w-48 px-3">
                    <a href="{{ $dayLink(1) }}" class="admin-button-secondary size-11 p-0" aria-label="Hari berikutnya">
                        <x-admin.icon name="chevron-right" size="16" />
                    </a>
                </div>
            </div>
            <div>
                <label for="filter-office" class="admin-label">Kantor</label>
                <select id="filter-office" name="office_id" class="admin-field w-56 px-3">
                    <option value="">Semua kantor</option>
                    @foreach ($offices as $office)
                        <option value="{{ $office->id }}" @selected($officeId == $office->id)>{{ $office->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="admin-button-primary px-5 text-sm">Tampilkan</button>
            <p class="admin-muted ml-auto self-center text-[13px]">{{ $selectedDate->locale('id')->translatedFormat('l, d F Y') }}</p>
        </form>

        <!-- Summary -->
        <section aria-label="Ringkasan" class="admin-glass-panel admin-stat-strip overflow-hidden">
            <div>
                <span class="admin-label" style="margin-bottom: 0">Pegawai</span>
                <span class="admin-display text-3xl">{{ $stats['total_employees'] }}</span>
            </div>
            <div>
                <span class="admin-label flex items-center gap-2" style="margin-bottom: 0"><span class="admin-meter-dot admin-meter-success"></span>Absen masuk</span>
                <span class="admin-display text-3xl">{{ $stats['checked_in'] }}</span>
            </div>
            <div>
                <span class="admin-label" style="margin-bottom: 0">Absen pulang</span>
                <span class="admin-display text-3xl">{{ $stats['checked_out'] }}</span>
            </div>
            <div>
                <span class="admin-label flex items-center gap-2" style="margin-bottom: 0"><span class="admin-meter-dot admin-meter-warning"></span>Terlambat</span>
                <span class="admin-display text-3xl">{{ $stats['late'] }}</span>
            </div>
            <div>
                <span class="admin-label flex items-center gap-2" style="margin-bottom: 0"><span class="admin-meter-dot admin-meter-idle"></span>{{ $absentLabel }}</span>
                <span class="admin-display text-3xl">{{ $stats['absent'] }}</span>
            </div>
            <div>
                <span class="admin-label" style="margin-bottom: 0">Total denda</span>
                <span @class(['admin-display whitespace-nowrap text-2xl leading-[2.25rem]', 'admin-text-danger' => $stats['total_fine'] > 0])>{{ $rupiah($stats['total_fine']) }}</span>
            </div>
        </section>

        <!-- Table -->
        <section aria-labelledby="judul-daftar" class="admin-glass-panel overflow-hidden">
            <div class="admin-panel-header">
                <h2 id="judul-daftar" class="admin-panel-title">Daftar hadir</h2>
                @if ($selectedOffice)
                    <span class="admin-chip">{{ $selectedOffice->name }}</span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col" class="w-14 py-2.5 pl-6 pr-2 text-left">No.</th>
                            <th scope="col" class="px-4 py-2.5 text-left">Nama</th>
                            <th scope="col" class="px-4 py-2.5 text-left">Jam kerja</th>
                            <th scope="col" class="px-4 py-2.5 text-left">Masuk</th>
                            <th scope="col" class="px-4 py-2.5 text-left">Pulang</th>
                            <th scope="col" class="px-4 py-2.5 text-left">Keterangan</th>
                            <th scope="col" class="px-4 py-2.5 text-right">Denda</th>
                            <th scope="col" class="py-2.5 pl-4 pr-6 text-right"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData as $index => $data)
                            @php($attendance = $data['attendance'])
                            <tr>
                                <td class="admin-muted py-2 pl-6 pr-2 text-sm tabular-nums">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-4 py-2">
                                    <div class="flex items-center gap-3">
                                        <span class="admin-avatar admin-avatar-sm" style="width: 2.125rem; height: 2.125rem">{{ $data['user']->initials() }}</span>
                                        <span class="flex flex-col leading-tight">
                                            <span class="font-bold">{{ $data['user']->name }}</span>
                                            <span class="admin-muted text-xs">{{ $data['user']->office?->name ?? ($data['user']->role?->name ?? '-') }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-4 py-2 text-sm tabular-nums">
                                    @if ($data['work_schedule'])
                                        {{ \Illuminate\Support\Str::substr($data['work_schedule']->check_in_time, 0, 5) }}–{{ \Illuminate\Support\Str::substr($data['work_schedule']->check_out_time, 0, 5) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-2">
                                    @if ($attendance)
                                        <div class="flex items-center gap-2.5">
                                            @if ($attendance->image_path)
                                                <button type="button" class="admin-photo-button"
                                                    aria-label="Lihat foto masuk {{ $data['user']->name }}"
                                                    @click="$dispatch('open-photo-modal', { url: @js($attendance->image_url), title: @js('Foto Masuk - '.$data['user']->name) })">
                                                    <x-admin.icon name="image" />
                                                </button>
                                            @endif
                                            <span class="flex flex-col leading-tight">
                                                <span @class(['admin-display text-lg', 'admin-text-warning' => $data['status'] === 'late'])>{{ $attendance->created_at->format('H:i') }}</span>
                                                @if ($attendance->liveness_verified === false)
                                                    <span class="admin-text-danger text-[11px] font-semibold" title="Kedip tidak terdeteksi, foto diambil manual. Periksa wajah di foto.">Foto manual</span>
                                                @endif
                                            </span>
                                        </div>
                                    @else
                                        <span class="admin-muted">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-2">
                                    @if ($attendance && $attendance->check_out_at)
                                        <div class="flex items-center gap-2.5">
                                            @if ($attendance->check_out_image_path)
                                                <button type="button" class="admin-photo-button"
                                                    aria-label="Lihat foto pulang {{ $data['user']->name }}"
                                                    @click="$dispatch('open-photo-modal', { url: @js($attendance->check_out_image_url), title: @js('Foto Pulang - '.$data['user']->name) })">
                                                    <x-admin.icon name="image" />
                                                </button>
                                            @endif
                                            <span class="flex flex-col leading-tight">
                                                <span class="admin-display text-lg">{{ $attendance->check_out_at->format('H:i') }}</span>
                                                @if ($attendance->check_out_liveness_verified === false)
                                                    <span class="admin-text-danger text-[11px] font-semibold" title="Kedip tidak terdeteksi, foto diambil manual. Periksa wajah di foto.">Foto manual</span>
                                                @endif
                                            </span>
                                        </div>
                                    @elseif ($attendance && ! $isToday)
                                        <span class="admin-text-danger text-[13px]">Tidak absen pulang</span>
                                    @else
                                        <span class="admin-muted">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-2">
                                    @switch($data['status'])
                                        @case('on_time')
                                        @case('present')
                                            <span class="admin-status-success px-2.5 py-1 text-xs">Tepat waktu</span>
                                        @break

                                        @case('late')
                                            <span class="admin-status-warning px-2.5 py-1 text-xs">Terlambat {{ $data['late_minutes'] }} mnt</span>
                                        @break

                                        @case('absent')
                                            <span @class(['px-2.5 py-1 text-xs', 'admin-status-neutral' => $isToday, 'admin-status-danger' => ! $isToday])>{{ $absentLabel }}</span>
                                        @break

                                        @case('no_schedule')
                                            <span class="admin-status-neutral px-2.5 py-1 text-xs">Tidak terjadwal</span>
                                        @break

                                        @default
                                            <span class="admin-status-neutral px-2.5 py-1 text-xs">{{ $data['status'] }}</span>
                                    @endswitch
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-right text-sm tabular-nums">
                                    @if ($data['fine'] > 0)
                                        <span class="admin-text-danger font-bold">{{ $rupiah($data['fine']) }}</span>
                                    @else
                                        <span class="admin-muted">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap py-2 pl-4 pr-6 text-right">
                                    @if ($attendance)
                                        <form action="{{ route('admin.reports.daily.reset', $attendance) }}"
                                            method="POST" class="inline" x-data="{}"
                                            @submit.prevent="$dispatch('admin-confirm', {
                                                title: 'Reset Absensi',
                                                message: 'Reset absensi ' + @js($data['user']->name) + ' hari ini? Data absen masuk & pulang akan dihapus.',
                                                confirmText: 'Reset',
                                                variant: 'danger',
                                                form: $el,
                                            })">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-button-danger admin-icon-action px-3 text-xs"
                                                aria-label="Reset absensi {{ $data['user']->name }}">Reset</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <x-admin.empty-state icon="users" title="Tidak ada data pegawai"
                                            hint="Pegawai aktif akan tampil di rekap harian ini." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($reportData->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="py-3 pl-6 pr-4 text-right text-sm font-semibold">Total denda</td>
                                    <td class="admin-text-danger px-4 py-3 text-right text-sm font-bold tabular-nums">{{ $rupiah($stats['total_fine']) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </section>
        </div>

        <!-- Photo Modal Overlay -->
        <div x-data="{
            open: false,
            imageUrl: '',
            title: '',
            returnFocus: null,
            close() {
                this.open = false;
                const el = this.returnFocus;
                this.returnFocus = null;
                if (el && typeof el.focus === 'function' && document.contains(el)) {
                    this.$nextTick(() => el.focus());
                }
            },
        }" x-id="['photo-modal-title']"
            @open-photo-modal.window="if (!open) returnFocus = document.activeElement; open = true; imageUrl = $event.detail.url; title = $event.detail.title; $nextTick(() => $refs.closeBtn.focus())"
            @keydown.escape.window="open && close()" @keydown.tab.window="if (open) { $event.preventDefault(); $refs.closeBtn.focus() }"
            x-show="open" x-cloak role="dialog" aria-modal="true" :aria-labelledby="$id('photo-modal-title')"
            class="admin-modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <!-- Modal Box -->
            <div class="admin-glass-modal relative w-full max-w-md overflow-hidden p-5 text-left"
                @click.away="open && close()" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95">

                <!-- Header -->
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 :id="$id('photo-modal-title')" class="admin-label" style="margin-bottom: 0" x-text="title">Foto Absensi</h2>
                    <button type="button" x-ref="closeBtn" @click="close()" class="admin-button-secondary admin-icon-action size-11 p-0"
                        aria-label="Tutup">
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
