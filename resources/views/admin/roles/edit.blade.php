<x-layouts.app :title="'Edit Role'">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Role</h1>
                <p class="text-gray-500 dark:text-gray-400">Ubah data role: {{ $role->name }}</p>
            </div>
            <a href="{{ route('admin.roles.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
                        Role</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                        class="mt-1 block w-full p-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $role->slug) }}"
                        class="mt-1 block w-full p-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                        class="mt-1 block w-full p-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('description', $role->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_admin" id="is_admin" value="1"
                        class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                        {{ old('is_admin', $role->is_admin) ? 'checked' : '' }}>
                    <label for="is_admin" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Role ini memiliki akses ke Admin Panel
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
