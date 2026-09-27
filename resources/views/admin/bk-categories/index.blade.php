@php
    $typeLabels = ['violation' => 'Pelanggaran', 'counseling' => 'Konseling'];
    $severityLabels = ['light' => 'Ringan', 'medium' => 'Sedang', 'heavy' => 'Berat'];
@endphp

<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="BK" title="Kategori BK" description="Kategori pelanggaran dan konseling"
            :count="$categories->total() . ' kategori'">
            <a href="{{ route('admin.bk-categories.create') }}"
                class="admin-button-primary inline-flex items-center gap-2 px-4 py-2 text-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Kategori
            </a>
        </x-admin.page-header>

        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Nama</th>
                            <th class="px-6 py-4 text-left">Jenis</th>
                            <th class="px-6 py-4 text-left">Tingkat Keparahan</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-6 py-4 text-sm font-bold">{{ $category->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $typeLabels[$category->record_type] ?? $category->record_type }}
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $category->default_severity ? ($severityLabels[$category->default_severity] ?? $category->default_severity) : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="{{ $category->is_active ? 'admin-status-success' : 'admin-status-neutral' }} px-2.5 py-1 text-xs">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.bk-categories.edit', $category) }}"
                                            class="admin-row-action admin-row-action-edit" title="Edit kategori"
                                            aria-label="Edit kategori {{ $category->name }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin.empty-state icon="fas-tags" title="Belum ada kategori BK"
                                        hint="Tambahkan kategori pelanggaran atau konseling untuk dipakai Guru BK.">
                                        <a href="{{ route('admin.bk-categories.create') }}"
                                            class="admin-button-secondary inline-flex items-center px-4 py-2 text-sm">
                                            Tambah Kategori
                                        </a>
                                    </x-admin.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages())
                <div class="admin-panel-footer">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
