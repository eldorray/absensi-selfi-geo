<x-layouts.app>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Informasi</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui kartu informasi</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                @include('admin.announcements._form')

                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('admin.announcements.index') }}"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium transition-colors">Batal</a>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
