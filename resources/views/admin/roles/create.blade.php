<x-layouts.app :title="'Tambah Role'">
    <div class="mx-auto max-w-2xl space-y-6">
        <x-admin.page-header kicker="Master Data" title="Tambah Role" description="Buat role pengguna baru">
            <a href="{{ route('admin.roles.index') }}"
                class="admin-button-secondary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </x-admin.page-header>

        <div class="admin-glass-panel p-6 md:p-8">
            <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="admin-label">Nama Role</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="admin-field p-2.5" placeholder="Contoh: Kepala Sekolah">
                    @error('name')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="admin-label">Slug (opsional)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                        class="admin-field p-2.5" placeholder="kepala-sekolah">
                    <p class="admin-hint">Akan dibuat otomatis dari nama role jika dikosongkan.</p>
                    @error('slug')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="admin-label">Deskripsi</label>
                    <textarea name="description" id="description" rows="3" class="admin-field p-2.5"
                        placeholder="Deskripsi singkat tentang role ini">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_admin" id="is_admin" value="1"
                        class="admin-checkbox h-4 w-4 rounded" {{ old('is_admin') ? 'checked' : '' }}>
                    <label for="is_admin" class="ml-2 block text-sm">
                        Role ini memiliki akses ke Admin Panel
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.roles.index') }}" class="admin-button-secondary px-4 py-2 text-sm">
                        Batal
                    </a>
                    <button type="submit" class="admin-button-primary px-6 py-2 text-sm">
                        Simpan Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
