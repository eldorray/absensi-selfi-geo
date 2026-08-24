@php
    $assignment = $student->schoolClass?->homeroomAssignments?->first();
    $initials = collect(explode(' ', $student->nama_lengkap))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $typeLabels = ['violation' => 'Pelanggaran', 'counseling' => 'Konseling'];
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Dalam penanganan', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
@endphp

<x-layouts.app>
    <div class="space-y-6" data-kesiswaan-design="profile-centered-admin">
        <x-admin.page-header kicker="Kesiswaan" title="Profil Siswa" description="Pusat informasi siswa dan pengawasan rujukan dalam mode hanya-baca.">
            <a href="{{ route('admin.kesiswaan.index') }}" class="admin-button-secondary px-4 py-2.5">Kembali ke daftar</a>
            <a href="{{ route('admin.students.edit', [$student->school_level, $student]) }}" class="admin-button-primary px-4 py-2.5">Buka Data Siswa</a>
        </x-admin.page-header>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.45fr)_minmax(19rem,0.75fr)]">
            <div class="space-y-5">
                <section class="rounded-[26px] border border-emerald-200/70 bg-emerald-50 p-5 md:p-6 dark:border-emerald-800/60 dark:bg-emerald-950/45">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="grid size-16 shrink-0 place-items-center rounded-[20px] bg-emerald-200 text-lg font-black text-emerald-900 dark:bg-emerald-800 dark:text-emerald-50" aria-hidden="true">{{ $initials ?: 'S' }}</div>
                        <div class="min-w-0 flex-1"><p class="text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">{{ $student->status ?? 'Siswa' }}</p><h1 class="mt-1 truncate text-2xl font-black admin-text-main">{{ $student->nama_lengkap }}</h1><p class="mt-1 text-xs admin-text-muted">NISN {{ $student->nisn ?: '-' }} · NIK {{ $student->nik ?: '-' }}</p></div>
                        <span class="w-fit rounded-xl bg-white px-3 py-2 text-xs font-black text-emerald-800 shadow-sm dark:bg-emerald-900 dark:text-emerald-100">{{ $student->schoolClass?->name ?? strtoupper($student->school_level) }}</span>
                    </div>
                </section>

                <section class="admin-glass-panel p-5 md:p-6">
                    <h2 class="text-base font-black admin-text-main">Informasi siswa</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach([
                            ['Kelas aktif', $student->schoolClass?->name ?? '-'],
                            ['Wali kelas', $assignment?->teacher?->name ?? '-'],
                            ['Tahun ajaran', $assignment?->academicYear?->name ?? '-'],
                            ['Jenjang', strtoupper($student->school_level)],
                            ['Telepon', $student->no_telepon ?: '-'],
                            ['Alamat', $student->alamat ?: '-'],
                        ] as [$label, $value])
                            <div class="rounded-2xl border admin-border bg-white/50 p-4 dark:bg-white/5"><p class="admin-label">{{ $label }}</p><p class="mt-1 text-sm font-bold admin-text-main">{{ $value }}</p></div>
                        @endforeach
                    </div>
                </section>

                <section class="admin-glass-panel p-5 md:p-6">
                    <div class="flex items-end justify-between gap-3"><div><h2 class="text-base font-black admin-text-main">Aktivitas rujukan</h2><p class="mt-1 text-xs admin-text-muted">Riwayat yang dapat diawasi administrator.</p></div><span class="text-sm font-black text-emerald-700 dark:text-emerald-300">{{ $referrals->total() }}</span></div>
                    <div class="mt-4 space-y-3">
                        @forelse($referrals as $referral)
                            <a href="{{ route('admin.kesiswaan.referrals.show', $referral) }}" class="block rounded-2xl border admin-border p-4 transition hover:border-emerald-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                <div class="flex items-start justify-between gap-3"><h3 class="text-sm font-black admin-text-main">{{ $referral->reason }}</h3><span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold admin-text-muted dark:bg-slate-800">{{ ucfirst(str_replace('_', ' ', $referral->status->value)) }}</span></div>
                                <p class="mt-2 text-xs admin-text-muted">{{ $referral->observed_at?->format('d M Y') }} · {{ $referral->counselor?->name ?? 'Belum ditangani' }}</p>
                            </a>
                        @empty
                            <div class="rounded-2xl border admin-border p-6 text-center"><p class="font-bold admin-text-main">Belum ada rujukan</p><p class="mt-1 text-xs admin-text-muted">Aktivitas rujukan siswa akan muncul di sini.</p></div>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $referrals->links() }}</div>
                </section>
            </div>

            <aside class="space-y-5">
                <section class="admin-glass-panel p-5">
                    <h2 class="text-base font-black admin-text-main">Ringkasan BK yang aman</h2>
                    <p class="mt-1 text-xs admin-text-muted">Metadata umum tanpa mengubah isi profesional.</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-950/60"><strong class="text-2xl font-black text-emerald-800 dark:text-emerald-200">{{ $summary['active_count'] }}</strong><span class="mt-1 block text-[10px] font-bold text-emerald-700 dark:text-emerald-300">Catatan aktif</span></div>
                        <div class="rounded-2xl bg-amber-50 p-4 dark:bg-amber-950/60"><strong class="text-lg font-black text-amber-800 dark:text-amber-200">{{ $summary['needs_follow_up'] ? 'Ya' : 'Tidak' }}</strong><span class="mt-1 block text-[10px] font-bold text-amber-700 dark:text-amber-300">Perlu tindak lanjut</span></div>
                    </div>
                    <dl class="mt-4 divide-y admin-border text-xs">
                        <div class="flex items-start justify-between gap-4 py-3"><dt class="admin-text-muted">Jenis</dt><dd class="max-w-[65%] text-right font-bold admin-text-main">{{ collect($summary['types'])->map(fn ($type) => $typeLabels[$type] ?? ucfirst($type))->implode(', ') ?: '-' }}</dd></div>
                        <div class="flex items-start justify-between gap-4 py-3"><dt class="admin-text-muted">Status</dt><dd class="max-w-[65%] text-right font-bold admin-text-main">{{ collect($summary['statuses'])->map(fn ($status) => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)))->implode(', ') ?: '-' }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-[22px] border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-800 dark:bg-emerald-950/45">
                    <h2 class="text-sm font-black text-emerald-900 dark:text-emerald-100">Halaman ini hanya-baca</h2>
                    <p class="mt-2 text-xs leading-5 text-emerald-800 dark:text-emerald-200">Perubahan identitas dilakukan melalui Data Siswa. Isi konseling dan catatan profesional tetap mengikuti kebijakan akses BK.</p>
                </section>
            </aside>
        </div>
    </div>
</x-layouts.app>
