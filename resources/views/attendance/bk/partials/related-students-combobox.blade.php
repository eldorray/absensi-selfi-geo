@php
    $relatedOptions = $students->map(fn ($student) => [
        'id' => $student->id,
        'name' => $student->nama_lengkap,
        'nisn' => $student->nisn ?: 'Tanpa NISN',
        'nik' => $student->nik ?: 'Tanpa NIK',
        'className' => $student->schoolClass?->name ?: ($student->tingkat_rombel ?: 'Belum ada kelas'),
        'search' => mb_strtolower(implode(' ', [$student->nama_lengkap, $student->nisn, $student->nik, $student->schoolClass?->name, $student->tingkat_rombel])),
    ])->values();
    $selectedRelatedIds = collect(old('related_student_ids', $record->exists ? $record->relatedStudents->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->values();
@endphp

<div data-bk-student-combobox="related"
    x-data="{
        open: false,
        query: '',
        selectedIds: @js($selectedRelatedIds),
        students: @js($relatedOptions),
        get filtered() {
            const keyword = this.query.trim().toLocaleLowerCase('id-ID');
            return keyword === '' ? this.students : this.students.filter(student => student.search.includes(keyword));
        },
        get selectedStudents() { return this.students.filter(student => this.selectedIds.includes(student.id)); },
        toggle(id) { this.selectedIds = this.selectedIds.includes(id) ? this.selectedIds.filter(value => value !== id) : [...this.selectedIds, id]; },
        remove(id) { this.selectedIds = this.selectedIds.filter(value => value !== id); },
    }"
    @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
    <span class="block text-xs font-bold">Siswa terkait <span class="font-normal theme-text-muted">(opsional)</span></span>
    <template x-for="id in selectedIds" :key="id"><input type="hidden" name="related_student_ids[]" :value="id"></template>
    <button type="button" @click="open = !open; if (open) $nextTick(() => $refs.relatedSearch.focus())"
        class="theme-input mt-2 flex min-h-12 w-full items-center justify-between rounded-2xl px-4 py-3 text-left"
        role="combobox" :aria-expanded="open" aria-controls="related-student-options" aria-haspopup="listbox">
        <span class="text-xs" x-text="selectedIds.length ? `${selectedIds.length} siswa dipilih` : 'Pilih siswa terkait'"></span>
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"></path></svg>
    </button>
    <div x-show="selectedStudents.length" class="mt-2 flex flex-wrap gap-2">
        <template x-for="student in selectedStudents" :key="student.id"><button type="button" @click="remove(student.id)" class="inline-flex min-h-9 items-center gap-1 rounded-full bg-emerald-500/15 px-3 text-[10px] font-bold"><span x-text="student.name"></span><span aria-hidden="true">×</span><span class="sr-only">Hapus siswa terkait</span></button></template>
    </div>
    <div x-show="open" x-cloak class="solid-panel absolute z-30 mt-2 w-full rounded-2xl p-2 shadow-xl">
        <input x-ref="relatedSearch" x-model="query" type="search" class="theme-input w-full rounded-xl p-3 text-xs" placeholder="Cari siswa terkait..." autocomplete="off">
        <ul id="related-student-options" role="listbox" aria-multiselectable="true" class="mt-2 max-h-60 space-y-1 overflow-y-auto">
            <template x-for="student in filtered" :key="student.id"><li role="option" :aria-selected="selectedIds.includes(student.id)"><button type="button" @click="toggle(student.id)" class="flex min-h-12 w-full items-center gap-3 rounded-xl px-3 py-2 text-left hover:bg-emerald-500/10"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded border border-emerald-500" :class="selectedIds.includes(student.id) ? 'bg-emerald-500 text-white' : ''"><span x-show="selectedIds.includes(student.id)">✓</span></span><span class="min-w-0"><span class="block truncate text-xs font-bold" x-text="student.name"></span><span class="block truncate text-[10px] theme-text-muted" x-text="`${student.className} · NISN ${student.nisn}`"></span></span></button></li></template>
            <li x-show="filtered.length === 0" class="px-3 py-6 text-center text-xs theme-text-muted">Siswa tidak ditemukan.</li>
        </ul>
    </div>
</div>
