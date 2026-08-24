@php
    $assignment = $student->schoolClass?->homeroomAssignments?->first();
    $initials = collect(explode(' ', $student->nama_lengkap))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $typeLabels = ['violation' => 'Pelanggaran', 'counseling' => 'Konseling'];
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Dalam penanganan', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
    $referralStatusLabels = ['new' => 'Baru', 'in_handling' => 'Ditangani', 'completed' => 'Selesai', 'rejected' => 'Ditolak'];
@endphp

<x-layouts.mobile title="Profil Siswa" backUrl="{{ route('attendance.kesiswaan.index') }}">
    <div class="space-y-5 p-4 pb-8" data-kesiswaan-design="profile-centered">
        <section data-profile-hero="student" class="overflow-hidden rounded-[28px] border border-emerald-200/70 bg-emerald-50 dark:border-emerald-800/60 dark:bg-emerald-950/45">
            <div class="h-2 bg-emerald-600 dark:bg-emerald-500"></div>
            <div class="p-5">
                <div class="flex items-center gap-4">
                    <div class="grid size-16 shrink-0 place-items-center rounded-[20px] bg-emerald-200 text-lg font-black text-emerald-900 dark:bg-emerald-800 dark:text-emerald-50" aria-hidden="true">
                        {{ $initials ?: 'S' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-[0.14em] text-emerald-700 dark:text-emerald-300">{{ $student->status ?? 'Siswa' }}</span>
                            <span class="rounded-lg bg-white/80 px-2 py-1 text-[9px] font-black text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100">{{ strtoupper($student->school_level) }}</span>
                        </div>
                        <h1 class="mt-2 text-xl font-black leading-tight theme-text-main">{{ $student->nama_lengkap }}</h1>
                        <p class="mt-1 text-xs theme-text-muted">NISN {{ $student->nisn ?: 'belum tersedia' }}</p>
                    </div>
                </div>

                <div data-profile-summary="student" class="mt-5 grid grid-cols-3 gap-2">
                    <div class="rounded-2xl bg-white/75 p-3 dark:bg-white/5"><span class="block text-[9px] font-bold uppercase theme-text-muted">Kelas</span><strong class="mt-1 block truncate text-xs theme-text-main">{{ $student->schoolClass?->name ?? '-' }}</strong></div>
                    <div class="rounded-2xl bg-white/75 p-3 dark:bg-white/5"><span class="block text-[9px] font-bold uppercase theme-text-muted">BK aktif</span><strong class="mt-1 block text-base font-black text-emerald-800 dark:text-emerald-200">{{ $summary['active_count'] }}</strong></div>
                    <div class="rounded-2xl bg-white/75 p-3 dark:bg-white/5"><span class="block text-[9px] font-bold uppercase theme-text-muted">Rujukan</span><strong class="mt-1 block text-base font-black theme-text-main">{{ $referrals->total() }}</strong></div>
                </div>
            </div>
        </section>

        <section class="solid-panel rounded-[24px] p-5">
            <div class="flex items-center gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-[14px] bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200" aria-hidden="true">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-6 4h6" /></svg>
                </span>
                <div><h2 class="text-sm font-black theme-text-main">Informasi akademik</h2><p class="mt-0.5 text-[11px] theme-text-muted">Penempatan siswa pada tahun ajaran aktif.</p></div>
            </div>
            <dl class="mt-4 divide-y theme-border text-xs">
                <div class="flex items-center justify-between gap-4 py-3"><dt class="theme-text-muted">Kelas aktif</dt><dd class="text-right font-bold theme-text-main">{{ $student->schoolClass?->name ?? '-' }}</dd></div>
                <div class="flex items-center justify-between gap-4 py-3"><dt class="theme-text-muted">Wali kelas</dt><dd class="max-w-[62%] text-right font-bold theme-text-main">{{ $assignment?->teacher?->name ?? '-' }}</dd></div>
                <div class="flex items-center justify-between gap-4 py-3"><dt class="theme-text-muted">Tahun ajaran</dt><dd class="text-right font-bold theme-text-main">{{ $assignment?->academicYear?->name ?? '-' }}</dd></div>
            </dl>
        </section>

        <section class="solid-panel rounded-[24px] p-5">
            <div class="flex items-center gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-[14px] bg-slate-100 theme-text-muted dark:bg-slate-800" aria-hidden="true">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 0 0-16 0m8-11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                </span>
                <div><h2 class="text-sm font-black theme-text-main">Informasi pribadi</h2><p class="mt-0.5 text-[11px] theme-text-muted">Data identitas yang tersedia.</p></div>
            </div>
            <dl class="mt-4 divide-y theme-border text-xs">
                <div class="flex items-start justify-between gap-4 py-3"><dt class="theme-text-muted">NIK</dt><dd class="max-w-[62%] break-all text-right font-bold theme-text-main">{{ $student->nik ?: '-' }}</dd></div>
                <div class="flex items-start justify-between gap-4 py-3"><dt class="theme-text-muted">Tempat, tanggal lahir</dt><dd class="max-w-[62%] text-right font-bold theme-text-main">{{ $student->tempat_lahir ?: '-' }}{{ $student->tanggal_lahir ? ', '.$student->tanggal_lahir->translatedFormat('d F Y') : '' }}</dd></div>
                <div class="flex items-start justify-between gap-4 py-3"><dt class="theme-text-muted">Telepon</dt><dd class="max-w-[62%] text-right font-bold theme-text-main">{{ $student->no_telepon ?: '-' }}</dd></div>
                <div class="flex items-start justify-between gap-4 py-3"><dt class="theme-text-muted">Alamat</dt><dd class="max-w-[62%] text-right font-bold leading-5 theme-text-main">{{ $student->alamat ?: '-' }}</dd></div>
            </dl>
        </section>

        <section class="solid-panel rounded-[24px] p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-[14px] bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200" aria-hidden="true">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h8M8 10h8m-8 4h5m-7 7 3-3h9a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2v3Z" /></svg>
                    </span>
                    <div><h2 class="text-sm font-black theme-text-main">Ringkasan BK</h2><p class="mt-0.5 text-[11px] theme-text-muted">Hanya informasi umum yang aman ditampilkan.</p></div>
                </div>
                <span class="rounded-xl bg-emerald-100 px-3 py-2 text-sm font-black text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">{{ $summary['active_count'] }}</span>
            </div>
            <dl class="mt-4 divide-y theme-border text-xs">
                <div class="flex items-start justify-between gap-4 py-3"><dt class="theme-text-muted">Jenis catatan</dt><dd class="max-w-[62%] text-right font-bold theme-text-main">{{ collect($summary['types'])->map(fn ($type) => $typeLabels[$type] ?? ucfirst($type))->implode(', ') ?: '-' }}</dd></div>
                <div class="flex items-start justify-between gap-4 py-3"><dt class="theme-text-muted">Status penanganan</dt><dd class="max-w-[62%] text-right font-bold theme-text-main">{{ collect($summary['statuses'])->map(fn ($status) => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)))->implode(', ') ?: '-' }}</dd></div>
                <div class="flex items-center justify-between gap-4 py-3"><dt class="theme-text-muted">Perlu tindak lanjut</dt><dd class="font-black {{ $summary['needs_follow_up'] ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ $summary['needs_follow_up'] ? 'Ya' : 'Tidak' }}</dd></div>
            </dl>
        </section>

        @can('create', App\Models\StudentReferral::class)
            <a href="{{ route('attendance.kesiswaan.referrals.create', $student) }}" class="theme-btn-submit flex min-h-12 w-full items-center justify-center rounded-[18px] px-5 py-3 text-sm font-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                Buat rujukan ke Guru BK
            </a>
        @endcan

        <section class="space-y-3">
            <div class="flex items-end justify-between gap-3 px-1"><div><h2 class="text-sm font-black theme-text-main">Riwayat rujukan</h2><p class="mt-1 text-[11px] theme-text-muted">Rujukan yang dapat Anda akses.</p></div><span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">{{ $referrals->total() }}</span></div>
            @forelse($referrals as $referral)
                <a href="{{ route('attendance.kesiswaan.referrals.show', $referral) }}" class="solid-panel block rounded-[20px] p-4 transition hover:border-emerald-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    <div class="flex items-start justify-between gap-3"><h3 class="min-w-0 flex-1 text-sm font-black theme-text-main">{{ $referral->reason }}</h3><span class="shrink-0 rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold theme-text-muted dark:bg-slate-800">{{ $referralStatusLabels[$referral->status->value] ?? ucfirst(str_replace('_', ' ', $referral->status->value)) }}</span></div>
                    <p class="mt-2 text-[11px] theme-text-muted">{{ $referral->observed_at?->translatedFormat('d M Y') }} · {{ $referral->counselor?->name ?? 'Belum ditangani' }}</p>
                </a>
            @empty
                <div class="solid-panel rounded-[20px] p-7 text-center"><p class="text-sm font-black theme-text-main">Belum ada rujukan</p><p class="mt-1 text-xs theme-text-muted">Rujukan siswa akan tampil di bagian ini.</p></div>
            @endforelse
            {{ $referrals->links() }}
        </section>
    </div>
</x-layouts.mobile>
