@php
    $typeLabels = ['violation' => 'Pelanggaran', 'counseling' => 'Konseling'];
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Dalam penanganan', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
    $statusTones = ['new' => 'admin-status-info', 'in_progress' => 'admin-status-warning', 'waiting_follow_up' => 'admin-status-warning', 'completed' => 'admin-status-success'];
@endphp

<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="BK" title="Pengawasan Catatan BK"
            description="Konten profesional hanya-baca untuk administrator" :count="$records->total() . ' catatan'" />

        <div class="admin-glass-panel p-6">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4 md:items-end">
                <div>
                    <label for="bk-status-filter" class="admin-label">Status</label>
                    <select id="bk-status-filter" name="status" class="admin-field p-2.5">
                        <option value="">Semua status</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="bk-level-filter" class="admin-label">Jenjang</label>
                    <select id="bk-level-filter" name="school_level" class="admin-field p-2.5">
                        <option value="">Semua jenjang</option>
                        <option value="mi" @selected(request('school_level') === 'mi')>MI</option>
                        <option value="smp" @selected(request('school_level') === 'smp')>SMP</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 md:pb-3">
                    <input type="checkbox" id="bk-archived-filter" name="archived" value="1" class="admin-checkbox h-5 w-5 rounded"
                        @checked(request('archived'))>
                    <label for="bk-archived-filter" class="text-sm font-semibold">Tampilkan arsip</label>
                </div>
                <button type="submit" class="admin-button-primary w-full px-4 py-2.5 text-sm">Filter</button>
            </form>
        </div>

        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Siswa</th>
                            <th class="px-6 py-4 text-left">Guru BK</th>
                            <th class="px-6 py-4 text-left">Jenis</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td class="px-6 py-4 text-sm font-bold">
                                    <a class="underline-offset-2 hover:underline" href="{{ route('admin.bk-records.show', $record) }}">
                                        {{ $record->student?->nama_lengkap ?? 'Siswa dihapus' }}
                                    </a>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">{{ $record->counselor?->name ?? 'Akun dihapus' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">{{ $typeLabels[$record->record_type] ?? $record->record_type }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="{{ $statusTones[$record->status] ?? 'admin-status-neutral' }} px-2.5 py-1 text-xs">
                                        {{ $statusLabels[$record->status] ?? $record->status }}
                                    </span>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">{{ $record->occurred_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin.empty-state icon="clipboard" title="Belum ada catatan BK"
                                        hint="Ubah filter atau tunggu Guru BK mencatat kasus baru." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($records->hasPages())
                <div class="admin-panel-footer">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
