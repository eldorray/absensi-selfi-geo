@php
    $severityLabels = ['light' => 'Ringan', 'medium' => 'Sedang', 'heavy' => 'Berat'];
@endphp

<x-layouts.app>
    <div class="mx-auto max-w-2xl space-y-6">
        <x-admin.page-header kicker="BK" title="{{ $category->exists ? 'Edit' : 'Tambah' }} Kategori">
            <a href="{{ route('admin.bk-categories.index') }}"
                class="admin-button-secondary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
        </x-admin.page-header>

        <div class="admin-glass-panel p-6 md:p-8">
            <form method="POST"
                action="{{ $category->exists ? route('admin.bk-categories.update', $category) : route('admin.bk-categories.store') }}"
                class="space-y-6">
                @csrf
                @if ($category->exists)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="admin-label">Nama <span aria-hidden="true" class="admin-text-danger">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
                        class="admin-field p-2.5" required
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')
                        <p id="name-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="record_type" class="admin-label">Jenis <span aria-hidden="true" class="admin-text-danger">*</span></label>
                    <select id="record_type" name="record_type" class="admin-field p-2.5" required
                        @error('record_type') aria-invalid="true" aria-describedby="record_type-error" @enderror>
                        <option value="violation" @selected(old('record_type', $category->record_type) === 'violation')>Pelanggaran</option>
                        <option value="counseling" @selected(old('record_type', $category->record_type) === 'counseling')>Konseling</option>
                    </select>
                    @error('record_type')
                        <p id="record_type-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="default_severity" class="admin-label">Tingkat keparahan bawaan</label>
                    <select id="default_severity" name="default_severity" class="admin-field p-2.5"
                        @error('default_severity') aria-invalid="true" aria-describedby="default_severity-error" @enderror>
                        <option value="">Tidak ada</option>
                        @foreach ($severityLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('default_severity', $category->default_severity) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('default_severity')
                        <p id="default_severity-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="admin-label">Urutan <span aria-hidden="true" class="admin-text-danger">*</span></label>
                    <input type="number" id="sort_order" name="sort_order" min="0" max="65535"
                        value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="admin-field p-2.5" required
                        @error('sort_order') aria-invalid="true" aria-describedby="sort_order-error" @enderror>
                    @error('sort_order')
                        <p id="sort_order-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="admin-checkbox h-5 w-5 rounded"
                        @checked(old('is_active', $category->is_active ?? true))>
                    <label for="is_active" class="text-sm font-semibold">Aktif</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.bk-categories.index') }}" class="admin-button-secondary px-4 py-2 text-sm">
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
