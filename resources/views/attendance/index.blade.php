<x-layouts.mobile title="Riwayat Absen" backUrl="{{ route('attendance.dashboard') }}" activeTab="riwayat">
    <x-slot:headerAction>
        <x-guru.chip>{{ $attendances->total() }} hari</x-guru.chip>
    </x-slot:headerAction>

    @forelse ($attendances->getCollection()->groupBy(fn ($attendance) => $attendance->created_at->format('Y-m')) as $month => $days)
        <section class="flex flex-col gap-2.5" aria-labelledby="bulan-{{ $month }}">
            <h2 id="bulan-{{ $month }}" class="g-h2">{{ $days->first()->created_at->locale('id')->isoFormat('MMMM Y') }}</h2>
            <x-guru.list>
                @foreach ($days as $attendance)
                    <div class="g-list__item">
                        <span class="g-history__date">
                            <span>{{ $attendance->created_at->format('d') }}</span>
                            <span>{{ $attendance->created_at->locale('id')->isoFormat('ddd') }}</span>
                        </span>
                        <span class="g-list__body">
                            <span class="g-history__times g-num">
                                <span>Masuk <b>{{ $attendance->created_at->format('H.i') }}</b></span>
                                <span>Pulang <b>{{ $attendance->check_out_at?->format('H.i') ?? '––.––' }}</b></span>
                            </span>
                            <span class="g-list__desc">{{ number_format((float) $attendance->distance_meters, 0, ',', '.') }} m dari titik absen</span>
                            <span class="mt-1.5 flex flex-wrap gap-2">
                                <a href="{{ $attendance->image_url }}" target="_blank" rel="noopener" class="g-chip min-h-11 px-3"><x-guru.icon name="image" :size="14" />Foto masuk</a>
                                @if ($attendance->check_out_image_url)
                                    <a href="{{ $attendance->check_out_image_url }}" target="_blank" rel="noopener" class="g-chip min-h-11 px-3"><x-guru.icon name="image" :size="14" />Foto pulang</a>
                                @endif
                            </span>
                        </span>
                        <x-guru.chip :tone="$attendance->status->value === 'late' ? 'late' : 'ok'">{{ $attendance->status->value === 'late' ? 'Terlambat' : 'Tepat waktu' }}</x-guru.chip>
                    </div>
                @endforeach
            </x-guru.list>
        </section>
    @empty
        <x-guru.empty icon="calendar" title="Belum ada riwayat">
            Jam masuk dan pulang Anda tercatat di sini setelah absen pertama.
            <x-slot:action>
                <x-guru.button :href="route('attendance.dashboard')" icon="home">Ke Beranda</x-guru.button>
            </x-slot:action>
        </x-guru.empty>
    @endforelse

    @if ($attendances->hasPages())
        <div class="g-pager">{{ $attendances->links('pagination::simple-tailwind') }}</div>
    @endif
</x-layouts.mobile>
