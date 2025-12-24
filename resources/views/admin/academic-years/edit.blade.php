<x-layouts.app>
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Tahun Ajaran</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui periode tahun ajaran</p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <form action="{{ route('admin.academic-years.update', $academicYear) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Tahun Ajaran
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $academicYear->name) }}"
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
                        <input type="date" name="start_date" id="start_date"
                            value="{{ old('start_date', $academicYear->start_date->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Selesai
                        </label>
                        <input type="date" name="end_date" id="end_date"
                            value="{{ old('end_date', $academicYear->end_date->format('Y-m-d')) }}"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-500 @enderror">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($academicYear->is_active)
                    <div
                        class="p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-green-700 dark:text-green-300 font-medium">Tahun ajaran ini sedang
                                aktif</span>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('admin.academic-years.index') }}"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
