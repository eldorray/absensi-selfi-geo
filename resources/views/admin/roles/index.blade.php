<x-layouts.app :title="'Kelola Role'">
    <div class="space-y-6">
        <x-admin.page-header kicker="Master Data" title="Kelola Role" description="Atur role pengguna di sistem"
            :count="$roles->count() . ' role'">
            <a href="{{ route('admin.roles.create') }}"
                class="admin-button-primary inline-flex items-center gap-2 px-4 py-2 text-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Role
            </a>
        </x-admin.page-header>

        @if ($errors->any())
            <div class="admin-alert-danger rounded-2xl px-4 py-3 text-sm font-semibold">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="admin-glass-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">Role</th>
                            <th class="px-6 py-4 text-left">Slug</th>
                            <th class="px-6 py-4 text-left">Akses Admin</th>
                            <th class="px-6 py-4 text-left">Jumlah User</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td class="px-6 py-4">
                                    <span
                                        class="{{ $role->is_admin? 'admin-status-danger': match ($role->slug) {'tendik' => 'admin-status-success','kepala-sekolah', 'guru' => 'admin-status-info',default => 'admin-status-neutral'} }} px-2.5 py-1 text-xs">
                                        {{ $role->name }}
                                    </span>
                                    @if ($role->description)
                                        <p class="admin-muted mt-1.5 text-xs">{{ $role->description }}</p>
                                    @endif
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 font-mono text-xs">
                                    {{ $role->slug }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($role->is_admin)
                                        <span class="admin-status-success px-2.5 py-1 text-xs">Ya</span>
                                    @else
                                        <span class="admin-status-neutral px-2.5 py-1 text-xs">Tidak</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $role->users_count }} user
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.roles.edit', $role) }}"
                                            class="admin-row-action admin-row-action-edit" title="Edit role"
                                            aria-label="Edit role {{ $role->name }}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
</svg></a>
                                        @if ($role->users_count === 0)
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                class="inline" x-data="{}"
                                                @submit.prevent="$dispatch('admin-confirm', {
                                                    title: 'Hapus Role',
                                                    message: @js('Hapus role '.$role->name.'? Tindakan ini tidak dapat dibatalkan.'),
                                                    confirmText: 'Hapus',
                                                    variant: 'danger',
                                                    form: $el,
                                                })">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="admin-row-action admin-row-action-delete" title="Hapus role"
                                                    aria-label="Hapus role {{ $role->name }}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
</svg></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin.empty-state icon="fas-user-tag" title="Belum ada role"
                                        hint="Tambahkan role pertama untuk mengatur hak akses pengguna.">
                                        <a href="{{ route('admin.roles.create') }}"
                                            class="admin-button-secondary inline-flex items-center px-4 py-2 text-sm">
                                            Tambah Role
                                        </a>
                                    </x-admin.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
