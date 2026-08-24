<x-layouts.mobile title="Kesiswaan" backUrl="{{ route('attendance.dashboard') }}">
    <div class="space-y-5 p-4 pb-8" data-kesiswaan-list="mobile">
        <section data-kesiswaan-hero="directory" class="overflow-hidden rounded-[28px] border border-emerald-200/70 bg-emerald-50 dark:border-emerald-800/60 dark:bg-emerald-950/45">
            <div class="h-2 bg-emerald-600 dark:bg-emerald-500"></div>
            <div class="p-5">
                <div class="flex items-start gap-4">
                    <span class="grid size-12 shrink-0 place-items-center rounded-[17px] bg-emerald-200 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100" aria-hidden="true">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-700 dark:text-emerald-300">Petugas Kesiswaan</p>
                        <h1 class="mt-1 text-2xl font-black leading-tight theme-text-main">Direktori siswa</h1>
                        <p class="mt-2 text-xs leading-5 theme-text-muted">Buka profil siswa, lihat informasi akademik, dan pantau ringkasan penanganan sesuai kewenangan Anda.</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <div class="rounded-2xl bg-white/75 p-3 dark:bg-white/5">
                        <span class="block text-[9px] font-bold uppercase theme-text-muted">Cakupan siswa</span>
                        <strong class="mt-1 block text-sm font-black theme-text-main">{{ strtoupper(auth()->user()->office?->school_level ?? '-') }}</strong>
                    </div>
                    <div class="rounded-2xl bg-white/75 p-3 dark:bg-white/5">
                        <span class="block text-[9px] font-bold uppercase theme-text-muted">Hasil ditemukan</span>
                        <strong class="mt-1 block text-sm font-black text-emerald-800 dark:text-emerald-200">{{ $students->total() }} siswa</strong>
                    </div>
                </div>
            </div>
        </section>

        <form data-kesiswaan-search="students" method="GET" action="{{ route('attendance.kesiswaan.index') }}" class="solid-panel rounded-[22px] p-3">
            <label for="student-search" class="sr-only">Cari nama, NISN, atau NIK</label>
            <div class="flex items-center gap-2">
                <div class="relative min-w-0 flex-1">
                    <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 theme-text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path stroke-linecap="round" d="m20 20-3.5-3.5"></path></svg>
                    <input id="student-search" name="search" value="{{ request('search') }}" placeholder="Nama, NISN, atau NIK" class="theme-input min-h-12 w-full rounded-[18px] py-3 pl-12 pr-4 text-sm">
                </div>
                <button type="submit" class="theme-btn-submit grid size-12 shrink-0 place-items-center rounded-[17px]" aria-label="Cari siswa">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path stroke-linecap="round" d="m20 20-3.5-3.5"></path></svg>
                </button>
            </div>
            @if(request('search'))
                <div class="mt-2 flex items-center justify-between gap-3 px-1 text-[11px]"><p class="truncate theme-text-muted">Hasil untuk “{{ request('search') }}”</p><a href="{{ route('attendance.kesiswaan.index') }}" class="shrink-0 font-black text-emerald-700 dark:text-emerald-300">Hapus pencarian</a></div>
            @endif
        </form>

        <section id="student-directory" class="min-w-0 scroll-mt-4 space-y-3">
            <div class="flex items-end justify-between gap-3 px-1">
                <div><h2 class="text-sm font-black theme-text-main">Siswa dalam cakupan</h2><p class="mt-1 text-[11px] theme-text-muted">Pilih satu siswa untuk membuka profil.</p></div>
                <span class="text-xs font-black text-emerald-700 dark:text-emerald-300">{{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }}</span>
            </div>

            <div class="space-y-2">
                @forelse($students as $student)
                    @php
                        $initials = collect(explode(' ', $student->nama_lengkap))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->implode('');
                    @endphp
                    <a href="{{ route('attendance.kesiswaan.show', $student) }}" class="solid-panel group flex min-h-[72px] items-center gap-3 rounded-[20px] p-3 transition hover:border-emerald-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                        <span class="grid size-12 shrink-0 place-items-center rounded-[16px] bg-emerald-100 text-xs font-black text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                        <span class="min-w-0 flex-1">
                            <strong class="block truncate text-sm theme-text-main">{{ $student->nama_lengkap }}</strong>
                            <span class="mt-1 flex min-w-0 items-center gap-1.5 text-[11px] theme-text-muted">
                                <span class="truncate">{{ $student->schoolClass?->name ?? 'Belum memiliki kelas' }}</span>
                                <span aria-hidden="true">·</span>
                                <span class="shrink-0">NISN {{ $student->nisn ?: '-' }}</span>
                            </span>
                        </span>
                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-100 theme-text-muted transition group-hover:bg-emerald-100 group-hover:text-emerald-700 dark:bg-slate-800 dark:group-hover:bg-emerald-900 dark:group-hover:text-emerald-200" aria-hidden="true">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"></path></svg>
                        </span>
                    </a>
                @empty
                    <div class="solid-panel rounded-[22px] p-8 text-center">
                        <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-slate-100 theme-text-muted dark:bg-slate-800" aria-hidden="true"><svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path stroke-linecap="round" d="m20 20-3.5-3.5"></path></svg></span>
                        <p class="mt-3 text-sm font-black theme-text-main">Siswa tidak ditemukan</p>
                        <p class="mt-1 text-xs leading-5 theme-text-muted">Ubah kata pencarian atau hapus pencarian untuk melihat semua siswa dalam cakupan.</p>
                    </div>
                @endforelse
            </div>

            @if($students->hasPages())
                <div data-kesiswaan-pagination="stable" class="mx-auto min-h-12 w-full max-w-full overflow-hidden pt-1 text-xs [&_nav]:w-full [&_nav]:max-w-full [&_nav>div]:w-full [&_nav>div]:justify-between [&_a]:min-h-11 [&_a]:rounded-2xl [&_a]:border-emerald-200 [&_a]:bg-white [&_a]:px-4 [&_a]:font-black [&_a]:text-emerald-700 dark:[&_a]:border-emerald-800 dark:[&_a]:bg-slate-900 dark:[&_a]:text-emerald-300">
                    {{ $students->links('pagination::simple-tailwind') }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.mobile>
