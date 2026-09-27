@php
    $statusLabels = ['new' => 'Baru', 'in_handling' => 'Ditangani', 'completed' => 'Selesai', 'rejected' => 'Ditolak'];
    $statusTones = ['new' => 'pending', 'in_handling' => 'izin', 'completed' => 'ok', 'rejected' => 'attn'];
@endphp

<x-layouts.mobile title="Rujukan Saya" backUrl="{{ route('attendance.dashboard') }}">
    <p class="text-sm text-guru-muted">Pantau rujukan yang Anda kirim ke guru BK.</p>

    @if ($referrals->isEmpty())
        <x-guru.empty icon="send" title="Belum ada rujukan">Buat rujukan dari profil siswa di menu Kelas.</x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($referrals as $referral)
                <x-guru.list-item :href="route('attendance.kesiswaan.referrals.show', $referral)" icon="send" tone="attn"
                    :title="$referral->student->nama_lengkap"
                    :desc="($referral->student->schoolClass?->name ?? strtoupper($referral->school_level)).' · '.$referral->reason">
                    <span class="g-list__desc">{{ $referral->counselor?->name ?? 'Belum ditangani' }} · {{ $referral->observed_at?->locale('id')->isoFormat('D MMM Y') }}</span>
                    <x-slot:end>
                        <x-guru.chip :tone="$statusTones[$referral->status->value] ?? 'neutral'">{{ $statusLabels[$referral->status->value] ?? $referral->status->value }}</x-guru.chip>
                    </x-slot:end>
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($referrals->hasPages())
        <div class="g-pager">{{ $referrals->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
