<x-layouts.app>
    <div class="space-y-6" data-kesiswaan-list="admin">
        <x-admin.page-header kicker="Kesiswaan" title="Pusat Profil Siswa" description="Cari siswa lintas jenjang dan buka profil read-only untuk pengawasan rujukan."/>

        <form method="GET" action="{{ route('admin.kesiswaan.index') }}" class="admin-glass-panel grid gap-4 p-5 md:grid-cols-[minmax(0,1.5fr)_minmax(10rem,0.55fr)_minmax(12rem,0.7fr)_auto] md:items-end">
            <div><label for="kesiswaan-search" class="admin-label">Pencarian siswa</label><input id="kesiswaan-search" name="search" value="{{ request('search') }}" placeholder="Nama, NISN, NIK, atau kelas" class="admin-field mt-2 min-h-11 px-4 py-2.5"></div>
            <div><label for="kesiswaan-level" class="admin-label">Jenjang</label><select id="kesiswaan-level" name="school_level" class="admin-field mt-2 min-h-11 px-3 py-2.5"><option value="">Semua jenjang</option><option value="mi" @selected(request('school_level') === 'mi')>MI</option><option value="smp" @selected(request('school_level') === 'smp')>SMP</option></select></div>
            <div><label for="kesiswaan-class" class="admin-label">Kelas</label><select id="kesiswaan-class" name="school_class_id" class="admin-field mt-2 min-h-11 px-3 py-2.5"><option value="">Semua kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string) request('school_class_id') === (string) $class->id)>{{ strtoupper($class->school_level) }} · {{ $class->name }}</option>@endforeach</select></div>
            <button type="submit" class="admin-button-primary min-h-11 px-5 py-2.5">Terapkan</button>
        </form>

        <section class="admin-glass-panel overflow-hidden">
            <div class="admin-panel-header flex-wrap gap-3"><div><span class="admin-label">Daftar siswa</span><p class="admin-muted mt-1 text-xs">{{ $students->total() }} siswa sesuai filter.</p></div></div>
            <div class="divide-y admin-border">
                @forelse($students as $student)
                    @php $initials = collect(explode(' ', $student->nama_lengkap))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode(''); @endphp
                    <a href="{{ route('admin.kesiswaan.show', $student) }}" class="flex min-h-20 items-center gap-4 px-5 py-4 transition hover:bg-emerald-50/70 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-emerald-600 dark:hover:bg-emerald-950/30">
                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-xs font-black text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                        <span class="min-w-0 flex-1"><strong class="block truncate text-sm admin-text-main">{{ $student->nama_lengkap }}</strong><span class="admin-muted mt-1 block truncate text-xs">NISN {{ $student->nisn ?: '—' }} · NIK {{ $student->nik ?: '—' }}</span></span>
                        <span class="hidden rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold admin-text-muted sm:block dark:bg-slate-800">{{ strtoupper($student->school_level) }} · {{ $student->schoolClass?->name ?? 'Tanpa kelas' }}</span>
                        <svg class="size-5 shrink-0 admin-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"></path></svg>
                    </a>
                @empty
                    <div class="p-10 text-center"><p class="font-black admin-text-main">Siswa tidak ditemukan</p><p class="admin-muted mt-1 text-sm">Ubah pencarian atau filter untuk melihat data lain.</p></div>
                @endforelse
            </div>
        </section>

        {{ $students->links() }}
    </div>
</x-layouts.app>
