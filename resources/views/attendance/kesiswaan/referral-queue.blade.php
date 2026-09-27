@php
    $urgencyLabels = ['normal' => 'Biasa', 'important' => 'Penting', 'urgent' => 'Mendesak'];
    $urgencyTones = ['normal' => 'neutral', 'important' => 'pending', 'urgent' => 'attn'];
@endphp

<x-layouts.mobile title="Antrean Rujukan" backUrl="{{ route('attendance.dashboard') }}">
    <p class="text-sm text-guru-muted">Rujukan jenjang Anda, diurutkan berdasarkan urgensi dan waktu.</p>

    @if ($referrals->isEmpty())
        <x-guru.empty icon="inbox" title="Antrean kosong">Belum ada rujukan baru atau rujukan yang Anda tangani.</x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($referrals as $referral)
                <x-guru.list-item :href="route('attendance.kesiswaan.referrals.show', $referral)" icon="inbox"
                    :tone="$referral->urgency->value === 'urgent' ? 'attn' : 'izin'"
                    :title="$referral->student->nama_lengkap"
                    :desc="($referral->student->schoolClass?->name ?? strtoupper($referral->school_level)).' · '.$referral->reason">
                    <span class="g-list__desc">{{ $referral->status->value === 'new' ? 'Belum ditangani' : ($referral->counselor?->name ?? 'Dalam penanganan') }} · {{ $referral->observed_at?->locale('id')->isoFormat('D MMM Y') }}</span>
                    <x-slot:end>
                        <x-guru.chip :tone="$urgencyTones[$referral->urgency->value] ?? 'neutral'">{{ $urgencyLabels[$referral->urgency->value] ?? $referral->urgency->value }}</x-guru.chip>
                    </x-slot:end>
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($referrals->hasPages())
        <div class="g-pager">{{ $referrals->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
