@php
    $toneFor = fn ($leave) => match ($leave->status) { 'approved' => 'ok', 'rejected' => 'attn', default => 'pending' };
    $current = (string) request('status', '');
@endphp

<x-layouts.mobile title="Persetujuan Izin" backUrl="{{ route('attendance.dashboard') }}">
    @if ($pendingCount > 0)
        <x-slot:headerAction>
            <x-guru.chip tone="pending">{{ $pendingCount }} menunggu</x-guru.chip>
        </x-slot:headerAction>
    @endif

    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    <nav aria-label="Filter status" class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
        @foreach (['' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
            <a href="{{ route('approval.leaves.index', $value !== '' ? ['status' => $value] : []) }}"
                @class(['g-chip min-h-11 px-4', 'g-chip--ok' => $current === $value])
                @if ($current === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($leaves->isEmpty())
        <x-guru.empty icon="clipboard" title="Tidak ada pengajuan">Belum ada pengajuan izin untuk filter ini.</x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($leaves as $leave)
                <a href="{{ route('approval.leaves.show', $leave) }}" class="g-list__item">
                    <span class="g-avatar g-avatar--sm">{{ $leave->user->initials() }}</span>
                    <span class="g-list__body">
                        <span class="g-list__title truncate">{{ $leave->user->name }}</span>
                        <span class="g-list__desc">{{ $leave->type_label }} · {{ $leave->duration }} hari · {{ $leave->start_date->locale('id')->isoFormat('D MMM Y') }}</span>
                    </span>
                    <x-guru.chip :tone="$toneFor($leave)">{{ $leave->status_label }}</x-guru.chip>
                    <x-guru.icon name="chevron" :size="18" class="g-list__chev" />
                </a>
            @endforeach
        </x-guru.list>
    @endif

    @if ($leaves->hasPages())
        <div class="g-pager">{{ $leaves->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
