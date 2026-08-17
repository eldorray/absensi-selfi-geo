<x-layouts.mobile title="Detail Pengajuan" backUrl="{{ route('approval.leaves.index') }}" isSheet="true" showNav="true">
    <div class="space-y-4 pb-4">
        
        @if (session('success'))
            <div class="animate-stagger stagger-0 rounded-2xl border border-emerald-500/25 bg-emerald-500/15 px-4 py-3 text-xs theme-status-ok-text text-left font-bold shadow-sm flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-emerald-400 flex-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="animate-stagger stagger-0 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-xs text-red-400 text-left font-bold shadow-sm flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-red-400 flex-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Employee Info Card -->
        <div class="glass-card theme-border rounded-[28px] p-5 text-left flex items-center gap-4 animate-stagger stagger-0 shadow-md">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-400 to-green-600 p-[1.5px] flex-none shadow-sm">
                <div class="w-full h-full rounded-2xl bg-slate-950 flex items-center justify-center font-bold text-white text-sm font-outfit uppercase">
                    {{ $leave->user->initials() }}
                </div>
            </div>
            <div class="leading-tight flex-1 min-w-0">
                <h3 class="font-black text-sm theme-text-main font-display truncate">{{ $leave->user->name }}</h3>
                <p class="text-[9px] theme-text-muted mt-1 font-outfit uppercase font-semibold truncate">{{ $leave->user->role?->name ?? 'Pegawai' }}</p>
                <p class="text-[8px] theme-text-muted mt-0.5 font-outfit uppercase font-medium truncate opacity-75">{{ $leave->user->office?->name ?? '-' }}</p>
            </div>
        </div>

        <!-- Leave Detail Card -->
        <div class="glass-card theme-border rounded-[28px] p-5 text-left animate-stagger stagger-40 shadow-lg">
            <div class="flex items-center justify-between gap-3 mb-4">
                <span class="px-3 py-1 text-[9px] font-black uppercase tracking-wider rounded-full bg-white/10 theme-text-main shadow-inner">
                    {{ $leave->type_label }}
                </span>
                
                <span class="px-3 py-1 text-[9px] font-black uppercase tracking-wider rounded-full @if($leave->status === 'approved') theme-status-ok-card theme-status-ok-text status-badge-glow @elseif($leave->status === 'rejected') theme-status-late-card theme-status-late-text @else bg-amber-400/15 text-amber-400 border border-amber-400/30 @endif">
                    {{ $leave->status_label }}
                </span>
            </div>

            <!-- Date range list -->
            <div class="space-y-3.5 text-xs mb-4">
                <div class="flex items-center gap-3 theme-text-muted">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center flex-none text-emerald-400">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <p class="text-[8px] uppercase font-bold tracking-wider opacity-60">Durasi Perizinan</p>
                        <p class="font-bold theme-text-main mt-0.5">
                            {{ $leave->start_date->format('d M Y') }}
                            @if ($leave->start_date != $leave->end_date)
                                - {{ $leave->end_date->format('d M Y') }}
                            @endif
                            <span class="theme-status-ok-text font-black ml-1">({{ $leave->duration }} hari)</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 theme-text-muted">
                    <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center flex-none">
                        <svg class="w-4.5 h-4.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <p class="text-[8px] uppercase font-bold tracking-wider opacity-60">Tanggal Pengajuan</p>
                        <p class="font-bold theme-text-main mt-0.5">{{ $leave->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                </div>
            </div>

            <!-- Reason -->
            <div class="pt-4 border-t border-white/5">
                <h3 class="font-black text-[10px] theme-text-muted font-outfit uppercase tracking-wider mb-2">Alasan Pengajuan</h3>
                <p class="text-xs theme-text-main leading-relaxed font-semibold bg-white/5 p-3.5 rounded-2xl border border-white/5">{{ $leave->reason }}</p>
            </div>
        </div>

        <!-- Attachment Card -->
        @if ($leave->attachment)
            <div class="glass-card theme-border rounded-[28px] p-5 text-left animate-stagger stagger-80 shadow-md">
                <h3 class="font-black text-[10px] theme-text-muted font-outfit uppercase tracking-wider mb-3">Dokumen Lampiran</h3>
                <div class="rounded-2xl overflow-hidden border border-white/10 shadow bg-slate-950/60 p-1">
                    <a href="{{ $leave->attachment_url }}" target="_blank" class="block group relative overflow-hidden rounded-xl">
                        <img src="{{ $leave->attachment_url }}" alt="Lampiran" class="w-full object-contain max-h-60 group-hover:scale-102 transition-transform duration-300">
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold font-outfit">
                            Buka Lampiran Penuh
                        </div>
                    </a>
                </div>
            </div>
        @endif

        <!-- Approval Actions -->
        @if ($leave->isPending())
            <div class="space-y-3 animate-stagger stagger-120">
                <form action="{{ route('approval.leaves.approve', $leave) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-emerald-500 to-green-600 active:scale-98 text-white font-bold rounded-full transition-all shadow-lg flex items-center justify-center text-xs uppercase tracking-wider font-outfit"
                        onclick="return confirm('Setujui pengajuan izin ini?')">
                        <svg class="w-4.5 h-4.5 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        Setujui Pengajuan
                    </button>
                </form>

                <div class="glass-card theme-border rounded-[28px] p-5 text-left shadow-lg">
                    <form action="{{ route('approval.leaves.reject', $leave) }}" method="POST" class="space-y-3">
                        @csrf
                        <label class="block text-[10px] font-bold tracking-wide uppercase theme-text-muted font-outfit">Tolak dengan alasan</label>
                        
                        <textarea name="rejection_reason" rows="2" 
                            class="theme-input w-full rounded-2xl px-4 py-3 text-xs font-semibold"
                            placeholder="Tuliskan alasan penolakan..."></textarea>
                        @error('rejection_reason')
                            <p class="text-red-400 text-xs font-medium mt-1">{{ $message }}</p>
                        @enderror
                        
                        <button type="submit"
                            class="w-full py-3.5 bg-gradient-to-r from-red-500 to-rose-600 active:scale-98 text-white font-bold rounded-full transition-all shadow-md text-xs uppercase tracking-wider font-outfit"
                            onclick="return confirm('Tolak pengajuan izin ini?')">
                            Tolak Pengajuan
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- Approval / Rejection Info -->
            <div class="glass-card theme-border rounded-[28px] p-5 text-left animate-stagger stagger-120 shadow-md">
                <h3 class="font-black text-[10px] theme-text-muted font-outfit uppercase tracking-wider mb-3.5">
                    {{ $leave->isApproved() ? 'Disetujui Oleh' : 'Ditolak Oleh' }}
                </h3>
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-400 to-green-600 p-[1.5px] flex-none shadow-sm">
                        <div class="w-full h-full rounded-xl bg-slate-950 flex items-center justify-center font-bold text-white text-xs font-outfit uppercase">
                            {{ $leave->approver ? substr($leave->approver->name, 0, 1) : '?' }}
                        </div>
                    </div>
                    
                    <div class="leading-tight text-left flex-1 min-w-0">
                        <p class="font-bold text-xs theme-text-main font-display truncate">{{ $leave->approver?->name ?? '-' }}</p>
                        <p class="text-[9px] theme-text-muted mt-0.5 font-outfit uppercase font-semibold">
                            {{ $leave->approved_at ? $leave->approved_at->format('d M Y, H:i') : '-' }} WIB
                        </p>
                    </div>
                </div>

                @if ($leave->isRejected() && $leave->rejection_reason)
                    <div class="mt-4 p-3.5 rounded-2xl border border-red-500/20 bg-red-500/10 theme-status-late-text text-xs leading-normal">
                        <p class="font-black text-[10px] uppercase tracking-wider mb-1 font-outfit text-red-400">Alasan Penolakan:</p>
                        <p class="font-semibold">{{ $leave->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-layouts.mobile>
