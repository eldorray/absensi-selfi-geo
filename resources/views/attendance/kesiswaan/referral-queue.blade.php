<x-layouts.mobile title="Antrean Rujukan" backUrl="{{ route('attendance.dashboard') }}">
    <div class="space-y-4 p-4 pb-8">
        <header><p class="text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Guru BK</p><h1 class="mt-1 text-2xl font-black theme-text-main">Antrean Rujukan</h1><p class="mt-1 text-xs theme-text-muted">Rujukan jenjang Anda, diurutkan berdasarkan urgensi dan waktu.</p></header>
        @forelse($referrals as $referral)
            @php $isUrgent = $referral->urgency->value === 'urgent'; @endphp
            <a href="{{ route('attendance.kesiswaan.referrals.show', $referral) }}" class="solid-panel block rounded-[20px] border-l-4 p-4 {{ $isUrgent ? 'border-l-red-600' : 'border-l-emerald-600' }} focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                <div class="flex items-start justify-between gap-3"><div><h2 class="text-sm font-black theme-text-main">{{ $referral->student->nama_lengkap }}</h2><p class="mt-1 text-[11px] theme-text-muted">{{ $referral->student->schoolClass?->name ?? strtoupper($referral->school_level) }} · {{ $referral->reason }}</p></div><span class="rounded-lg px-2 py-1 text-[9px] font-black {{ $isUrgent ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' }}">{{ strtoupper($referral->urgency->value) }}</span></div>
                <p class="mt-3 text-[11px] theme-text-muted">{{ $referral->status->value === 'new' ? 'Belum ditangani' : ($referral->counselor?->name ?? 'Dalam penanganan') }} · {{ $referral->observed_at?->format('d M Y') }}</p>
            </a>
        @empty
            <div class="solid-panel rounded-[20px] p-7 text-center"><p class="text-sm font-black theme-text-main">Antrean kosong</p><p class="mt-1 text-xs theme-text-muted">Belum ada rujukan baru atau rujukan yang Anda tangani.</p></div>
        @endforelse
        {{ $referrals->links() }}
    </div>
</x-layouts.mobile>
