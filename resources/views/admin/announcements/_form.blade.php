@php($a = $announcement ?? null)

<div>
    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Judul</label>
    <input type="text" name="title" id="title" value="{{ old('title', $a?->title) }}"
        placeholder="Contoh: Rapat Guru Semester Genap"
        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-500 @enderror">
    @error('title')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Ringkasan <span class="text-gray-400">(tampil di kartu, opsional)</span>
    </label>
    <input type="text" name="summary" id="summary" value="{{ old('summary', $a?->summary) }}"
        placeholder="Ringkasan singkat 1 baris"
        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('summary') border-red-500 @enderror">
    @error('summary')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Isi Informasi</label>
    <textarea name="body" id="body" rows="8"
        placeholder="Tulis isi lengkap informasi di sini..."
        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('body') border-red-500 @enderror">{{ old('body', $a?->body) }}</textarea>
    @error('body')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Gambar <span class="text-gray-400">(opsional, maks 2MB)</span>
    </label>
    @if ($a?->image_url)
        <img src="{{ $a->image_url }}" alt="" class="mb-2 h-28 w-auto rounded-xl object-cover border border-gray-200 dark:border-gray-700">
        <p class="mb-2 text-xs text-gray-400">Unggah gambar baru untuk mengganti yang lama.</p>
    @endif
    <input type="file" name="image" id="image" accept="image/*"
        class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-indigo-700 hover:file:bg-indigo-100 @error('image') border-red-500 @enderror">
    @error('image')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Urutan <span class="text-gray-400">(kecil = lebih dulu)</span>
        </label>
        <input type="number" name="sort_order" id="sort_order" min="0" max="9999"
            value="{{ old('sort_order', $a?->sort_order ?? 0) }}"
            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('sort_order') border-red-500 @enderror">
        @error('sort_order')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $a?->is_active ?? true))
                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif (tampilkan ke guru)</span>
        </label>
    </div>
</div>
