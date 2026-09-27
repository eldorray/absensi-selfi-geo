<x-layouts.app title="Kesiswaan">
    <div class="space-y-6" data-kesiswaan-list="admin">
        <x-admin.page-header kicker="Kesiswaan" title="Pusat Profil Siswa" description="Cari siswa lintas jenjang dan buka profil hanya-baca untuk pengawasan rujukan."/>

        <form method="GET" action="{{ route('admin.kesiswaan.index') }}" class="admin-glass-panel grid gap-4 p-5 md:grid-cols-[minmax(0,1.5fr)_minmax(10rem,0.55fr)_minmax(12rem,0.7fr)_auto] md:items-end">
            <div><label for="kesiswaan-search" class="admin-label">Pencarian siswa</label><input id="kesiswaan-search" name="search" value="{{ request('search') }}" placeholder="Nama, NISN, NIK, atau kelas" class="admin-field mt-2 min-h-11 px-4 py-2.5"></div>
            <div><label for="kesiswaan-level" class="admin-label">Jenjang</label><select id="kesiswaan-level" name="school_level" class="admin-field mt-2 min-h-11 px-3 py-2.5"><option value="">Semua jenjang</option><option value="mi" @selected(request('school_level') === 'mi')>MI</option><option value="smp" @selected(request('school_level') === 'smp')>SMP</option></select></div>
            <div><label for="kesiswaan-class" class="admin-label">Kelas</label><select id="kesiswaan-class" name="school_class_id" class="admin-field mt-2 min-h-11 px-3 py-2.5"><option value="">Semua kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string) request('school_class_id') === (string) $class->id)>{{ strtoupper($class->school_level) }} · {{ $class->name }}</option>@endforeach</select></div>
            <button type="submit" class="admin-button-primary min-h-11 px-5 py-2.5">Terapkan</button>
        </form>

        <section class="admin-glass-panel overflow-hidden" aria-labelledby="judul-daftar-siswa">
            <div class="admin-panel-header">
                <div>
                    <h2 id="judul-daftar-siswa" class="admin-panel-title">Daftar siswa</h2>
                    <p class="admin-muted mt-0.5 text-xs">{{ $students->total() }} siswa sesuai filter.</p>
                </div>
            </div>
            <ul class="divide-y admin-border">
                @forelse($students as $student)
                    @php $initials = collect(explode(' ', $student->nama_lengkap))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode(''); @endphp
                    <li>
                        <a href="{{ route('admin.kesiswaan.show', $student) }}" class="admin-row-link admin-list-row flex min-h-18 items-center gap-4 px-6 py-3.5">
                            <span class="admin-avatar" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                            <span class="min-w-0 flex-1">
                                <strong class="block truncate text-sm">{{ $student->nama_lengkap }}</strong>
                                <span class="admin-muted mt-0.5 block truncate text-xs tabular-nums">NISN {{ $student->nisn ?: '—' }} · NIK {{ $student->nik ?: '—' }}</span>
                            </span>
                            <span class="admin-chip hidden sm:inline-flex">{{ strtoupper($student->school_level) }} · {{ $student->schoolClass?->name ?? 'Tanpa kelas' }}</span>
                            <x-admin.icon name="chevron-right" size="18" class="admin-muted shrink-0" />
                        </a>
                    </li>
                @empty
                    <li><x-admin.empty-state icon="users" title="Siswa tidak ditemukan" hint="Ubah pencarian atau filter untuk melihat data lain." /></li>
                @endforelse
            </ul>
        </section>

        {{ $students->links() }}
    </div>
</x-layouts.app>
