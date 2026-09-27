<x-layouts.mobile title="Kelas Saya" activeTab="kelas">
    <x-guru.card as="div">
        <span class="g-card__caps">Kelas wali · {{ $assignment->academicYear->name }}</span>
        <p class="g-card__title">{{ $assignment->schoolClass->name }}</p>
        <div class="flex flex-wrap gap-2">
            <x-guru.chip>{{ $students->total() }} siswa</x-guru.chip>
        </div>
    </x-guru.card>

    <form method="GET" role="search" class="g-search">
        <label class="sr-only" for="student-search">Cari siswa</label>
        <x-guru.icon name="search" :size="18" />
        <input id="student-search" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa" class="g-input">
    </form>

    <section class="g-list" data-my-class-list aria-label="Daftar siswa">
        <div class="divide-y divide-guru-divider">
            @forelse ($students as $student)
                <a href="{{ route('attendance.my-class.show', $student) }}" class="g-list__item">
                    <span class="g-avatar g-avatar--sm">{{ str($student->nama_lengkap)->substr(0, 2)->upper() }}</span>
                    <span class="g-list__body">
                        <span class="g-list__title truncate">{{ $student->nama_lengkap }}</span>
                        <span class="g-list__desc">NISN {{ $student->nisn ?? '-' }}</span>
                    </span>
                    @if ($student->violations_count > 0)
                        <x-guru.chip tone="attn" aria-label="{{ $student->violations_count }} pelanggaran">{{ $student->violations_count }}</x-guru.chip>
                    @endif
                    <x-guru.icon name="chevron" :size="18" class="g-list__chev" />
                </a>
            @empty
                <div class="g-empty flex flex-col">
                    <span class="g-empty__icon"><x-guru.icon name="users" :size="24" /></span>
                    <h2>Siswa tidak ditemukan</h2>
                    <p>{{ request('search') ? 'Coba gunakan kata kunci lain.' : 'Data siswa kelas ini belum tersedia.' }}</p>
                </div>
            @endforelse
        </div>
    </section>

    @if ($students->hasPages())
        <div class="g-pager">{{ $students->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
