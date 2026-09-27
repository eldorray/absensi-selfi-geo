@php
    $toneFor = fn ($leave) => match ($leave->status) { 'approved' => 'ok', 'rejected' => 'attn', default => 'pending' };
    $iconFor = fn ($leave) => match ($leave->type) { 'sakit' => 'medical', 'cuti' => 'briefcase', default => 'doc' };
@endphp

<x-layouts.mobile title="Perizinan Saya" backUrl="{{ route('attendance.dashboard') }}" activeTab="izin">
    <x-slot:headerAction>
        <x-guru.button :href="route('attendance.leaves.create')" icon="plus" class="g-btn--sm">Ajukan</x-guru.button>
    </x-slot:headerAction>

    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif

    @if ($leaves->isEmpty())
        <x-guru.empty icon="doc" title="Belum ada pengajuan">
            Izin, cuti, atau sakit yang Anda ajukan tampil di sini beserta statusnya.
            <x-slot:action>
                <x-guru.button :href="route('attendance.leaves.create')" icon="plus">Ajukan Izin</x-guru.button>
            </x-slot:action>
        </x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($leaves as $leave)
                <x-guru.list-item :href="route('attendance.leaves.show', $leave)" :icon="$iconFor($leave)" tone="izin"
                    :title="$leave->type_label.' · '.$leave->duration.' hari'" :desc="$leave->reason">
                    <span class="g-list__desc g-num">
                        {{ $leave->start_date->locale('id')->isoFormat('D MMM Y') }}@if (! $leave->start_date->equalTo($leave->end_date)) – {{ $leave->end_date->locale('id')->isoFormat('D MMM Y') }}@endif
                    </span>
                    <x-slot:end>
                        <x-guru.chip :tone="$toneFor($leave)">{{ $leave->status_label }}</x-guru.chip>
                    </x-slot:end>
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($leaves->hasPages())
        <div class="g-pager">{{ $leaves->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
