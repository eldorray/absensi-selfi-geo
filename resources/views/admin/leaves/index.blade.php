<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Pengajuan Perizinan"
            description="Kelola pengajuan izin dan cuti karyawan">
            @if ($pendingCount > 0)
                <span class="admin-status-warning px-3 py-1.5 text-xs">{{ $pendingCount }} Menunggu</span>
            @endif
        </x-admin.page-header>

        <!-- Filters -->
        <div class="admin-glass-panel p-6">
            <form action="{{ route('admin.leaves.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="filter-status" class="admin-label">Status</label>
                    <select id="filter-status" name="status" class="admin-field p-2.5">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div>
                    <label for="filter-type" class="admin-label">Jenis</label>
                    <select id="filter-type" name="type" class="admin-field p-2.5">
                        <option value="">Semua Jenis</option>
                        <option value="izin" {{ request('type') == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="cuti" {{ request('type') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                        <option value="sakit" {{ request('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    </select>
                </div>
                <button type="submit" class="admin-button-primary px-4 py-2 text-sm">
                    Filter
                </button>
                @if (request()->hasAny(['status', 'type']))
                    <a href="{{ route('admin.leaves.index') }}" class="admin-button-secondary px-4 py-2 text-sm">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="admin-alert-success rounded-2xl p-4 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="admin-alert-danger rounded-2xl p-4 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <!-- Table -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Karyawan</th>
                            <th class="px-6 py-4 text-left">Jenis</th>
                            <th class="px-6 py-4 text-left">Tanggal</th>
                            <th class="px-6 py-4 text-left">Alasan</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaves as $leave)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold">{{ $leave->user->name }}</div>
                                    <div class="admin-muted text-xs">{{ $leave->user->role?->name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="{{ $leave->type === 'sakit' ? 'admin-status-danger' : 'admin-status-info' }} px-2.5 py-1 text-xs">
                                        {{ $leave->type_label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $leave->start_date->format('d M') }}
                                    @if ($leave->start_date != $leave->end_date)
                                        - {{ $leave->end_date->format('d M') }}
                                    @endif
                                    <span class="admin-muted">({{ $leave->duration }}h)</span>
                                </td>
                                <td class="admin-muted max-w-xs truncate px-6 py-4 text-sm">
                                    {{ Str::limit($leave->reason, 50) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="{{ match ($leave->status) { 'approved' => 'admin-status-success', 'pending' => 'admin-status-warning', 'rejected' => 'admin-status-danger', default => 'admin-status-neutral' } }} px-2.5 py-1 text-xs">
                                        {{ $leave->status_label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a href="{{ route('admin.leaves.show', $leave) }}"
                                        class="admin-button-primary admin-icon-action px-3 text-xs">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-admin.empty-state icon="fas-file-alt" title="Belum ada pengajuan perizinan"
                                        hint="Pengajuan izin, cuti, dan sakit dari karyawan akan tampil di sini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leaves->hasPages())
                <div class="admin-panel-footer">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
