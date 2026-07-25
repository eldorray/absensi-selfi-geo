@php($a = $announcement ?? null)

<div>
    <label for="title" class="admin-label">Judul</label>
    <input type="text" name="title" id="title" value="{{ old('title', $a?->title) }}"
        placeholder="Contoh: Rapat Guru Semester Genap"
        class="admin-field p-2.5 @error('title') border-red-500 @enderror">
    @error('title')
        <p class="admin-hint admin-text-danger">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="summary" class="admin-label">Ringkasan</label>
    <input type="text" name="summary" id="summary" value="{{ old('summary', $a?->summary) }}"
        placeholder="Ringkasan singkat 1 baris" class="admin-field p-2.5 @error('summary') border-red-500 @enderror">
    <p class="admin-hint">Tampil di kartu, opsional.</p>
    @error('summary')
        <p class="admin-hint admin-text-danger">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="body" class="admin-label">Isi Informasi</label>
    <textarea name="body" id="body" rows="8" placeholder="Tulis isi lengkap informasi di sini..."
        class="admin-field p-2.5 @error('body') border-red-500 @enderror">{{ old('body', $a?->body) }}</textarea>
    @error('body')
        <p class="admin-hint admin-text-danger">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="office_id" class="admin-label">Kantor</label>
    <select name="office_id" id="office_id"
        class="admin-field p-2.5 @error('office_id') border-red-500 @enderror">
        <option value="">Semua Kantor</option>
        @foreach ($offices as $office)
            <option value="{{ $office->id }}" @selected(old('office_id', $a?->office_id) == $office->id)>{{ $office->name }}
            </option>
        @endforeach
    </select>
    <p class="admin-hint">Pilih kantor tujuan, atau "Semua Kantor" agar tampil ke semua guru.</p>
    @error('office_id')
        <p class="admin-hint admin-text-danger">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="image" class="admin-label">Gambar</label>
    @if ($a?->image_url)
        <img src="{{ $a->image_url }}" alt="" class="mb-2 h-28 w-auto rounded-xl border object-cover"
            style="border-color: var(--admin-border-soft)">
        <p class="admin-hint mb-2" style="margin-top: 0">Unggah gambar baru untuk mengganti yang lama.</p>
    @endif
    <input type="file" name="image" id="image" accept="image/*"
        class="admin-field p-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-indigo-700 hover:file:bg-indigo-100 @error('image') border-red-500 @enderror">
    <p class="admin-hint">Opsional, maksimal 2MB.</p>
    @error('image')
        <p class="admin-hint admin-text-danger">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="sort_order" class="admin-label">Urutan</label>
        <input type="number" name="sort_order" id="sort_order" min="0" max="9999"
            value="{{ old('sort_order', $a?->sort_order ?? 0) }}"
            class="admin-field p-2.5 @error('sort_order') border-red-500 @enderror">
        <p class="admin-hint">Angka kecil tampil lebih dulu.</p>
        @error('sort_order')
            <p class="admin-hint admin-text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end pb-1">
        <label class="inline-flex cursor-pointer items-center gap-3">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $a?->is_active ?? true))
                class="admin-checkbox h-5 w-5 rounded">
            <span class="text-sm font-medium">Aktif (tampilkan ke guru)</span>
        </label>
    </div>
</div>
