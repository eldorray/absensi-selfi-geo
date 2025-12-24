<x-layouts.app>
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah Tahun Ajaran</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Buat periode tahun ajaran baru</p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <form action="{{ route('admin.academic-years.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Tahun Ajaran
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        placeholder="Contoh: 2024/2025"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Mulai
                        </label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Selesai
                        </label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-500 @enderror">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('admin.academic-years.index') }}"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
