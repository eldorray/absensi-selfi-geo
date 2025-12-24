<x-layouts.app :title="'Kelola Role'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kelola Role</h1>
                <p class="text-gray-500 dark:text-gray-400">Atur role pengguna di sistem</p>
            </div>
            <a href="{{ route('admin.roles.create') }}"
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Role
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Role</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Slug</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Akses Admin</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Jumlah User</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $role->badge_class }}">
                                        {{ $role->name }}
                                    </span>
                                </div>
                                @if ($role->description)
                                    <p class="text-xs text-gray-500 mt-1">{{ $role->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $role->slug }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($role->is_admin)
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ya</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Tidak</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $role->users_count }} user
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.roles.edit', $role) }}"
                                    class="text-sky-600 hover:text-sky-900 dark:text-sky-400 mr-3">Edit</a>
                                @if ($role->users_count === 0)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Yakin ingin menghapus role ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Belum ada role. <a href="{{ route('admin.roles.create') }}"
                                    class="text-sky-600 hover:underline">Tambah role pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
