@php
    $assignment = $student->schoolClass?->homeroomAssignments?->first();
    $initials = collect(explode(' ', $student->nama_lengkap))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $typeLabels = ['violation' => 'Pelanggaran', 'counseling' => 'Konseling'];
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Dalam penanganan', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
    $referralStatusLabels = ['new' => 'Baru', 'in_handling' => 'Ditangani', 'completed' => 'Selesai', 'rejected' => 'Ditolak'];
@endphp

<x-layouts.mobile title="Profil Siswa" backUrl="{{ route('attendance.kesiswaan.index') }}">
    <div class="flex flex-col gap-5" data-kesiswaan-design="profile-centered">
        <x-guru.card as="section" data-profile-hero="student">
            <div class="flex items-center gap-4">
                <span class="g-avatar size-16 rounded-[20px] text-lg" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-guru.chip tone="ok">{{ $student->status ?? 'Siswa' }}</x-guru.chip>
                        <x-guru.chip>{{ strtoupper($student->school_level) }}</x-guru.chip>
                    </div>
                    <h2 class="g-card__title mt-2">{{ $student->nama_lengkap }}</h2>
                    <p class="text-xs text-guru-muted">NISN {{ $student->nisn ?: 'belum tersedia' }}</p>
                </div>
            </div>
            <div data-profile-summary="student" class="g-stats">
                <div class="g-stat"><span class="g-stat__value text-[22px]">{{ $student->schoolClass?->name ?? '-' }}</span><span class="g-stat__label">Kelas</span></div>
                <x-guru.stat :value="$summary['active_count']" label="BK aktif" tone="primary" />
                <x-guru.stat :value="$referrals->total()" label="Rujukan" />
            </div>
        </x-guru.card>

        <x-guru.card as="section" aria-labelledby="judul-akademik">
            <h2 id="judul-akademik" class="g-h2">Informasi akademik</h2>
            <dl class="g-dl">
                <div><dt>Kelas aktif</dt><dd>{{ $student->schoolClass?->name ?? '-' }}</dd></div>
                <div><dt>Wali kelas</dt><dd>{{ $assignment?->teacher?->name ?? '-' }}</dd></div>
                <div><dt>Tahun ajaran</dt><dd>{{ $assignment?->academicYear?->name ?? '-' }}</dd></div>
            </dl>
        </x-guru.card>

        <x-guru.card as="section" aria-labelledby="judul-pribadi">
            <h2 id="judul-pribadi" class="g-h2">Informasi pribadi</h2>
            <dl class="g-dl">
                <div><dt>NIK</dt><dd class="break-all">{{ $student->nik ?: '-' }}</dd></div>
                <div><dt>Tempat, tanggal lahir</dt><dd>{{ $student->tempat_lahir ?: '-' }}{{ $student->tanggal_lahir ? ', '.$student->tanggal_lahir->locale('id')->isoFormat('D MMMM Y') : '' }}</dd></div>
                <div><dt>Telepon</dt><dd>{{ $student->no_telepon ?: '-' }}</dd></div>
                <div><dt>Alamat</dt><dd>{{ $student->alamat ?: '-' }}</dd></div>
            </dl>
        </x-guru.card>

        <x-guru.card as="section" aria-labelledby="judul-ringkasan-bk">
            <div class="flex items-center justify-between gap-3">
                <h2 id="judul-ringkasan-bk" class="g-h2">Ringkasan BK</h2>
                <x-guru.chip tone="ok">{{ $summary['active_count'] }} aktif</x-guru.chip>
            </div>
            <p class="text-xs text-guru-muted">Hanya informasi umum yang aman ditampilkan.</p>
            <dl class="g-dl">
                <div><dt>Jenis catatan</dt><dd>{{ collect($summary['types'])->map(fn ($type) => $typeLabels[$type] ?? ucfirst($type))->implode(', ') ?: '-' }}</dd></div>
                <div><dt>Status penanganan</dt><dd>{{ collect($summary['statuses'])->map(fn ($status) => $statusLabels[$status] ?? $status)->implode(', ') ?: '-' }}</dd></div>
                <div><dt>Perlu tindak lanjut</dt><dd @class(['text-guru-late' => $summary['needs_follow_up'], 'text-guru-primary' => ! $summary['needs_follow_up']])>{{ $summary['needs_follow_up'] ? 'Ya' : 'Tidak' }}</dd></div>
            </dl>
        </x-guru.card>

        @can('create', App\Models\StudentReferral::class)
            <x-guru.button :href="route('attendance.kesiswaan.referrals.create', $student)" icon="send" class="g-btn--block">Buat rujukan ke Guru BK</x-guru.button>
        @endcan

        <section class="flex flex-col gap-2.5" aria-labelledby="judul-riwayat-rujukan">
            <div class="flex items-end justify-between gap-3">
                <h2 id="judul-riwayat-rujukan" class="g-h2">Riwayat rujukan</h2>
                <span class="g-num text-xs font-bold text-guru-muted">{{ $referrals->total() }}</span>
            </div>
            @if ($referrals->isEmpty())
                <x-guru.empty icon="send" title="Belum ada rujukan">Rujukan siswa akan tampil di bagian ini.</x-guru.empty>
            @else
                <x-guru.list>
                    @foreach ($referrals as $referral)
                        <x-guru.list-item :href="route('attendance.kesiswaan.referrals.show', $referral)" icon="send" tone="attn" :title="$referral->reason"
                            :desc="$referral->observed_at?->locale('id')->isoFormat('D MMM Y').' · '.($referral->counselor?->name ?? 'Belum ditangani')">
                            <x-slot:end>
                                <x-guru.chip>{{ $referralStatusLabels[$referral->status->value] ?? $referral->status->value }}</x-guru.chip>
                            </x-slot:end>
                        </x-guru.list-item>
                    @endforeach
                </x-guru.list>
            @endif
            @if ($referrals->hasPages())
                <div class="g-pager">{{ $referrals->links('pagination::simple-tailwind') }}</div>
            @endif
        </section>
    </div>
</x-layouts.mobile>
