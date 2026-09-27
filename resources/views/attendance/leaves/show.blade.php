@php
    $tone = match ($leave->status) { 'approved' => 'ok', 'rejected' => 'attn', default => 'pending' };
@endphp

<x-layouts.mobile title="Detail Perizinan" backUrl="{{ route('attendance.leaves.index') }}" activeTab="izin">
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
    </x-guru.card>

    <x-guru.card as="div">
        <h2 class="g-h2">Alasan</h2>
        <p class="whitespace-pre-line">{{ $leave->reason }}</p>
    </x-guru.card>

    @if ($leave->attachment)
        <x-guru.card as="div">
            <h2 class="g-h2">Lampiran</h2>
            <a href="{{ $leave->attachment_url }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-2xl bg-guru-surface-2">
                <img src="{{ $leave->attachment_url }}" alt="Lampiran pengajuan" class="max-h-72 w-full object-contain">
            </a>
        </x-guru.card>
    @endif

    @unless ($leave->isPending())
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
    @endunless
</x-layouts.mobile>
