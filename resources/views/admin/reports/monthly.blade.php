@php
    $rupiah = fn (int $amount): string => 'Rp '.number_format($amount, 0, ',', '.');
    $start = \Carbon\Carbon::parse($startDate)->locale('id');
    $end = \Carbon\Carbon::parse($endDate)->locale('id');
    $monthLink = fn (int $offset): string => route('admin.reports.monthly', array_filter([
        'start_date' => $start->copy()->startOfMonth()->addMonthsNoOverflow($offset)->format('Y-m-d'),
        'end_date' => $start->copy()->startOfMonth()->addMonthsNoOverflow($offset)->endOfMonth()->format('Y-m-d'),
        'office_id' => $officeId,
    ]));
    $employees = $reportData->count();
    $averageRate = $employees > 0 ? round($reportData->avg('attendance_rate'), 1) : 0;
@endphp

<x-layouts.app title="Rekap Bulanan">
    <div class="space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Rekap Bulanan" :description="$activeYear ? 'Tahun ajaran '.$activeYear->name : null">
            @unless ($activeYear)
                <span class="admin-status-warning px-3 py-1.5 text-xs">Belum ada tahun ajaran aktif</span>
            @endunless
            <a href="{{ route('admin.reports.monthly.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'office_id' => $officeId]) }}"
                class="admin-button-secondary px-4 text-sm">
                <x-admin.icon name="download" />
                Export PDF
            </a>
        </x-admin.page-header>

        <!-- Filters -->
        <form method="GET" class="admin-glass-panel flex flex-wrap items-end gap-4 px-6 py-5">
            <div class="flex items-end gap-1.5">
                <a href="{{ $monthLink(-1) }}" class="admin-button-secondary size-11 p-0" aria-label="Bulan sebelumnya">
                    <x-admin.icon name="chevron-left" size="16" />
                </a>
                <div>
                    <label for="filter-start" class="admin-label">Tanggal awal</label>
                    <input id="filter-start" type="date" name="start_date" value="{{ $startDate }}" class="admin-field w-44 px-3">
                </div>
                <div>
                    <label for="filter-end" class="admin-label">Tanggal akhir</label>
                    <input id="filter-end" type="date" name="end_date" value="{{ $endDate }}" class="admin-field w-44 px-3">
                </div>
                <a href="{{ $monthLink(1) }}" class="admin-button-secondary size-11 p-0" aria-label="Bulan berikutnya">
                    <x-admin.icon name="chevron-right" size="16" />
                </a>
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
        </form>

        <!-- Summary -->
        <section aria-label="Ringkasan" class="admin-glass-panel admin-stat-strip overflow-hidden">
            <div>
                <span class="admin-label" style="margin-bottom: 0">Periode</span>
                <span class="admin-display whitespace-nowrap text-xl leading-9">{{ $start->translatedFormat('d M') }} – {{ $end->translatedFormat('d M Y') }}</span>
            </div>
            <div>
                <span class="admin-label" style="margin-bottom: 0">Hari kerja</span>
                <span class="admin-display text-3xl">{{ $workDays }}</span>
            </div>
            <div>
                <span class="admin-label" style="margin-bottom: 0">Pegawai</span>
                <span class="admin-display text-3xl">{{ $employees }}</span>
            </div>
            <div>
                <span class="admin-label flex items-center gap-2" style="margin-bottom: 0"><span class="admin-meter-dot admin-meter-success"></span>Rata-rata hadir</span>
                <span class="admin-display text-3xl">{{ $averageRate }}%</span>
            </div>
            <div>
                <span class="admin-label" style="margin-bottom: 0">Total denda</span>
                <span @class(['admin-display whitespace-nowrap text-2xl leading-9', 'admin-text-danger' => $totalFine > 0])>{{ $rupiah($totalFine) }}</span>
            </div>
        </section>

        <!-- Table -->
        <section aria-labelledby="judul-rekap" class="admin-glass-panel overflow-hidden">
            <div class="admin-panel-header">
                <h2 id="judul-rekap" class="admin-panel-title">Rekap kehadiran</h2>
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
                            <th scope="col" class="px-4 py-2.5 text-right">Hadir</th>
                            <th scope="col" class="px-4 py-2.5 text-right">Tepat waktu</th>
                            <th scope="col" class="px-4 py-2.5 text-right">Terlambat</th>
                            <th scope="col" class="px-4 py-2.5 text-right">Alpha</th>
                            <th scope="col" class="px-4 py-2.5 text-left">Kehadiran</th>
                            <th scope="col" class="py-2.5 pl-4 pr-6 text-right">Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData as $index => $data)
                            @php
                                $rate = $data['attendance_rate'];
                                $alpha = max(0, $data['total_alpha']);
                            @endphp
                            <tr>
                                <td class="admin-muted py-2.5 pl-6 pr-2 text-sm tabular-nums">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-4 py-2.5">
                                    <div class="flex items-center gap-3">
                                        <span class="admin-avatar admin-avatar-sm" style="width: 2.125rem; height: 2.125rem">{{ $data['user']->initials() }}</span>
                                        <span class="flex flex-col leading-tight">
                                            <span class="font-bold">{{ $data['user']->name }}</span>
                                            <span class="admin-muted text-xs">{{ $data['user']->office?->name ?? ($data['user']->role?->name ?? '-') }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-right"><span class="admin-display text-lg">{{ $data['total_present'] }}</span><span class="admin-muted text-xs"> / {{ $data['work_days'] }}</span></td>
                                <td class="px-4 py-2.5 text-right text-sm tabular-nums">{{ $data['total_on_time'] }}</td>
                                <td @class(['px-4 py-2.5 text-right text-sm tabular-nums', 'admin-text-warning font-bold' => $data['total_late'] > 0])>{{ $data['total_late'] }}</td>
                                <td @class(['px-4 py-2.5 text-right text-sm tabular-nums', 'admin-text-danger font-bold' => $alpha > 0])>{{ $alpha }}</td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="admin-meter admin-meter-thin w-20" role="img" aria-label="Kehadiran {{ $rate }} persen">
                                            @if ($rate > 0)
                                                <div class="admin-meter-seg {{ $rate >= 90 ? 'admin-meter-success' : 'admin-meter-warning' }}" style="flex: {{ min($rate, 100) }} 1 0px"></div>
                                            @endif
                                            @if ($rate < 100)
                                                <div class="admin-meter-seg admin-meter-idle" style="flex: {{ 100 - min($rate, 100) }} 1 0px"></div>
                                            @endif
                                        </div>
                                        <span @class([
                                            'text-sm font-bold tabular-nums',
                                            'admin-text-success' => $rate >= 90,
                                            'admin-text-warning' => $rate >= 75 && $rate < 90,
                                            'admin-text-danger' => $rate < 75,
                                        ])>{{ $rate }}%</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap py-2.5 pl-4 pr-6 text-right text-sm tabular-nums">
                                    @if ($data['total_fine'] > 0)
                                        <span class="admin-text-danger font-bold">{{ $rupiah($data['total_fine']) }}</span>
                                    @else
                                        <span class="admin-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-admin.empty-state icon="clipboard" title="Tidak ada data pegawai"
                                        hint="Pegawai aktif akan tampil di rekap kehadiran ini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($reportData->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="7" class="py-3 pl-6 pr-4 text-right text-sm font-semibold">Total denda keseluruhan</td>
                                <td class="admin-text-danger whitespace-nowrap py-3 pl-4 pr-6 text-right text-sm font-bold tabular-nums">{{ $rupiah($totalFine) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
