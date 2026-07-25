<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Komunikasi" title="Informasi"
            description="Kartu informasi geser yang tampil di beranda guru" :count="$announcements->total() . ' kartu'">
            <a href="{{ route('admin.announcements.create') }}"
                class="admin-button-primary inline-flex items-center gap-2 px-4 py-2 text-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Informasi
            </a>
        </x-admin.page-header>

        <!-- Table -->
        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Informasi</th>
                            <th class="px-6 py-4 text-center">Urutan</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($item->image_url)
                                            <img src="{{ $item->image_url }}" alt=""
                                                class="h-12 w-12 flex-shrink-0 rounded-xl object-cover">
                                        @else
                                            <div
                                                class="h-12 w-12 flex-shrink-0 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600">
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="max-w-xs truncate text-sm font-bold">{{ $item->title }}</p>
                                            <p class="admin-muted max-w-xs truncate text-xs">{{ $item->summary }}</p>
                                            <span class="admin-chip mt-1 inline-block">{{ $item->office?->name ?? 'Semua Kantor' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <span class="admin-chip admin-chip-time">{{ $item->sort_order }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <form action="{{ route('admin.announcements.toggle', $item) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="{{ $item->is_active ? 'admin-status-success' : 'admin-status-neutral' }} cursor-pointer px-3 py-1 text-xs transition-colors"
                                            title="Klik untuk ubah status">
                                            {{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.announcements.edit', $item) }}"
                                            class="admin-button-primary admin-icon-action size-11 p-0"
                                            title="Edit informasi">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.announcements.destroy', $item) }}" method="POST"
                                            x-data="{}"
                                            @submit.prevent="$dispatch('admin-confirm', {
                                                title: 'Hapus Informasi',
                                                message: 'Yakin ingin menghapus informasi ini?',
                                                confirmText: 'Hapus',
                                                variant: 'danger',
                                                form: $el,
                                            })">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="admin-button-danger admin-icon-action size-11 p-0"
                                                title="Hapus informasi">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
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
                                <td colspan="4">
                                    <x-admin.empty-state icon="fas-bullhorn" title="Belum ada informasi"
                                        hint="Kartu informasi yang aktif akan tampil di beranda guru.">
                                        <a href="{{ route('admin.announcements.create') }}"
                                            class="admin-button-secondary inline-flex items-center px-4 py-2 text-sm">
                                            Tambah Informasi
                                        </a>
                                    </x-admin.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($announcements->hasPages())
                <div class="admin-panel-footer">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
