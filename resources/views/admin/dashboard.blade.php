@php
    use App\Services\AdminDashboardService as D;

    $hour = (int) now()->format('H');
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 19 => 'Selamat sore',
        default => 'Selamat malam',
    };
    $firstName = \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->first();
    $legend = [
        ['key' => D::ON_TIME, 'label' => 'Tepat waktu', 'tone' => 'success'],
        ['key' => D::LATE, 'label' => 'Terlambat', 'tone' => 'warning'],
        ['key' => D::LEAVE, 'label' => 'Izin / cuti', 'tone' => 'info'],
        ['key' => D::NOT_YET, 'label' => 'Belum absen', 'tone' => 'idle'],
    ];
    $meterLabel = fn (array $counts): string => collect($legend)
        ->map(fn (array $item): string => $counts[$item['key']].' '.strtolower($item['label']))
        ->implode(', ');
    $shownNotYet = $notYet->take(5);
@endphp

<x-layouts.app title="Dashboard">
    <div class="space-y-6">
        <div class="admin-page-header flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
                <span class="admin-kicker">{{ now()->locale('id')->translatedFormat('l, d F Y') }} · Diperbarui
                    {{ now()->format('H:i') }} WIB</span>
                <h1 class="mt-1.5">{{ $greeting }}, {{ $firstName }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.reports.daily.export-pdf', ['date' => today()->format('Y-m-d')]) }}"
                    class="admin-button-secondary px-4 text-sm">
                    <x-admin.icon name="download" />
                    Export PDF
                </a>
                <a href="{{ route('admin.reports.daily') }}" class="admin-button-primary px-4 text-sm">
                    Buka rekap harian
                    <x-admin.icon name="arrow-right" />
                </a>
            </div>
        </div>

        {{-- Kehadiran hari ini + per kantor --}}
        <section aria-labelledby="judul-hadir" class="admin-glass-panel grid grid-cols-1 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
            <div class="flex flex-col gap-5 p-6 md:p-8">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="judul-hadir" class="admin-label" style="margin-bottom: 0; font-size: .875rem">Kehadiran hari ini</h2>
                    @if ($expected > 0)
                        <span class="admin-status-success px-3 py-1 text-xs">{{ $presentPct }}% hadir</span>
                    @endif
                </div>

                @if ($expected > 0)
                    <div class="flex flex-wrap items-baseline gap-3">
                        <span class="admin-display text-7xl leading-[0.9] md:text-[5.5rem]">{{ $present }}</span>
                        <span class="admin-display admin-muted text-2xl md:text-3xl">/ {{ $expected }} pegawai</span>
                    </div>
                    <div class="admin-meter" role="img" aria-label="{{ $meterLabel($totals) }}">
                        @foreach ($legend as $item)
                            @if ($totals[$item['key']] > 0)
                                <div class="admin-meter-seg admin-meter-{{ $item['tone'] }}" style="flex: {{ $totals[$item['key']] }} 1 0px"></div>
                            @endif
                        @endforeach
                    </div>
                    <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach ($legend as $item)
                            <div class="flex flex-col gap-0.5">
                                <dt class="admin-muted flex items-center gap-2 text-[13px]">
                                    <span class="admin-meter-dot admin-meter-{{ $item['tone'] }}"></span>{{ $item['label'] }}
                                </dt>
                                <dd class="admin-display text-[1.625rem]">{{ $totals[$item['key']] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @if ($totals[D::OFF] > 0)
                        <p class="admin-muted text-xs">{{ $totals[D::OFF] }} pegawai tidak terjadwal hari ini, tidak dihitung.</p>
                    @endif
                @else
                    <x-admin.empty-state icon="calendar" title="Tidak ada jadwal kerja hari ini"
                        hint="Pegawai yang terjadwal atau sudah absen akan dihitung di sini." />
                @endif
            </div>

            <div class="flex flex-col gap-5 border-t p-6 md:p-8 lg:border-l lg:border-t-0" style="border-color: var(--admin-border-soft)">
                <h2 class="admin-label" style="margin-bottom: 0; font-size: .875rem">Per kantor</h2>
                @forelse ($offices as $office)
                    <div class="flex flex-col gap-2">
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="font-bold">{{ $office['name'] }}</span>
                            <span class="admin-display text-[1.375rem]">{{ $office['present'] }}<span class="admin-muted text-base"> / {{ $office['expected'] }}</span></span>
                        </div>
                        <div class="admin-meter admin-meter-thin" role="img" aria-label="{{ $office['name'] }}: {{ $meterLabel($office['counts']) }}">
                            @foreach ($legend as $item)
                                @if ($office['counts'][$item['key']] > 0)
                                    <div class="admin-meter-seg admin-meter-{{ $item['tone'] }}" style="flex: {{ $office['counts'][$item['key']] }} 1 0px"></div>
                                @endif
                            @endforeach
                        </div>
                        <p class="admin-muted text-xs">
                            {{ $office['counts'][D::ON_TIME] }} tepat waktu · {{ $office['counts'][D::LATE] }} terlambat ·
                            {{ $office['counts'][D::LEAVE] }} izin · {{ $office['counts'][D::NOT_YET] }} belum absen
                        </p>
                    </div>
                @empty
                    <p class="admin-muted text-sm">Belum ada pegawai terjadwal hari ini.</p>
                @endforelse
            </div>
        </section>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
            {{-- Perlu tindakan --}}
            <section aria-labelledby="judul-tindakan" class="admin-glass-panel overflow-hidden">
                <div class="px-6 pb-3 pt-5">
                    <h2 id="judul-tindakan" class="admin-panel-title">Perlu tindakan</h2>
                </div>
                @php
                    $actions = [
                        ['title' => 'Perizinan menunggu', 'sub' => $pendingLeaveSummary ?: 'Tidak ada pengajuan baru', 'count' => $pendingLeaves, 'icon' => 'doc', 'tone' => 'warning', 'href' => route('admin.leaves.index', ['status' => 'pending'])],
                        ['title' => 'Foto manual perlu dicek', 'sub' => 'Kedip tidak terdeteksi saat absen', 'count' => $manualPhotos, 'icon' => 'shield-alert', 'tone' => 'danger', 'href' => route('admin.reports.daily')],
                        ['title' => 'Belum absen', 'sub' => 'Terjadwal hari ini', 'count' => $totals[D::NOT_YET], 'icon' => 'user-x', 'tone' => 'primary', 'href' => route('admin.reports.daily')],
                    ];
                @endphp
                <ul>
                    @foreach ($actions as $action)
                        <li class="border-t" style="border-color: var(--admin-border-soft)">
                            <a href="{{ $action['href'] }}" class="admin-row-link flex items-center gap-3.5 px-6 py-3.5">
                                <span class="admin-tile admin-tone-{{ $action['tone'] }}"><x-admin.icon :name="$action['icon']" size="20" /></span>
                                <span class="flex min-w-0 flex-1 flex-col">
                                    <span class="font-bold">{{ $action['title'] }}</span>
                                    <span class="admin-muted truncate text-[13px]">{{ $action['sub'] }}</span>
                                </span>
                                <span class="admin-display text-[1.625rem]">{{ $action['count'] }}</span>
                                <x-admin.icon name="chevron-right" size="16" class="admin-muted shrink-0" />
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($shownNotYet->isNotEmpty())
                    <div class="border-t px-6 pb-2 pt-4" style="border-color: var(--admin-border-soft)">
                        <h3 class="admin-eyebrow">Belum absen</h3>
                    </div>
                    <ul class="pb-2">
                        @foreach ($shownNotYet as $employee)
                            <li class="flex items-center gap-3 px-6 py-1.5">
                                <span class="admin-avatar admin-avatar-sm">{{ $employee->initials() }}</span>
                                <span class="min-w-0 flex-1 truncate text-sm">{{ $employee->name }}</span>
                                <span class="admin-muted text-xs">{{ $employee->office?->name ?? '-' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    @if ($notYet->count() > $shownNotYet->count())
                        <a href="{{ route('admin.reports.daily') }}" class="flex min-h-11 items-center px-6 pb-2 text-[13px] font-bold"
                            style="color: var(--admin-primary)">+{{ $notYet->count() - $shownNotYet->count() }} lainnya di rekap harian</a>
                    @endif
                @endif
            </section>

            {{-- Absensi terbaru --}}
            <section aria-labelledby="judul-terbaru" class="admin-glass-panel overflow-hidden xl:col-span-2">
                <div class="flex items-center justify-between gap-3 px-6 pb-3 pt-5">
                    <h2 id="judul-terbaru" class="admin-panel-title">Absensi terbaru</h2>
                    <a href="{{ route('admin.attendances.index') }}" class="flex min-h-11 items-center text-[13px] font-bold"
                        style="color: var(--admin-primary)">Lihat semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-2.5 text-left">Pegawai</th>
                                <th scope="col" class="px-4 py-2.5 text-left">Kantor</th>
                                <th scope="col" class="px-4 py-2.5 text-left">Masuk</th>
                                <th scope="col" class="px-4 py-2.5 text-left">Status</th>
                                <th scope="col" class="px-6 py-2.5 text-left">Wajah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAttendances as $attendance)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-2.5">
                                        <div class="flex items-center gap-3">
                                            <span class="admin-avatar admin-avatar-square">
                                                <img src="{{ $attendance->image_url }}" alt="Selfie {{ $attendance->user->name }}" loading="lazy">
                                            </span>
                                            <span class="font-bold">{{ $attendance->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="admin-muted whitespace-nowrap px-4 py-2.5 text-sm">{{ $attendance->user->office?->name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-2.5">
                                        <span class="admin-display text-lg">{{ $attendance->created_at->format('H:i') }}</span>
                                        @unless ($attendance->created_at->isToday())
                                            <span class="admin-muted ml-1 text-xs">{{ $attendance->created_at->locale('id')->translatedFormat('d M') }}</span>
                                        @endunless
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2.5">
                                        <span @class([
                                            'px-2.5 py-1 text-xs',
                                            'admin-status-warning' => $attendance->status->value === 'late',
                                            'admin-status-success' => $attendance->status->value !== 'late',
                                        ])>{{ $attendance->status->label() }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-2.5">
                                        @if ($attendance->liveness_verified === false)
                                            <span class="admin-text-danger inline-flex items-center gap-1.5 text-[13px] font-semibold">
                                                <x-admin.icon name="shield-alert" size="16" />Perlu dicek
                                            </span>
                                        @elseif ($attendance->liveness_verified === true)
                                            <span class="admin-text-success inline-flex items-center gap-1.5 text-[13px] font-semibold">
                                                <x-admin.icon name="shield-check" size="16" />Terverifikasi
                                            </span>
                                        @else
                                            <span class="admin-muted text-sm" title="Absen sebelum cek kedip diaktifkan">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-admin.empty-state icon="clipboard" title="Belum ada absensi"
                                            hint="Absensi pegawai yang masuk akan tampil di sini." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
