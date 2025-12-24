<x-layouts.app>
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah User Baru</h1>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <!-- Name -->
                <div class="mb-6">
                    <label for="name"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                    <input type="password" name="password" id="password"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div class="mb-6">
                    <label for="password_confirmation"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Konfirmasi
                        Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label for="role_id"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                    <select name="role_id" id="role_id"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Role --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Office -->
                <div class="mb-6">
                    <label for="office_id"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kantor</label>
                    <select name="office_id" id="office_id"
                        class="w-full p-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Kantor --</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}"
                                {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('office_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.users.index') }}"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-colors">
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
