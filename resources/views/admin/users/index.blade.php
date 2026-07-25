<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Master Data" title="Kelola User"
            description="Manajemen akun karyawan dan administrator" :count="$users->total() . ' user'">
            <form action="{{ route('admin.users.sync') }}" method="POST" x-data="{}"
                @submit.prevent="$dispatch('admin-confirm', {
                    title: 'Sync Data Induk',
                    message: 'Tarik data ' + $el.source.options[$el.source.selectedIndex].text + ' dari API data induk? Ini membuat/memperbarui user.',
                    confirmText: 'Sync',
                    variant: 'primary',
                    form: $el,
                })"
                class="flex items-center gap-2">
                @csrf
                <select name="source" aria-label="Unit sumber sync" class="admin-field !w-auto px-3 py-2 text-sm">
                    <option value="guru-mi">Guru MI</option>
                    <option value="guru-smp">Guru SMP</option>
                </select>
                <button type="submit" class="admin-button-secondary inline-flex items-center gap-2 px-4 py-2 text-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Sync Data Induk
                </button>
            </form>
            <a href="{{ route('admin.users.export-pdf') }}"
                class="admin-button-secondary inline-flex items-center gap-2 px-4 py-2 text-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Cetak PDF Password
            </a>
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
                            <th class="px-6 py-4 text-left">Password</th>
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
                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($user->visible_password)
                                        <div class="flex items-center gap-1.5"
                                            x-data="{
                                                show: false,
                                                pw: @js($user->visible_password),
                                                copied: false,
                                                copy() {
                                                    navigator.clipboard.writeText(this.pw);
                                                    this.copied = true;
                                                    setTimeout(() => this.copied = false, 1500);
                                                },
                                            }">
                                            <span class="font-mono text-sm" x-text="show ? pw : '••••••••'"></span>
                                            <button type="button" @click="show = !show"
                                                class="admin-icon-action size-8 p-0"
                                                :title="show ? 'Sembunyikan' : 'Lihat password'">
                                                <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <svg x-show="show" x-cloak class="h-4 w-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="copy()"
                                                class="admin-icon-action size-8 p-0"
                                                :title="copied ? 'Tersalin' : 'Salin password'">
                                                <svg x-show="!copied" class="h-4 w-4" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <svg x-show="copied" x-cloak class="h-4 w-4 text-green-500"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <span class="admin-muted text-xs italic">belum di-set</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', ['user' => $user] + request()->query()) }}"
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
                                            <form action="{{ route('admin.users.reset-password', $user) }}"
                                                method="POST" x-data="{}"
                                                @submit.prevent="$dispatch('admin-confirm', {
                                                    title: 'Reset Password',
                                                    message: 'Reset password user ini ke default Guru12345? Password lama akan diganti.',
                                                    confirmText: 'Reset',
                                                    variant: 'primary',
                                                    form: $el,
                                                })">
                                                @csrf
                                                <button type="submit"
                                                    class="admin-button-secondary admin-icon-action size-11 p-0"
                                                    title="Reset password ke Guru12345">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 7a2 2 0 012 2m4-2a6 6 0 01-7.743 5.743L11 14H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                x-data="{}"
                                                @submit.prevent="$dispatch('admin-confirm', {
                                                    title: 'Hapus User',
                                                    message: 'Yakin ingin menghapus user ini?',
                                                    confirmText: 'Hapus',
                                                    variant: 'danger',
                                                    form: $el,
                                                })">
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
                                <td colspan="6">
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
