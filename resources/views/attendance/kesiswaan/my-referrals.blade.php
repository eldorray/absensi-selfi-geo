<x-layouts.mobile title="Rujukan Saya" backUrl="{{ route('attendance.dashboard') }}">
    <div class="space-y-4 p-4 pb-8">
        <header><p class="text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Wali kelas</p><h1 class="mt-1 text-2xl font-black theme-text-main">Rujukan Saya</h1><p class="mt-1 text-xs theme-text-muted">Pantau rujukan yang Anda kirim ke Guru BK.</p></header>
        @forelse($referrals as $referral)
            <a href="{{ route('attendance.kesiswaan.referrals.show', $referral) }}" class="solid-panel block rounded-[20px] p-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                <div class="flex items-start justify-between gap-3"><div><h2 class="text-sm font-black theme-text-main">{{ $referral->student->nama_lengkap }}</h2><p class="mt-1 text-[11px] theme-text-muted">{{ $referral->student->schoolClass?->name ?? strtoupper($referral->school_level) }} · {{ $referral->reason }}</p></div><span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold theme-text-muted dark:bg-slate-800">{{ ucfirst(str_replace('_', ' ', $referral->status->value)) }}</span></div>
                <p class="mt-3 text-[11px] theme-text-muted">{{ $referral->counselor?->name ?? 'Belum ditangani' }} · {{ $referral->observed_at?->format('d M Y') }}</p>
            </a>
        @empty
            <div class="solid-panel rounded-[20px] p-7 text-center"><p class="text-sm font-black theme-text-main">Belum ada rujukan</p><p class="mt-1 text-xs theme-text-muted">Buat rujukan dari profil siswa di menu Kelas Saya.</p></div>
        @endforelse
        {{ $referrals->links() }}
    </div>
</x-layouts.mobile>
