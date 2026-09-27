@php
    $tabs = [
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'all' => 'Semua',
    ];
    $query = fn (array $extra = []): array => array_filter(array_merge(['status' => $status, 'type' => $type], $extra), fn ($v) => $v !== null);
@endphp

<x-layouts.app title="Perizinan">
    <div class="space-y-5">
        <x-admin.page-header kicker="Kehadiran" title="Perizinan" description="Proses pengajuan izin, sakit, dan cuti pegawai" />

        <div class="flex flex-wrap items-end justify-between gap-4">
            <nav class="admin-segmented" aria-label="Status pengajuan">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.leaves.index', array_filter(['status' => $key, 'type' => $type])) }}"
                        @if ($status === $key) aria-current="page" @endif>
                        {{ $label }}
                        @if ($key === 'pending' && $pendingCount > 0)
                            <span class="admin-badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <form action="{{ route('admin.leaves.index') }}" method="GET" class="flex items-end gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <div>
                    <label for="filter-type" class="admin-label">Jenis</label>
                    <select id="filter-type" name="type" class="admin-field w-44 px-3" onchange="this.form.requestSubmit()">
                        <option value="">Semua jenis</option>
                        <option value="izin" @selected($type === 'izin')>Izin</option>
                        <option value="sakit" @selected($type === 'sakit')>Sakit</option>
                        <option value="cuti" @selected($type === 'cuti')>Cuti</option>
                    </select>
                </div>
                <noscript><button type="submit" class="admin-button-primary px-4 text-sm">Terapkan</button></noscript>
            </form>
        </div>

        <div class="admin-glass-panel grid grid-cols-1 overflow-hidden lg:grid-cols-[26rem_minmax(0,1fr)]">
            <div class="flex flex-col border-b lg:border-b-0 lg:border-r" style="border-color: var(--admin-border-soft)">
                @if ($leaves->isEmpty())
                    <x-admin.empty-state icon="doc" title="Tidak ada pengajuan"
                        hint="Pengajuan izin, cuti, dan sakit dari pegawai akan tampil di sini." />
                @else
                    <ul class="admin-pick-list flex-1" aria-label="Daftar pengajuan">
                        @foreach ($leaves as $leave)
                            @php($isSelected = $selected?->is($leave))
                            <li>
                                <a href="{{ route('admin.leaves.index', $query(['leave' => $leave->id, 'page' => $leaves->currentPage() > 1 ? $leaves->currentPage() : null])) }}#detail-pengajuan"
                                    class="admin-pick-item" @if ($isSelected) aria-current="true" @endif>
                                    <span class="admin-avatar">{{ $leave->user->initials() }}</span>
                                    <span class="flex min-w-0 flex-1 flex-col gap-1">
                                        <span class="flex items-center justify-between gap-2">
                                            <span class="truncate font-bold">{{ $leave->user->name }}</span>
                                            <span class="admin-muted shrink-0 text-xs">{{ $leave->created_at->isToday() ? $leave->created_at->format('H:i') : $leave->created_at->locale('id')->translatedFormat('d M') }}</span>
                                        </span>
                                        <span class="flex flex-wrap items-center gap-2 text-[13px]">
                                            <span class="{{ $leave->type === 'sakit' ? 'admin-status-danger' : 'admin-status-info' }} px-2 py-0.5 text-xs">{{ $leave->type_label }}</span>
                                            <span>
                                                {{ $leave->start_date->locale('id')->translatedFormat('d M') }}@if (! $leave->start_date->equalTo($leave->end_date))–{{ $leave->end_date->locale('id')->translatedFormat('d M') }}@endif
                                            </span>
                                            <span class="admin-muted">· {{ $leave->duration }} hari</span>
                                            @if ($status === 'all')
                                                <span class="{{ match ($leave->status) {'approved' => 'admin-status-success','pending' => 'admin-status-warning','rejected' => 'admin-status-danger',default => 'admin-status-neutral'} }} px-2 py-0.5 text-xs">{{ $leave->status_label }}</span>
                                            @endif
                                        </span>
                                        <span class="admin-muted truncate text-[13px]">{{ $leave->reason }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($leaves->hasPages())
                        <div class="admin-panel-footer">
                            {{ $leaves->links() }}
                        </div>
                    @endif
                @endif
            </div>

            <section id="detail-pengajuan" aria-labelledby="detail-pengajuan-judul" class="scroll-mt-24 p-6 md:p-8">
                @if ($selected)
                    @include('admin.leaves._detail', ['leave' => $selected])
                @else
                    <x-admin.empty-state icon="doc" title="Pilih pengajuan"
                        hint="Detail pengajuan yang dipilih akan tampil di sini." />
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
