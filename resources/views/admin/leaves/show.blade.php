<x-layouts.app>
    <div class="space-y-6">
        <x-admin.page-header kicker="Kehadiran" title="Detail Pengajuan"
            description="Tinjau dan proses pengajuan perizinan">
            <a href="{{ route('admin.leaves.index') }}"
                class="admin-button-secondary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
        </x-admin.page-header>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="admin-alert-success rounded-2xl p-4 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="admin-alert-danger rounded-2xl p-4 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Leave Info -->
                <div class="admin-glass-panel p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <span class="admin-label" style="margin-bottom: 0">Detail Pengajuan</span>
                        <div class="flex gap-2">
                            <span
                                class="{{ $leave->type === 'sakit' ? 'admin-status-danger' : 'admin-status-info' }} px-3 py-1 text-xs">
                                {{ $leave->type_label }}
                            </span>
                            <span
                                class="{{ match ($leave->status) {'pending' => 'admin-status-warning','approved' => 'admin-status-success','rejected' => 'admin-status-danger',default => 'admin-status-neutral'} }} px-3 py-1 text-xs">
                                {{ $leave->status_label }}
                            </span>
                        </div>
                    </div>

                    <dl class="admin-dl mb-6 grid-cols-2">
                        <div>
                            <dt>Tanggal Mulai</dt>
                            <dd class="font-semibold">{{ $leave->start_date->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt>Tanggal Selesai</dt>
                            <dd class="font-semibold">{{ $leave->end_date->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt>Durasi</dt>
                            <dd class="font-semibold">{{ $leave->duration }} hari</dd>
                        </div>
                        <div>
                            <dt>Diajukan</dt>
                            <dd class="font-semibold">{{ $leave->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                    </dl>

                    <div>
                        <span class="admin-label">Alasan</span>
                        <p class="rounded-2xl border p-4 text-sm"
                            style="border-color: var(--admin-border-soft); background: var(--admin-glass-soft)">
                            {{ $leave->reason }}
                        </p>
                    </div>
                </div>

                <!-- Attachment -->
                @if ($leave->attachment)
                    <div class="admin-glass-panel p-6">
                        <span class="admin-label">Lampiran</span>
                        <a href="{{ $leave->attachment_url }}" target="_blank">
                            <img src="{{ $leave->attachment_url }}" alt="Lampiran"
                                class="max-h-96 w-full rounded-2xl object-contain">
                        </a>
                    </div>
                @endif

                <!-- Approval Actions -->
                @if ($leave->isPending())
                    <div class="admin-glass-panel p-6">
                        <span class="admin-label">Tindakan</span>

                        <div class="flex gap-4">
                            <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="flex-1"
                                x-data="{}"
                                @submit.prevent="$dispatch('admin-confirm', {
                                    title: 'Setujui Pengajuan',
                                    message: 'Setujui pengajuan ini?',
                                    confirmText: 'Setujui',
                                    variant: 'success',
                                    form: $el,
                                })">
                                @csrf
                                <button type="submit" class="admin-button-success w-full py-3 text-sm">
                                    &#10003; Setujui
                                </button>
                            </form>
                        </div>

                        <div class="admin-alert-danger mt-4 rounded-2xl bg-red-50 dark:bg-red-900/20 p-4">
                            <form action="{{ route('admin.leaves.reject', $leave) }}" method="POST"
                                x-data="{}"
                                @submit.prevent="$dispatch('admin-confirm', {
                                    title: 'Tolak Pengajuan',
                                    message: 'Tolak pengajuan ini?',
                                    confirmText: 'Tolak',
                                    variant: 'danger',
                                    form: $el,
                                })">
                                @csrf
                                <label for="rejection_reason" class="admin-label">Tolak dengan alasan:</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="2" class="admin-field mb-3 p-3"
                                    placeholder="Masukkan alasan penolakan..."></textarea>
                                <button type="submit" class="admin-button-danger w-full py-2 text-sm">
                                    &#10005; Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Approval Info -->
                @if (!$leave->isPending())
                    <div class="admin-glass-panel p-6">
                        <span class="admin-label">{{ $leave->isApproved() ? 'Disetujui' : 'Ditolak' }}</span>
                        <div class="flex items-center gap-4">
                            <span class="admin-avatar admin-avatar-md">
                                {{ $leave->approver ? substr($leave->approver->name, 0, 1) : '?' }}
                            </span>
                            <div>
                                <p class="text-sm font-bold">{{ $leave->approver?->name ?? '-' }}</p>
                                <p class="admin-muted text-xs">{{ $leave->approved_at?->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        @if ($leave->isRejected() && $leave->rejection_reason)
                            <div class="admin-alert-danger mt-4 rounded-2xl bg-red-50 dark:bg-red-900/20 p-4">
                                <p class="mb-1 text-sm font-bold">Alasan Penolakan:</p>
                                <p class="text-sm">{{ $leave->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar - Employee Info -->
            <div class="space-y-6">
                <div class="admin-glass-panel p-6">
                    <span class="admin-label">Info Karyawan</span>

                    <div class="mb-4 text-center">
                        <span class="admin-avatar admin-avatar-lg mx-auto mb-3">
                            {{ $leave->user->initials() }}
                        </span>
                        <h3 class="text-sm font-bold">{{ $leave->user->name }}</h3>
                        <p class="admin-muted text-xs">{{ $leave->user->email }}</p>
                    </div>

                    <dl class="admin-dl text-sm">
                        <div>
                            <dt>Role</dt>
                            <dd class="font-semibold">{{ $leave->user->role?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt>Kantor</dt>
                            <dd class="font-semibold">{{ $leave->user->office?->name ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <a href="{{ route('admin.leaves.index') }}"
                    class="admin-button-secondary block w-full py-3 text-center text-sm">
                    &larr; Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
