<x-layouts.mobile title="Kelas Saya">
    <div class="space-y-3">
        <section class="flex items-center justify-between gap-3 px-1 text-left">
            <div class="min-w-0">
                <p class="text-[10px] font-semibold theme-text-muted">Wali kelas · {{ $assignment->academicYear->name }}</p>
                <h1 class="truncate text-lg font-black theme-text-main">{{ $assignment->schoolClass->name }}</h1>
            </div>
            <span class="flex-none rounded-xl bg-green-500/10 px-2.5 py-1.5 text-[10px] font-bold text-green-700 dark:text-green-300">
                {{ $students->total() }} siswa
            </span>
        </section>

        <form method="GET" role="search">
            <label class="sr-only" for="student-search">Cari siswa</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 theme-text-muted"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path stroke-linecap="round" d="m20 20-4-4"></path>
                </svg>
                <input id="student-search" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama siswa" class="theme-input min-h-11 w-full rounded-2xl py-2.5 pl-10 pr-4 text-sm">
            </div>
        </form>

        <section class="solid-panel overflow-hidden rounded-2xl" data-my-class-list>
            <div class="divide-y theme-divide">
                @forelse ($students as $student)
                    <a href="{{ route('attendance.my-class.show', $student) }}"
                        class="flex min-h-16 items-center gap-3 px-3 py-2.5 text-left transition-colors active:bg-green-500/10">
                        <span class="grid h-9 w-9 flex-none place-items-center rounded-xl bg-green-500/10 text-[11px] font-black text-green-700 dark:text-green-300">
                            {{ str($student->nama_lengkap)->substr(0, 2)->upper() }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold theme-text-main">{{ $student->nama_lengkap }}</span>
                            <span class="block truncate text-[10px] theme-text-muted">NISN {{ $student->nisn ?? '-' }}</span>
                        </span>
                        @if ($student->violations_count > 0)
                            <span class="grid min-w-6 flex-none place-items-center rounded-lg bg-amber-500/10 px-1.5 py-1 text-[10px] font-bold text-amber-700 dark:text-amber-300"
                                aria-label="{{ $student->violations_count }} pelanggaran">
                                {{ $student->violations_count }}
                            </span>
                        @endif
                        <svg class="h-4 w-4 flex-none theme-text-muted" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"></path>
                        </svg>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm font-bold theme-text-main">Siswa tidak ditemukan</p>
                        <p class="mt-1 text-xs theme-text-muted">
                            {{ request('search') ? 'Coba gunakan kata kunci lain.' : 'Data siswa kelas ini belum tersedia.' }}
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        @if ($students->hasPages())
            <div class="px-1">{{ $students->links() }}</div>
        @endif
    </div>
</x-layouts.mobile>
