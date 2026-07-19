<x-layouts.app>
    <div class="mx-auto max-w-2xl space-y-6">
        <x-admin.page-header kicker="Master Data" title="Edit User"
            description="Perbarui akun {{ $user->name }}">
            <a href="{{ route('admin.users.index') }}"
                class="admin-button-secondary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
        </x-admin.page-header>

        <!-- Form -->
        <div class="admin-glass-panel p-6 md:p-8">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="admin-label">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                        class="admin-field p-2.5">
                    @error('name')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="admin-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                        class="admin-field p-2.5">
                    @error('email')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <hr class="admin-divider">

                <!-- Password -->
                <div>
                    <label for="password" class="admin-label">Password Baru</label>
                    <input type="password" name="password" id="password" class="admin-field p-2.5">
                    <p class="admin-hint">Kosongkan jika tidak ingin mengubah password.</p>
                    @error('password')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="admin-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="admin-field p-2.5">
                </div>

                <hr class="admin-divider">

                <!-- Role -->
                <div>
                    <label for="role_id" class="admin-label">Role</label>
                    <select name="role_id" id="role_id" class="admin-field p-2.5">
                        <option value="">-- Pilih Role --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Office -->
                <div>
                    <label for="office_id" class="admin-label">Kantor</label>
                    <select name="office_id" id="office_id" class="admin-field p-2.5">
                        <option value="">-- Pilih Kantor --</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office->id }}"
                                {{ old('office_id', $user->office_id) == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('office_id')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="admin-button-secondary px-4 py-2 text-sm">
                        Batal
                    </a>
                    <button type="submit" class="admin-button-primary px-6 py-2 text-sm">
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
