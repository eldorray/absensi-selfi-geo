<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Rekap Absensi"
            :description="$activeYear ? 'Tahun Ajaran ' . $activeYear->name : null">
            @unless ($activeYear)
                <span class="admin-status-warning px-3 py-1.5 text-xs">Belum ada tahun ajaran aktif</span>
            @endunless
        </x-admin.page-header>

        <!-- Filters -->
        <div class="admin-glass-panel p-6">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="filter-start" class="admin-label">Tanggal Awal</label>
                    <input id="filter-start" type="date" name="start_date" value="{{ $startDate }}"
                        class="admin-field p-2.5">
                </div>
                <div>
                    <label for="filter-end" class="admin-label">Tanggal Akhir</label>
                    <input id="filter-end" type="date" name="end_date" value="{{ $endDate }}" class="admin-field p-2.5">
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
                    <a href="{{ route('admin.reports.monthly.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
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

        <!-- Table -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="admin-panel-header flex-wrap">
                <span class="admin-label">Rekap Kehadiran</span>
                <span class="flex flex-wrap items-center gap-2">
                    <span class="admin-chip admin-chip-time">
                        {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
                    </span>
                    <span class="admin-chip">{{ $workDays }} hari kerja</span>
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left">No.</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-center">Hari Kerja</th>
                            <th class="px-4 py-3 text-center">Total Hadir</th>
                            <th class="px-4 py-3 text-center">Tepat Waktu</th>
                            <th class="px-4 py-3 text-center">Terlambat</th>
                            <th class="px-4 py-3 text-center">Alpha</th>
                            <th class="px-4 py-3 text-center">Persentase</th>
                            <th class="px-4 py-3 text-right">Total Denda</th>
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
                                <td class="admin-muted px-4 py-3 text-center text-sm">
                                    {{ $data['work_days'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="admin-text-success font-semibold">{{ $data['total_present'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="admin-status-success px-2.5 py-1 text-xs">{{ $data['total_on_time'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="admin-status-warning px-2.5 py-1 text-xs">{{ $data['total_late'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="admin-status-danger px-2.5 py-1 text-xs">{{ max(0, $data['total_alpha']) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $rate = $data['attendance_rate'];
                                        $rateClass =
                                            $rate >= 90
                                                ? 'admin-text-success'
                                                : ($rate >= 75
                                                    ? 'admin-text-warning'
                                                    : 'admin-text-danger');
                                    @endphp
                                    <div class="inline-flex items-center gap-2">
                                        <span class="admin-meter w-14">
                                            <span class="admin-meter-seg {{ $rate >= 90 ? 'admin-meter-success' : 'admin-meter-warning' }}"
                                                style="width: {{ min($rate, 100) }}%"></span>
                                        </span>
                                        <span class="{{ $rateClass }} font-semibold" style="font-variant-numeric: tabular-nums">{{ $rate }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if ($data['total_fine'] > 0)
                                        <span class="admin-text-danger font-semibold">Rp
                                            {{ number_format($data['total_fine'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="admin-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <x-admin.empty-state icon="fas-chart-bar" title="Tidak ada data pegawai"
                                        hint="Pegawai aktif akan tampil di rekap kehadiran ini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($reportData->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="8" class="px-4 py-3 text-right text-sm font-semibold">Total Denda Keseluruhan</td>
                                <td class="admin-text-danger px-4 py-3 text-right text-sm font-bold">Rp
                                    {{ number_format($totalFine, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
