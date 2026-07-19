<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Master Data" title="Kelola User"
            description="Manajemen akun karyawan dan administrator" :count="$users->total() . ' user'">
            <form action="{{ route('admin.users.sync') }}" method="POST"
                onsubmit="return confirm('Tarik data pegawai dari API data induk? Ini membuat/memperbarui user.')">
                @csrf
                <button type="submit"
                    class="admin-button-secondary inline-flex items-center gap-2 px-4 py-2 text-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Sync Data Induk
                </button>
            </form>
            <a href="{{ route('admin.users.create') }}"
                class="admin-button-primary inline-flex items-center gap-2 px-4 py-2 text-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah User
            </a>
        </x-admin.page-header>

        <!-- Messages -->
        @if (session('success'))
            <div class="admin-alert-success flex items-center gap-3 rounded-2xl p-4">
                <svg class="h-5 w-5 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="admin-alert-danger flex items-center gap-3 rounded-2xl p-4">
                <svg class="h-5 w-5 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filters -->
        <div class="admin-glass-panel p-6">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="filter-search" class="admin-label">Cari</label>
                    <input id="filter-search" type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nama atau email..." class="admin-field p-2.5">
                </div>
                <div>
                    <label for="filter-role" class="admin-label">Role</label>
                    <select id="filter-role" name="role_id" class="admin-field p-2.5">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
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
                            <th class="px-6 py-4 text-left">Nama</th>
                            <th class="px-6 py-4 text-left">Email</th>
                            <th class="px-6 py-4 text-left">Role</th>
                            <th class="px-6 py-4 text-left">Kantor</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="admin-avatar">{{ $user->initials() }}</span>
                                        <span class="text-sm font-bold">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $user->email }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="{{ $user->role?->is_admin? 'admin-status-danger': match ($user->role?->slug) {'tendik' => 'admin-status-success','kepala-sekolah', 'guru' => 'admin-status-info',default => 'admin-status-neutral'} }} px-2.5 py-1 text-xs">
                                        {{ $user->role?->name ?? 'No Role' }}
                                    </span>
                                </td>
                                <td class="admin-muted whitespace-nowrap px-6 py-4 text-sm">
                                    {{ $user->office?->name ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="admin-button-primary admin-icon-action size-11 p-0"
                                            title="Edit user">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="admin-button-danger admin-icon-action size-11 p-0"
                                                    title="Hapus user">
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
                                    <x-admin.empty-state icon="fas-users" title="Tidak ada user yang ditemukan"
                                        hint="Ubah kata kunci atau filter, atau tambahkan user baru." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="admin-panel-footer">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
