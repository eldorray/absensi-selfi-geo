{{-- Detail satu pengajuan + tombol proses. Dipakai di daftar perizinan (panel kanan) dan halaman detail. --}}
@php
    $statusClass = match ($leave->status) {'pending' => 'admin-status-warning','approved' => 'admin-status-success','rejected' => 'admin-status-danger',default => 'admin-status-neutral'};
    $dates = $leave->start_date->equalTo($leave->end_date)
        ? $leave->start_date->locale('id')->translatedFormat('d M Y')
        : $leave->start_date->locale('id')->translatedFormat('d M').'–'.$leave->end_date->locale('id')->translatedFormat('d M Y');
@endphp

<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start gap-4">
        <span class="admin-avatar admin-avatar-md">{{ $leave->user->initials() }}</span>
        <div class="min-w-[12rem] flex-1">
            <h2 id="detail-pengajuan-judul" class="admin-display text-[1.75rem] leading-tight">{{ $leave->user->name }}</h2>
            <p class="admin-muted text-[13px]">
                {{ $leave->user->office?->name ?? ($leave->user->role?->name ?? '-') }} · diajukan
                {{ $leave->created_at->locale('id')->translatedFormat('d M Y, H:i') }}
            </p>
        </div>
        <span class="{{ $statusClass }} px-3 py-1 text-[13px]">{{ $leave->status_label }}</span>
    </div>

    <dl class="admin-fact-grid">
        <div>
            <dt class="admin-label" style="margin-bottom: 0">Jenis</dt>
            <dd><span class="{{ $leave->type === 'sakit' ? 'admin-status-danger' : 'admin-status-info' }} px-2.5 py-1 text-xs">{{ $leave->type_label }}</span></dd>
        </div>
        <div>
            <dt class="admin-label" style="margin-bottom: 0">Tanggal</dt>
            <dd class="admin-display text-[1.375rem]">{{ $dates }}</dd>
        </div>
        <div>
            <dt class="admin-label" style="margin-bottom: 0">Durasi</dt>
            <dd class="admin-display text-[1.375rem]">{{ $leave->duration }} hari</dd>
        </div>
    </dl>

    <div>
        <h3 class="admin-label">Alasan</h3>
        <p class="max-w-prose text-[15px] leading-relaxed" style="text-wrap: pretty">{{ $leave->reason }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_16rem]">
        <div>
            <h3 class="admin-label">Lampiran</h3>
            @if ($leave->attachment)
                <a href="{{ $leave->attachment_url }}" target="_blank" rel="noopener"
                    class="admin-row-link flex items-center gap-3 rounded-xl border p-2.5 pr-4" style="border-color: var(--admin-border)">
                    <img src="{{ $leave->attachment_url }}" alt="Lampiran {{ $leave->type_label }} {{ $leave->user->name }}"
                        class="h-14 w-14 shrink-0 rounded-lg object-cover" loading="lazy">
                    <span class="flex min-w-0 flex-col">
                        <span class="truncate font-semibold">{{ basename($leave->attachment) }}</span>
                        <span class="admin-muted text-xs">Buka di tab baru</span>
                    </span>
                </a>
            @else
                <p class="admin-muted text-sm">Tidak ada lampiran.</p>
            @endif
        </div>
        <div>
            <h3 class="admin-label">Disetujui tahun ajaran ini</h3>
            <div class="grid grid-cols-3 gap-2">
                @foreach (['izin' => 'Izin', 'sakit' => 'Sakit', 'cuti' => 'Cuti'] as $key => $label)
                    <div class="flex flex-col rounded-xl px-3 py-2" style="background: var(--admin-canvas)">
                        <span class="admin-muted text-xs">{{ $label }}</span>
                        <span class="admin-display text-xl">{{ $history[$key] ?? 0 }}<span class="admin-muted text-xs"> hr</span></span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if ($leave->isPending())
        <div class="flex flex-col gap-3 border-t pt-5" style="border-color: var(--admin-border-soft)">
            <form id="approve-leave-{{ $leave->id }}" action="{{ route('admin.leaves.approve', $leave) }}" method="POST"
                x-data="{}"
                @submit.prevent="$dispatch('admin-confirm', {
                    title: 'Setujui Pengajuan',
                    message: @js('Setujui pengajuan '.$leave->type_label.' dari '.$leave->user->name.'?'),
                    confirmText: 'Setujui',
                    variant: 'success',
                    form: $el,
                })">
                @csrf
            </form>

            <form action="{{ route('admin.leaves.reject', $leave) }}" method="POST"
                x-data="{ reason: @js(old('rejection_reason', '')) }"
                @submit.prevent="$dispatch('admin-confirm', {
                    title: 'Tolak Pengajuan',
                    message: @js('Tolak pengajuan '.$leave->type_label.' dari '.$leave->user->name.'?'),
                    confirmText: 'Tolak',
                    variant: 'danger',
                    form: $el,
                })">
                @csrf
                <label for="rejection_reason-{{ $leave->id }}" class="admin-label">
                    Alasan penolakan <span class="font-medium">— wajib jika menolak, akan terlihat oleh pegawai</span>
                </label>
                <textarea name="rejection_reason" id="rejection_reason-{{ $leave->id }}" rows="2" x-model="reason"
                    class="admin-field resize-y px-3.5 py-3" maxlength="500"
                    placeholder="Contoh: bertepatan dengan asesmen, mohon ajukan untuk tanggal lain."
                    @error('rejection_reason') aria-invalid="true" aria-describedby="rejection_reason-error" @enderror required>{{ old('rejection_reason') }}</textarea>
                @error('rejection_reason')
                    <p id="rejection_reason-error" class="admin-hint admin-text-danger">{{ $message }}</p>
                @enderror
                <div class="mt-3 flex flex-wrap justify-end gap-3">
                    <button type="submit" class="admin-button-danger-outline admin-button-secondary px-5 text-sm"
                        :disabled="reason.trim() === ''">
                        Tolak
                    </button>
                    <button type="submit" form="approve-leave-{{ $leave->id }}" class="admin-button-success px-5 text-sm">
                        <x-admin.icon name="check" />
                        Setujui
                    </button>
                </div>
            </form>
        </div>
    @elseif ($leave->isApproved())
        <div class="admin-alert-success flex items-center gap-3 rounded-xl p-4 text-sm font-semibold">
            <x-admin.icon name="check" />
            Disetujui oleh {{ $leave->approver?->name ?? '-' }}{{ $leave->approved_at ? ' · '.$leave->approved_at->locale('id')->translatedFormat('d M Y, H:i') : '' }}
        </div>
    @else
        <div class="admin-alert-danger rounded-xl p-4 text-sm">
            <p class="font-bold">Ditolak oleh {{ $leave->approver?->name ?? '-' }}{{ $leave->approved_at ? ' · '.$leave->approved_at->locale('id')->translatedFormat('d M Y, H:i') : '' }}</p>
            @if ($leave->rejection_reason)
                <p class="mt-1">{{ $leave->rejection_reason }}</p>
            @endif
        </div>
    @endif
</div>
