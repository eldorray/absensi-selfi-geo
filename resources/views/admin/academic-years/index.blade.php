<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Master Data" title="Tahun Ajaran"
            description="Kelola periode tahun ajaran untuk sistem absensi"
            :count="$academicYears->total() . ' periode'">
            <a href="{{ route('admin.academic-years.create') }}"
                class="admin-button-primary inline-flex items-center gap-2 px-4 py-2 text-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Tahun Ajaran
            </a>
        </x-admin.page-header>

        <!-- Table -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Nama</th>
                            <th class="px-6 py-4 text-left">Tanggal Mulai</th>
                            <th class="px-6 py-4 text-left">Tanggal Selesai</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($academicYears as $year)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="text-sm font-bold">{{ $year->name }}</span>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $year->start_date->format('d M Y') }}
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $year->end_date->format('d M Y') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    @if ($year->is_active)
                                        <span class="admin-status-success px-2.5 py-1 text-[10px] uppercase tracking-wider">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="admin-status-neutral px-2.5 py-1 text-[10px] uppercase tracking-wider">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (!$year->is_active)
                                            <form action="{{ route('admin.academic-years.activate', $year) }}"
                                                method="POST"
                                                onsubmit="return confirm('Aktifkan tahun ajaran ini? Jadwal kerja akan di-reset.')">
                                                @csrf
                                                <button type="submit"
                                                    class="admin-button-success admin-icon-action size-11 p-0"
                                                    title="Aktifkan">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.academic-years.edit', $year) }}"
                                            class="admin-button-primary admin-icon-action size-11 p-0"
                                            title="Edit tahun ajaran">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        @if (!$year->is_active)
                                            <form action="{{ route('admin.academic-years.destroy', $year) }}"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="admin-button-danger admin-icon-action size-11 p-0"
                                                    title="Hapus tahun ajaran">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin.empty-state icon="fas-calendar" title="Belum ada tahun ajaran"
                                        hint="Tambahkan tahun ajaran pertama untuk memulai penjadwalan absensi.">
                                        <a href="{{ route('admin.academic-years.create') }}"
                                            class="admin-button-secondary inline-flex items-center px-4 py-2 text-sm">
                                            Tambah Tahun Ajaran
                                        </a>
                                    </x-admin.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($academicYears->hasPages())
                <div class="admin-panel-footer">
                    {{ $academicYears->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
