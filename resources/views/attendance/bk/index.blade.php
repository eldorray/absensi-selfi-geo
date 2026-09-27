@php
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Diproses', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
@endphp

<x-layouts.mobile title="Catatan BK" backUrl="{{ route('attendance.dashboard') }}">
    <x-slot:headerAction>
        <x-guru.button :href="route('attendance.bk.create')" icon="plus" class="g-btn--sm">Tambah</x-guru.button>
    </x-slot:headerAction>

    <p class="text-sm text-guru-muted">Catatan bimbingan dan konseling yang Anda tangani.</p>

    @if ($records->isEmpty())
        <x-guru.empty icon="chat" title="Belum ada catatan">
            Buat catatan pertama untuk siswa yang Anda tangani.
            <x-slot:action>
                <x-guru.button :href="route('attendance.bk.create')" icon="plus">Buat Catatan</x-guru.button>
            </x-slot:action>
        </x-guru.empty>
    @else
        <x-guru.list>
            @foreach ($records as $record)
                <x-guru.list-item :href="route('attendance.bk.show', $record)"
                    :icon="$record->record_type === 'violation' ? 'alert' : 'chat'"
                    :tone="$record->record_type === 'violation' ? 'attn' : 'izin'"
                    :title="$record->student->nama_lengkap"
                    :desc="$record->occurred_at->locale('id')->isoFormat('D MMM Y, HH.mm').' · '.($record->category?->name ?? $record->custom_topic)">
                    <x-slot:end>
                        <x-guru.chip>{{ $statusLabels[$record->status] ?? $record->status }}</x-guru.chip>
                    </x-slot:end>
                </x-guru.list-item>
            @endforeach
        </x-guru.list>
    @endif

    @if ($records->hasPages())
        <div class="g-pager">{{ $records->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
