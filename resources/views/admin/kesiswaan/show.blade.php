@php
    $assignment = $student->schoolClass?->homeroomAssignments?->first();
    $initials = collect(explode(' ', $student->nama_lengkap))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $typeLabels = ['violation' => 'Pelanggaran', 'counseling' => 'Konseling'];
    $statusLabels = ['new' => 'Baru', 'in_progress' => 'Dalam penanganan', 'waiting_follow_up' => 'Menunggu tindak lanjut', 'completed' => 'Selesai'];
    $referralStatusLabels = ['new' => 'Baru', 'in_handling' => 'Dalam penanganan', 'completed' => 'Selesai', 'rejected' => 'Ditolak'];
    $referralStatusClass = ['new' => 'admin-status-warning', 'in_handling' => 'admin-status-info', 'completed' => 'admin-status-success', 'rejected' => 'admin-status-neutral'];
@endphp

<x-layouts.app title="Profil Siswa">
    <div class="space-y-6" data-kesiswaan-design="profile-centered-admin">
        <x-admin.page-header kicker="Kesiswaan" title="Profil Siswa" description="Pusat informasi siswa dan pengawasan rujukan dalam mode hanya-baca.">
            <a href="{{ route('admin.kesiswaan.index') }}" class="admin-button-secondary px-4 text-sm">
                <x-admin.icon name="chevron-left" size="16" />
                Kembali ke daftar
            </a>
            <a href="{{ route('admin.students.edit', [$student->school_level, $student]) }}" class="admin-button-primary px-4 text-sm">Buka Data Siswa</a>
        </x-admin.page-header>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(19rem,0.75fr)]">
            <div class="space-y-6">
                <section class="admin-hero-card flex flex-col gap-4 p-6 sm:flex-row sm:items-center">
                    <span class="admin-hero-avatar" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="admin-hero-muted text-xs font-semibold">{{ $student->status ?? 'Siswa' }}</p>
                        <h2 class="admin-display mt-0.5 truncate text-[1.75rem] leading-tight">{{ $student->nama_lengkap }}</h2>
                        <p class="admin-hero-muted mt-1 text-xs tabular-nums">NISN {{ $student->nisn ?: '-' }} · NIK {{ $student->nik ?: '-' }}</p>
                    </div>
                    <span class="admin-hero-chip">{{ $student->schoolClass?->name ?? strtoupper($student->school_level) }}</span>
                </section>

                <section class="admin-glass-panel p-6">
                    <h2 class="admin-panel-title">Informasi siswa</h2>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach([
                            ['Kelas aktif', $student->schoolClass?->name ?? '-'],
                            ['Wali kelas', $assignment?->teacher?->name ?? '-'],
                            ['Tahun ajaran', $assignment?->academicYear?->name ?? '-'],
                            ['Jenjang', strtoupper($student->school_level)],
                            ['Telepon', $student->no_telepon ?: '-'],
                            ['Alamat', $student->alamat ?: '-'],
                        ] as [$label, $value])
                            <div class="admin-soft-tile p-4">
                                <dt class="admin-label" style="margin-bottom: .25rem">{{ $label }}</dt>
                                <dd class="text-sm font-bold">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section class="admin-glass-panel p-6">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <h2 class="admin-panel-title">Aktivitas rujukan</h2>
                            <p class="admin-muted mt-1 text-xs">Riwayat yang dapat diawasi administrator.</p>
                        </div>
                        <span class="admin-display text-2xl">{{ $referrals->total() }}</span>
                    </div>
                    <ul class="mt-4 space-y-3">
                        @forelse($referrals as $referral)
                            <li>
                                <a href="{{ route('admin.kesiswaan.referrals.show', $referral) }}" class="admin-row-link admin-card-link block p-4">
                                    <span class="flex items-start justify-between gap-3">
                                        <span class="text-sm font-bold">{{ $referral->reason }}</span>
                                        <span class="{{ $referralStatusClass[$referral->status->value] ?? 'admin-status-neutral' }} shrink-0 px-2.5 py-0.5 text-xs">{{ $referralStatusLabels[$referral->status->value] ?? $referral->status->value }}</span>
                                    </span>
                                    <span class="admin-muted mt-2 block text-xs">{{ $referral->observed_at?->locale('id')->translatedFormat('d M Y') }} · {{ $referral->counselor?->name ?? 'Belum ditangani' }}</span>
                                </a>
                            </li>
                        @empty
                            <li><x-admin.empty-state icon="clipboard" title="Belum ada rujukan" hint="Aktivitas rujukan siswa akan muncul di sini." /></li>
                        @endforelse
                    </ul>
                    <div class="mt-4">{{ $referrals->links() }}</div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="admin-glass-panel p-6">
                    <h2 class="admin-panel-title">Ringkasan BK yang aman</h2>
                    <p class="admin-muted mt-1 text-xs">Metadata umum tanpa mengubah isi profesional.</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="admin-tone-primary rounded-xl p-4">
                            <strong class="admin-display block text-3xl">{{ $summary['active_count'] }}</strong>
                            <span class="mt-1 block text-xs font-bold">Catatan aktif</span>
                        </div>
                        <div class="admin-tone-warning rounded-xl p-4">
                            <strong class="admin-display block text-2xl">{{ $summary['needs_follow_up'] ? 'Ya' : 'Tidak' }}</strong>
                            <span class="mt-1 block text-xs font-bold">Perlu tindak lanjut</span>
                        </div>
                    </div>
                    <dl class="mt-4 divide-y admin-border text-sm">
                        <div class="flex items-start justify-between gap-4 py-3"><dt class="admin-muted">Jenis</dt><dd class="max-w-[65%] text-right font-bold">{{ collect($summary['types'])->map(fn ($type) => $typeLabels[$type] ?? ucfirst($type))->implode(', ') ?: '-' }}</dd></div>
                        <div class="flex items-start justify-between gap-4 py-3"><dt class="admin-muted">Status</dt><dd class="max-w-[65%] text-right font-bold">{{ collect($summary['statuses'])->map(fn ($status) => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)))->implode(', ') ?: '-' }}</dd></div>
                    </dl>
                </section>

                <section class="admin-alert-success rounded-2xl p-5">
                    <h2 class="text-sm font-bold">Halaman ini hanya-baca</h2>
                    <p class="mt-2 text-xs leading-5">Perubahan identitas dilakukan melalui Data Siswa. Isi konseling dan catatan profesional tetap mengikuti kebijakan akses BK.</p>
                </section>
            </aside>
        </div>
    </div>
</x-layouts.app>
