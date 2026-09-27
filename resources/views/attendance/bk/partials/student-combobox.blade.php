@php
    $studentOptions = $students->map(fn ($student) => [
        'id' => $student->id,
        'name' => $student->nama_lengkap,
        'nisn' => $student->nisn ?: 'Tanpa NISN',
        'nik' => $student->nik ?: 'Tanpa NIK',
        'className' => $student->schoolClass?->name ?: ($student->tingkat_rombel ?: 'Belum ada kelas'),
        'search' => mb_strtolower(implode(' ', [
            $student->nama_lengkap,
            $student->nisn,
            $student->nik,
            $student->schoolClass?->name,
            $student->tingkat_rombel,
        ])),
    ])->values();
    $selectedStudentId = (int) old('student_id', $record->student_id ?? 0);
@endphp

<div data-bk-student-combobox="primary"
    x-data="{
        open: false,
        query: '',
        activeIndex: 0,
        selectedId: @js($selectedStudentId),
        students: @js($studentOptions),
        get filtered() {
            const keyword = this.query.trim().toLocaleLowerCase('id-ID');
            return keyword === '' ? this.students : this.students.filter(student => student.search.includes(keyword));
        },
        get selected() { return this.students.find(student => student.id === Number(this.selectedId)); },
        choose(student) { this.selectedId = student.id; this.query = ''; this.open = false; this.activeIndex = 0; },
        move(step) {
            if (!this.open) this.open = true;
            const total = this.filtered.length;
            if (total > 0) this.activeIndex = (this.activeIndex + step + total) % total;
        },
        chooseActive() { if (this.filtered[this.activeIndex]) this.choose(this.filtered[this.activeIndex]); },
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative">
    <label for="primary-student-search" class="g-label">Siswa utama</label>
    <input type="hidden" name="student_id" :value="selectedId" required>
    <button type="button" @click="open = !open; if (open) $nextTick(() => $refs.search.focus())"
        class="g-input mt-2 flex w-full items-center justify-between gap-3 rounded-2xl px-4 py-3 text-left"
        role="combobox" :aria-expanded="open" aria-controls="primary-student-options" aria-haspopup="listbox">
        <span class="min-w-0">
            <span class="block truncate text-xs font-bold" x-text="selected?.name || 'Pilih siswa'"></span>
            <span class="block truncate text-xs text-guru-muted" x-show="selected" x-text="selected ? `${selected.className} · NISN ${selected.nisn}` : 'Cari nama, NISN, NIK, atau kelas'"></span>
        </span>
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"></path></svg>
    </button>

    <div x-show="open" x-cloak x-transition.origin.top
        class="absolute z-40 mt-2 w-full rounded-2xl border border-guru-border bg-guru-surface p-2 shadow-xl">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-guru-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
            <input id="primary-student-search" x-ref="search" x-model="query" type="search"
                @input="activeIndex = 0" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="chooseActive()"
                class="g-input pl-10" placeholder="Cari siswa..." autocomplete="off">
        </div>
        <ul id="primary-student-options" role="listbox" class="mt-2 max-h-60 space-y-1 overflow-y-auto">
            <template x-for="(student, index) in filtered" :key="student.id">
                <li role="option" :aria-selected="selectedId === student.id">
                    <button type="button" @click="choose(student)" @mouseenter="activeIndex = index"
                        class="flex min-h-12 w-full items-center justify-between rounded-xl px-3 py-2 text-left"
                        :class="activeIndex === index ? 'bg-guru-primary-soft text-guru-primary' : 'hover:bg-guru-surface-2'">
                        <span class="min-w-0"><span class="block truncate text-xs font-bold" x-text="student.name"></span><span class="block truncate text-xs text-guru-muted" x-text="`${student.className} · NISN ${student.nisn} · NIK ${student.nik}`"></span></span>
                        <svg x-show="selectedId === student.id" class="h-4 w-4 shrink-0 text-guru-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 13 4 4L19 7"></path></svg>
                    </button>
                </li>
            </template>
            <li x-show="filtered.length === 0" class="px-3 py-6 text-center text-xs text-guru-muted">Siswa tidak ditemukan.</li>
        </ul>
    </div>
    @error('student_id')<p class="g-error">{{ $message }}</p>@enderror
</div>
