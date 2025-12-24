<x-layouts.app>
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.offices.index') }}" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Kantor</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui detail lokasi kantor</p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <form method="POST" action="{{ route('admin.offices.update', $office) }}">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Kantor
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $office->name) }}"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coordinates -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Latitude
                        </label>
                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $office->latitude) }}"
                            class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('latitude')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Longitude
                        </label>
                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $office->longitude) }}"
                            class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('longitude')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Radius -->
                <div class="mb-6">
                    <label for="radius_meters" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Radius (meter)
                    </label>
                    <input type="number" name="radius_meters" id="radius_meters" value="{{ old('radius_meters', $office->radius_meters) }}"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        min="10" max="5000">
                    @error('radius_meters')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.offices.index') }}"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors">
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
