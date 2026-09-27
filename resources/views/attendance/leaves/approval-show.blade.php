@php
    $tone = match ($leave->status) { 'approved' => 'ok', 'rejected' => 'attn', default => 'pending' };
@endphp

<x-layouts.mobile title="Detail Pengajuan" backUrl="{{ route('approval.leaves.index') }}">
    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif
    @if (session('error'))
        <x-guru.notice tone="error" role="alert">{{ session('error') }}</x-guru.notice>
    @endif

    <x-guru.card as="div">
        <div class="flex items-center gap-3">
            <span class="g-avatar">{{ $leave->user->initials() }}</span>
            <div class="min-w-0">
                <p class="truncate text-[17px] font-bold">{{ $leave->user->name }}</p>
                <p class="truncate text-xs text-guru-muted">{{ $leave->user->role?->name ?? 'Pegawai' }} · {{ $leave->user->office?->name ?? '-' }}</p>
            </div>
        </div>
    </x-guru.card>

    <x-guru.card>
        <div class="flex items-center justify-between gap-3">
            <x-guru.chip tone="izin">{{ $leave->type_label }}</x-guru.chip>
            <x-guru.chip :tone="$tone">{{ $leave->status_label }}</x-guru.chip>
        </div>
        <div class="flex flex-col gap-1">
            <span class="g-card__caps">Tanggal</span>
            <p class="g-card__title">
                {{ $leave->start_date->locale('id')->isoFormat('D MMM Y') }}@if (! $leave->start_date->equalTo($leave->end_date)) – {{ $leave->end_date->locale('id')->isoFormat('D MMM Y') }}@endif
            </p>
            <p class="text-sm text-guru-muted">{{ $leave->duration }} hari · diajukan {{ $leave->created_at->locale('id')->isoFormat('D MMM Y, HH.mm') }}</p>
        </div>
        <div class="flex flex-col gap-1">
            <span class="g-card__caps">Alasan</span>
            <p class="whitespace-pre-line">{{ $leave->reason }}</p>
        </div>
    </x-guru.card>

    @if ($leave->attachment)
        <x-guru.card as="div">
            <h2 class="g-h2">Lampiran</h2>
            <a href="{{ $leave->attachment_url }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-2xl bg-guru-surface-2">
                <img src="{{ $leave->attachment_url }}" alt="Lampiran pengajuan" class="max-h-72 w-full object-contain">
            </a>
        </x-guru.card>
    @endif

    @if ($leave->isPending())
        <form action="{{ route('approval.leaves.approve', $leave) }}" method="POST">
            @csrf
            <x-guru.button type="submit" icon="check" class="g-btn--block" onclick="return confirm('Setujui pengajuan izin ini?')">Setujui Pengajuan</x-guru.button>
        </form>

        <form action="{{ route('approval.leaves.reject', $leave) }}" method="POST" class="g-card">
            @csrf
            <x-guru.field label="Tolak dengan alasan" for="rejection_reason" error="rejection_reason">
                <textarea name="rejection_reason" id="rejection_reason" rows="2" class="g-input" placeholder="Tuliskan alasan penolakan"
                    @error('rejection_reason') aria-invalid="true" aria-describedby="rejection_reason-error" @enderror>{{ old('rejection_reason') }}</textarea>
            </x-guru.field>
            <x-guru.button type="submit" variant="danger" icon="x" class="g-btn--block" onclick="return confirm('Tolak pengajuan izin ini?')">Tolak Pengajuan</x-guru.button>
        </form>
    @else
        <x-guru.card as="div">
            <h2 class="g-h2">{{ $leave->isApproved() ? 'Disetujui oleh' : 'Ditolak oleh' }}</h2>
            <div class="flex items-center gap-3">
                <span class="g-avatar g-avatar--sm">{{ $leave->approver?->initials() ?? '?' }}</span>
                <div class="min-w-0">
                    <p class="truncate font-bold">{{ $leave->approver?->name ?? '-' }}</p>
                    <p class="text-xs text-guru-muted">{{ $leave->approved_at?->locale('id')->isoFormat('D MMM Y, HH.mm') ?? '-' }}</p>
                </div>
            </div>
            @if ($leave->isRejected() && $leave->rejection_reason)
                <x-guru.notice tone="error" title="Alasan penolakan">{{ $leave->rejection_reason }}</x-guru.notice>
            @endif
        </x-guru.card>
    @endif
</x-layouts.mobile>
