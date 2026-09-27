<x-layouts.mobile title="Profil Siswa" backUrl="{{ route('attendance.my-class.index') }}" activeTab="kelas">
    <x-guru.card>
        <span class="g-card__caps">{{ $assignment->schoolClass->name }}</span>
        <h2 class="g-card__title">{{ $student->nama_lengkap }}</h2>
        <dl class="g-dl">
            <div><dt>NISN</dt><dd>{{ $student->nisn ?? '-' }}</dd></div>
            <div><dt>Status</dt><dd>{{ $student->status }}</dd></div>
        </dl>
        <x-guru.button :href="route('attendance.kesiswaan.referrals.create', $student)" icon="send" class="g-btn--block">Buat rujukan ke Guru BK</x-guru.button>
    </x-guru.card>

    <section class="flex flex-col gap-2.5" aria-labelledby="judul-pelanggaran">
        <h2 id="judul-pelanggaran" class="g-h2">Ringkasan pelanggaran</h2>
        @if ($violations->isEmpty())
            <x-guru.empty icon="check" title="Tidak ada pelanggaran">Catatan pelanggaran siswa ini dari guru BK akan tampil di sini.</x-guru.empty>
        @else
            <x-guru.list>
                @foreach ($violations as $record)
                    <div class="g-list__item">
                        <span class="g-list__icon g-list__icon--attn"><x-guru.icon name="alert" /></span>
                        <span class="g-list__body">
                            <span class="g-list__title">{{ $record->category?->name ?? $record->custom_topic ?? 'Pelanggaran' }}</span>
                            <span class="g-list__desc">{{ $record->occurred_at->locale('id')->isoFormat('D MMM Y') }} · Status: {{ $record->status }}</span>
                        </span>
                    </div>
                @endforeach
            </x-guru.list>
        @endif
    </section>
</x-layouts.mobile>
