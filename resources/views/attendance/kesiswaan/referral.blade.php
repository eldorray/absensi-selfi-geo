@php
    $statusLabels = ['new' => 'Baru', 'in_handling' => 'Ditangani', 'completed' => 'Selesai', 'rejected' => 'Ditolak'];
    $statusTones = ['new' => 'pending', 'in_handling' => 'izin', 'completed' => 'ok', 'rejected' => 'attn'];
    $urgencyLabels = ['normal' => 'Biasa', 'important' => 'Penting', 'urgent' => 'Mendesak'];
    $urgencyTones = ['normal' => 'neutral', 'important' => 'pending', 'urgent' => 'attn'];
    $backUrl = (int) $referral->created_by === auth()->id()
        ? route('attendance.referrals.mine')
        : (auth()->user()->is_bk_counselor ? route('attendance.referrals.queue') : route('attendance.dashboard'));
@endphp

<x-layouts.mobile title="Detail Rujukan" :backUrl="$backUrl">
    @if (session('success'))
        <x-guru.notice tone="ok" role="status">{{ session('success') }}</x-guru.notice>
    @endif
    @if ($errors->any())
        <x-guru.notice tone="error" title="Status belum diperbarui" role="alert">{{ $errors->first() }}</x-guru.notice>
    @endif

    <x-guru.card>
        <div class="flex flex-wrap items-center gap-2">
            <x-guru.chip :tone="$statusTones[$referral->status->value] ?? 'neutral'">{{ $statusLabels[$referral->status->value] ?? $referral->status->value }}</x-guru.chip>
            <x-guru.chip :tone="$urgencyTones[$referral->urgency->value] ?? 'neutral'">{{ $urgencyLabels[$referral->urgency->value] ?? $referral->urgency->value }}</x-guru.chip>
        </div>
        <h2 class="g-card__title">{{ $referral->student->nama_lengkap }}</h2>
        <p class="text-sm text-guru-muted">Dirujuk {{ $referral->creator?->name ?? 'akun dihapus' }} · {{ $referral->counselor?->name ? 'ditangani '.$referral->counselor->name : 'belum ditangani' }}</p>
    </x-guru.card>

    <x-guru.card as="div">
        <h2 class="g-h2">Alasan</h2>
        <p class="whitespace-pre-line">{{ $referral->reason }}</p>
        <h2 class="g-h2">Pengamatan {{ $referral->observed_at->locale('id')->isoFormat('D MMMM Y') }}</h2>
        <p class="whitespace-pre-line">{{ $referral->observation }}</p>
    </x-guru.card>

    @if ($referral->safe_summary)
        <x-guru.notice tone="ok" title="Ringkasan dari Guru BK">{{ $referral->safe_summary }}</x-guru.notice>
    @endif

    @if ($referral->attachments->isNotEmpty())
        <section class="flex flex-col gap-2.5" aria-labelledby="judul-lampiran">
            <h2 id="judul-lampiran" class="g-h2">Lampiran</h2>
            <x-guru.list>
                @foreach ($referral->attachments as $attachment)
                    <x-guru.list-item :href="route('attendance.kesiswaan.referrals.attachments.show', [$referral, $attachment])" icon="paperclip" tone="neutral" :title="$attachment->original_name" />
                @endforeach
            </x-guru.list>
        </section>
    @endif

    @can('claim', $referral)
        <form method="POST" action="{{ route('attendance.kesiswaan.referrals.claim', $referral) }}">
            @csrf
            <x-guru.button type="submit" icon="check" class="g-btn--block">Ambil rujukan</x-guru.button>
        </form>
    @endcan

    @if (auth()->id() === $referral->assigned_counselor_id && $referral->status->value === 'in_handling' && ! $referral->bkRecord)
        <x-guru.button :href="route('attendance.bk.create', ['referral' => $referral->id])" icon="plus" class="g-btn--block">Buat catatan BK</x-guru.button>
    @endif

    @can('transition', $referral)
        <x-guru.card as="section" aria-labelledby="judul-status">
            <h2 id="judul-status" class="g-h2">Perbarui status</h2>
            <form method="POST" action="{{ route('attendance.kesiswaan.referrals.transition', $referral) }}" class="flex flex-col gap-3">
                @csrf
                @method('PATCH')
                <x-guru.field label="Status" for="status" error="status" :required="true">
                    <select id="status" name="status" required class="g-input">
                        <option value="completed" @selected(old('status') === 'completed')>Selesai</option>
                        <option value="rejected" @selected(old('status') === 'rejected')>Tolak</option>
                    </select>
                </x-guru.field>
                <x-guru.field label="Ringkasan aman" for="safe_summary" error="safe_summary" :required="true" hint="Terlihat oleh guru perujuk. Jangan tulis detail konseling.">
                    <textarea id="safe_summary" name="safe_summary" rows="3" required class="g-input"
                        aria-describedby="safe_summary-hint @error('safe_summary') safe_summary-error @enderror" @error('safe_summary') aria-invalid="true" @enderror>{{ old('safe_summary') }}</textarea>
                </x-guru.field>
                <x-guru.button type="submit" variant="secondary" class="g-btn--block">Perbarui</x-guru.button>
            </form>
        </x-guru.card>
    @endcan

    @if ($referral->histories->isNotEmpty())
        <x-guru.card as="section" aria-labelledby="judul-riwayat">
            <h2 id="judul-riwayat" class="g-h2">Riwayat</h2>
            @foreach ($referral->histories as $history)
                <div class="flex flex-col gap-1 border-t border-guru-divider pt-3 text-sm">
                    <b>{{ $history->from_status ? ($statusLabels[$history->from_status] ?? $history->from_status).' → ' : '' }}{{ $statusLabels[$history->to_status] ?? $history->to_status }}</b>
                    <p class="text-guru-muted">{{ $history->actor?->name ?? 'Akun dihapus' }} · <span class="g-num">{{ $history->transitioned_at->locale('id')->isoFormat('D MMM Y, HH.mm') }}</span></p>
                    @if ($history->safe_summary)
                        <p>{{ $history->safe_summary }}</p>
                    @endif
                </div>
            @endforeach
        </x-guru.card>
    @endif
</x-layouts.mobile>
