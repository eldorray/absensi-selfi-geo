<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Laporan Absensi" description="Lihat semua data absensi karyawan"
            :count="$attendances->total() . ' catatan'" />

        <!-- Filters -->
        <div class="admin-glass-panel p-6">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="filter-date" class="admin-label">Tanggal</label>
                    <input id="filter-date" type="date" name="date" value="{{ request('date') }}"
                        class="admin-field p-2.5">
                </div>
                <div>
                    <label for="filter-office" class="admin-label">Kantor</label>
                    <select id="filter-office" name="office_id" class="admin-field p-2.5">
                        <option value="">Semua Kantor</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}"
                                {{ request('office_id') == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-status" class="admin-label">Status</label>
                    <select id="filter-status" name="status" class="admin-field p-2.5">
                        <option value="">Semua Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="admin-button-primary w-full px-4 py-2.5 text-sm">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Karyawan</th>
                            <th class="px-6 py-4 text-left">Kantor</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Jarak</th>
                            <th class="px-6 py-4 text-left">Waktu</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="admin-avatar">
                                            <img src="{{ $attendance->image_url }}"
                                                alt="Selfie {{ $attendance->user->name }}">
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-bold">{{ $attendance->user->name }}</span>
                                            <span
                                                class="admin-muted block text-xs">{{ $attendance->user->email }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $attendance->user->office?->name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="{{ $attendance->status->value === 'present' ? 'admin-status-success' : 'admin-status-warning' }} px-2.5 py-1 text-xs">
                                        {{ $attendance->status->label() }}
                                    </span>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ number_format($attendance->distance_meters, 0) }} m
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="admin-chip admin-chip-time">{{ $attendance->created_at->format('H:i') }}</span>
                                    <span
                                        class="admin-muted ml-2 text-xs">{{ $attendance->created_at->format('d M Y') }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a href="{{ route('admin.attendances.show', $attendance) }}"
                                        class="admin-button-primary admin-icon-action px-3 text-xs">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-admin.empty-state icon="fas-clipboard-list"
                                        title="Tidak ada data absensi yang ditemukan"
                                        hint="Ubah tanggal, kantor, atau status pada filter di atas." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($attendances->hasPages())
                <div class="admin-panel-footer">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
