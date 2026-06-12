<x-layouts.mobile title="Detail Perizinan" backUrl="{{ route('attendance.leaves.index') }}" showNav="true">
    <div class="space-y-4 pb-4">
        
        <!-- Status Overview Card -->
        <div class="glass-card theme-border rounded-[24px] p-5 text-left">
            <div class="flex items-center justify-between gap-3 mb-4.5">
                <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-full bg-white/10 theme-text-main">
                    {{ $leave->type_label }}
                </span>
                
                <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-full @if($leave->status === 'approved') theme-status-ok-card theme-status-ok-text @elseif($leave->status === 'rejected') theme-status-late-card theme-status-late-text @else bg-amber-400/10 text-amber-500 border border-amber-500/20 @endif">
                    {{ $leave->status_label }}
                </span>
            </div>

            <!-- Date range list -->
            <div class="space-y-3.5 text-xs">
                <div class="flex items-center gap-3 theme-text-muted">
                    <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center flex-none">
                        <svg class="w-4.5 h-4.5 text-cyan-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                    </div>
                    <div class="leading-none">
                        <p class="text-[8px] uppercase font-bold tracking-wider opacity-60">Durasi Perizinan</p>
                        <p class="font-bold theme-text-main mt-1">
                            {{ $leave->start_date->format('d M Y') }}
                            @if ($leave->start_date != $leave->end_date)
                                - {{ $leave->end_date->format('d M Y') }}
                            @endif
                            <span class="theme-status-ok-text ml-1">({{ $leave->duration }} hari)</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 theme-text-muted">
                    <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center flex-none">
                        <svg class="w-4.5 h-4.5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="leading-none">
                        <p class="text-[8px] uppercase font-bold tracking-wider opacity-60">Tanggal Pengajuan</p>
                        <p class="font-bold theme-text-main mt-1">{{ $leave->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reason Card -->
        <div class="glass-card theme-border rounded-[22px] p-4.5 text-left">
            <h3 class="font-black text-[10px] theme-text-muted font-outfit uppercase tracking-wider mb-2.5">Alasan Pengajuan</h3>
            <p class="text-xs theme-text-main leading-relaxed font-semibold">{{ $leave->reason }}</p>
        </div>

        <!-- Attachment image Card -->
        @if ($leave->attachment)
            <div class="glass-card theme-border rounded-[22px] p-4 text-left">
                <h3 class="font-black text-[10px] theme-text-muted font-outfit uppercase tracking-wider mb-3">Dokumen Lampiran</h3>
                <div class="rounded-xl overflow-hidden border border-white/10 shadow bg-slate-900">
                    <a href="{{ $leave->attachment_url }}" target="_blank">
                        <img src="{{ $leave->attachment_url }}" alt="Lampiran" class="w-full object-contain max-h-60 hover:opacity-90 transition-opacity">
                    </a>
                </div>
            </div>
        @endif

        <!-- Approval / Rejection details -->
        @if (!$leave->isPending())
            <div class="glass-card theme-border rounded-[22px] p-4.5 text-left">
                <h3 class="font-black text-[10px] theme-text-muted font-outfit uppercase tracking-wider mb-3.5">
                    {{ $leave->isApproved() ? 'Disetujui Oleh' : 'Ditolak Oleh' }}
                </h3>
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-400 to-emerald-400 p-[1px] flex-none">
                        <div class="w-full h-full rounded-xl bg-slate-950 flex items-center justify-center font-bold text-white text-xs font-outfit uppercase">
                            {{ $leave->approver ? substr($leave->approver->name, 0, 1) : '?' }}
                        </div>
                    </div>
                    
                    <div class="leading-none text-left flex-1 min-w-0">
                        <p class="font-bold text-xs theme-text-main font-display truncate">{{ $leave->approver?->name ?? '-' }}</p>
                        <p class="text-[9px] theme-text-muted mt-1 font-outfit uppercase font-semibold">{{ $leave->approved_at?->format('d M Y, H:i') }} WIB</p>
                    </div>
                </div>

                @if ($leave->isRejected() && $leave->rejection_reason)
                    <div class="mt-4 p-3.5 rounded-xl border border-red-500/20 bg-red-500/10 theme-status-late-text text-xs leading-normal">
                        <p class="font-black text-[10px] uppercase tracking-wider mb-1 font-outfit">Alasan Penolakan:</p>
                        <p class="font-semibold">{{ $leave->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-layouts.mobile>
