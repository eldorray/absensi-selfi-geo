<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Master Data" title="Kelola Kantor"
            description="Manajemen lokasi kantor untuk geofencing absensi" :count="$offices->total() . ' kantor'">
            <a href="{{ route('admin.offices.create') }}"
                class="admin-button-primary inline-flex items-center gap-2 px-4 py-2 text-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Kantor
            </a>
        </x-admin.page-header>

        <!-- Success Message -->
        @if (session('success'))
            <div class="admin-alert-success flex items-center gap-3 rounded-2xl p-4">
                <svg class="h-5 w-5 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Table -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Nama Kantor</th>
                            <th class="px-6 py-4 text-left">Koordinat</th>
                            <th class="px-6 py-4 text-left">Radius</th>
                            <th class="px-6 py-4 text-left">Karyawan</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($offices as $office)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="admin-stat-icon admin-stat-icon-sm admin-tone-sky" aria-hidden="true">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                </path>
                                            </svg>
                                        </span>
                                        <span class="text-sm font-bold">{{ $office->name }}</span>
                                    </div>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    <span class="font-mono text-xs">{{ number_format($office->latitude, 6) }},
                                        {{ number_format($office->longitude, 6) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="admin-status-info px-2.5 py-1 text-xs">{{ $office->radius_meters }} m</span>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $office->users_count }} orang
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.offices.edit', $office) }}"
                                            class="admin-button-primary admin-icon-action size-11 p-0" title="Edit kantor">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.offices.destroy', $office) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus kantor ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="admin-button-danger admin-icon-action size-11 p-0" title="Hapus kantor">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin.empty-state icon="fas-building" title="Belum ada kantor"
                                        hint="Tambahkan kantor pertama untuk mengaktifkan geofencing absensi.">
                                        <a href="{{ route('admin.offices.create') }}"
                                            class="admin-button-secondary inline-flex items-center px-4 py-2 text-sm">
                                            Tambah Kantor
                                        </a>
                                    </x-admin.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($offices->hasPages())
                <div class="admin-panel-footer">
                    {{ $offices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
