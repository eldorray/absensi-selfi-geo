<x-layouts.mobile title="Kesiswaan" backUrl="{{ route('attendance.dashboard') }}">
    <div class="flex flex-col gap-5" data-kesiswaan-list="mobile">
        <x-guru.card as="section" data-kesiswaan-hero="directory">
            <div class="flex items-start gap-3">
                <span class="g-list__icon size-11 rounded-[14px]"><x-guru.icon name="school" :size="22" /></span>
                <div class="min-w-0">
                    <span class="g-card__caps">Petugas kesiswaan</span>
                    <h2 class="g-card__title">Direktori siswa</h2>
                    <p class="mt-1 text-sm text-guru-muted">Buka profil siswa dan pantau ringkasan penanganan sesuai kewenangan Anda.</p>
                </div>
            </div>
            <dl class="g-dl">
                <div><dt>Cakupan siswa</dt><dd>{{ strtoupper(auth()->user()->office?->school_level ?? '-') }}</dd></div>
                <div><dt>Hasil ditemukan</dt><dd>{{ $students->total() }} siswa</dd></div>
            </dl>
        </x-guru.card>

        <form data-kesiswaan-search="students" method="GET" action="{{ route('attendance.kesiswaan.index') }}" class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <div class="g-search min-w-0 flex-1">
                    <label for="student-search" class="sr-only">Cari nama, NISN, atau NIK</label>
                    <x-guru.icon name="search" :size="18" />
                    <input id="student-search" name="search" value="{{ request('search') }}" placeholder="Nama, NISN, atau NIK" class="g-input">
                </div>
                <button type="submit" class="g-btn g-btn--primary size-[52px] flex-none p-0" aria-label="Cari siswa"><x-guru.icon name="search" /></button>
            </div>
            @if (request('search'))
                <div class="flex items-center justify-between gap-3 text-xs">
                    <p class="truncate text-guru-muted">Hasil untuk “{{ request('search') }}”</p>
                    <a href="{{ route('attendance.kesiswaan.index') }}" class="flex min-h-11 items-center font-bold">Hapus pencarian</a>
                </div>
            @endif
        </form>

        <section id="student-directory" class="flex scroll-mt-4 flex-col gap-2.5" aria-labelledby="judul-direktori">
            <div class="flex items-end justify-between gap-3">
                <h2 id="judul-direktori" class="g-h2">Siswa dalam cakupan</h2>
                <span class="g-num text-xs font-bold text-guru-muted">{{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }}</span>
            </div>

            @if ($students->isEmpty())
                <x-guru.empty icon="search" title="Siswa tidak ditemukan">Ubah kata pencarian atau hapus pencarian untuk melihat semua siswa dalam cakupan.</x-guru.empty>
            @else
                <x-guru.list>
                    @foreach ($students as $student)
                        @php
                            $initials = collect(explode(' ', $student->nama_lengkap))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
                        @endphp
                        <a href="{{ route('attendance.kesiswaan.show', $student) }}" class="g-list__item">
                            <span class="g-avatar g-avatar--sm" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                            <span class="g-list__body">
                                <span class="g-list__title truncate">{{ $student->nama_lengkap }}</span>
                                <span class="g-list__desc truncate">{{ $student->schoolClass?->name ?? 'Belum memiliki kelas' }} · NISN {{ $student->nisn ?: '-' }}</span>
                            </span>
                            <x-guru.icon name="chevron" :size="18" class="g-list__chev" />
                        </a>
                    @endforeach
                </x-guru.list>
            @endif

            @if ($students->hasPages())
                <div data-kesiswaan-pagination="stable" class="g-pager">
                    {{ $students->links('pagination::simple-tailwind') }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.mobile>
