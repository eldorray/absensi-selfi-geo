<x-layouts.app>
    <div class="mx-auto max-w-2xl space-y-6">
        <x-admin.page-header kicker="Master Data" title="Tambah Tahun Ajaran"
            description="Buat periode tahun ajaran baru" />

        <!-- Form -->
        <div class="admin-glass-panel p-6 md:p-8">
            <form action="{{ route('admin.academic-years.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="admin-label">Nama Tahun Ajaran</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        placeholder="Contoh: 2024/2025"
                        class="admin-field p-2.5 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="start_date" class="admin-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                            class="admin-field p-2.5 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="admin-hint admin-text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="admin-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                            class="admin-field p-2.5 @error('end_date') border-red-500 @enderror">
                        @error('end_date')
                            <p class="admin-hint admin-text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.academic-years.index') }}"
                        class="admin-button-secondary px-4 py-2 text-sm">
                        Batal
                    </a>
                    <button type="submit" class="admin-button-primary px-6 py-2 text-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
