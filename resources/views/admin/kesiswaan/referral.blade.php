@php
    $statusLabels = ['new' => 'Baru', 'in_handling' => 'Dalam penanganan', 'completed' => 'Selesai', 'rejected' => 'Ditolak'];
    $statusTones = ['new' => 'admin-status-info', 'in_handling' => 'admin-status-warning', 'completed' => 'admin-status-success', 'rejected' => 'admin-status-danger'];
    $status = $referral->status->value;
@endphp

<x-layouts.app>
    <div class="mx-auto max-w-4xl space-y-6">
        <x-admin.page-header kicker="Kesiswaan · Hanya-baca" title="Rujukan {{ $referral->student?->nama_lengkap ?? 'Siswa' }}"
            description="Mode pengawasan hanya-baca.">
            @if ($referral->student)
                <a href="{{ route('admin.kesiswaan.show', $referral->student) }}"
                    class="admin-button-secondary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Kembali ke profil
                </a>
            @endif
        </x-admin.page-header>

        <section class="admin-glass-panel p-6">
            <dl class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="admin-label">Alasan</dt>
                    <dd class="text-sm">{{ $referral->reason }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="admin-label">Pengamatan ({{ $referral->observed_at?->translatedFormat('d F Y') ?? '-' }})</dt>
                    <dd class="whitespace-pre-line text-sm">{{ $referral->observation }}</dd>
                </div>
                <div>
                    <dt class="admin-label">Status</dt>
                    <dd>
                        <span class="{{ $statusTones[$status] ?? 'admin-status-neutral' }} px-2.5 py-1 text-xs">{{ $statusLabels[$status] ?? $status }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="admin-label">Ringkasan aman</dt>
                    <dd class="text-sm">{{ $referral->safe_summary ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="admin-label">Pembuat</dt>
                    <dd class="text-sm">{{ $referral->creator?->name ?? 'Akun dihapus' }}</dd>
                </div>
                <div>
                    <dt class="admin-label">Penanggung jawab</dt>
                    <dd class="text-sm">{{ $referral->counselor?->name ?? 'Belum ditangani' }}</dd>
                </div>
            </dl>
        </section>

        <div class="grid gap-6 md:grid-cols-2">
            <section class="admin-glass-panel overflow-hidden">
                <div class="admin-panel-header"><h2 class="admin-label">Lampiran privat</h2></div>
                @if ($referral->attachments->isNotEmpty())
                    <ul class="divide-y admin-divider">
                        @foreach ($referral->attachments as $attachment)
                            <li class="px-5 py-3 text-sm">
                                <a class="font-semibold underline-offset-2 hover:underline"
                                    href="{{ route('admin.kesiswaan.referrals.attachments.show', [$referral, $attachment]) }}">{{ $attachment->original_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-admin.empty-state icon="fas-folder" title="Tidak ada lampiran" />
                @endif
            </section>

            <section class="admin-glass-panel overflow-hidden">
                <div class="admin-panel-header"><h2 class="admin-label">Riwayat status</h2></div>
                @if ($referral->histories->isNotEmpty())
                    <ol class="divide-y admin-divider">
                        @foreach ($referral->histories as $history)
                            <li class="px-5 py-3 text-sm">
                                <p class="font-semibold">
                                    {{ $history->from_status ? ($statusLabels[$history->from_status] ?? $history->from_status) : 'Awal' }}
                                    <span aria-hidden="true">→</span><span class="sr-only">menjadi</span>
                                    {{ $statusLabels[$history->to_status] ?? $history->to_status }}
                                </p>
                                <p class="admin-muted text-xs">{{ $history->actor?->name ?? 'Akun dihapus' }} · {{ $history->transitioned_at?->translatedFormat('d M Y H:i') }}</p>
                                @if ($history->safe_summary)
                                    <p class="mt-1">{{ $history->safe_summary }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @else
                    <x-admin.empty-state icon="fas-clock" title="Belum ada riwayat status" />
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
