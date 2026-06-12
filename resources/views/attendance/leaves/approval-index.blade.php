<x-layouts.mobile title="Persetujuan Izin" showNav="true">
    <x-slot:headerAction>
        @if ($pendingCount > 0)
            <span class="px-2 py-1 bg-amber-400/10 border border-amber-400/20 text-amber-500 rounded-lg text-[9px] font-black uppercase tracking-wider">
                {{ $pendingCount }} Pending
            </span>
        @endif
    </x-slot:headerAction>

    <div class="space-y-4 pb-4">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs theme-status-ok-text text-left font-bold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter tag list -->
        <div class="flex gap-2 overflow-x-auto pb-1.5 custom-scroll select-none">
            <a href="{{ route('approval.leaves.index') }}"
                class="px-4.5 py-2 rounded-full text-[10px] font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-300 @if(!request('status')) bg-white text-slate-950 font-black shadow-md @else glass-card theme-border theme-text-muted hover:theme-text-main @endif">
                Semua
            </a>
            <a href="{{ route('approval.leaves.index', ['status' => 'pending']) }}"
                class="px-4.5 py-2 rounded-full text-[10px] font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-300 @if(request('status') == 'pending') bg-amber-500 text-slate-950 font-black shadow-md @else glass-card theme-border theme-text-muted hover:theme-text-main @endif">
                Menunggu
            </a>
            <a href="{{ route('approval.leaves.index', ['status' => 'approved']) }}"
                class="px-4.5 py-2 rounded-full text-[10px] font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-300 @if(request('status') == 'approved') bg-emerald-500 text-slate-950 font-black shadow-md @else glass-card theme-border theme-text-muted hover:theme-text-main @endif">
                Disetujui
            </a>
            <a href="{{ route('approval.leaves.index', ['status' => 'rejected']) }}"
                class="px-4.5 py-2 rounded-full text-[10px] font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-300 @if(request('status') == 'rejected') bg-red-500 text-slate-950 font-black shadow-md @else glass-card theme-border theme-text-muted hover:theme-text-main @endif">
                Ditolak
            </a>
        </div>

        <!-- Approval list items -->
        <div class="space-y-3">
            @forelse($leaves as $leave)
                <a href="{{ route('approval.leaves.show', $leave) }}"
                    class="block glass-card theme-border rounded-[22px] p-4 text-left hover:scale-[1.01] transition-transform duration-300">
                    
                    <div class="flex items-start justify-between gap-3.5 mb-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- User initials avatar -->
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-400 to-emerald-400 p-[1px] flex-none">
                                <div class="w-full h-full rounded-xl bg-slate-950 flex items-center justify-center font-bold text-white text-xs font-outfit uppercase">
                                    {{ substr($leave->user->name, 0, 2) }}
                                </div>
                            </div>
                            
                            <div class="leading-none text-left min-w-0">
                                <p class="font-black text-xs theme-text-main font-display truncate">{{ $leave->user->name }}</p>
                                <p class="text-[9px] theme-text-muted mt-1 font-outfit uppercase font-semibold truncate">{{ $leave->user->role?->name ?? 'Pegawai' }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-full flex-none @if($leave->status === 'approved') theme-status-ok-card theme-status-ok-text @elseif($leave->status === 'rejected') theme-status-late-card theme-status-late-text @else bg-amber-400/10 text-amber-500 border border-amber-500/20 @endif">
                            {{ $leave->status_label }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-1.5">
                            <!-- Type Badge -->
                            <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-full bg-white/10 theme-text-main">
                                {{ $leave->type_label }}
                            </span>
                            <span class="text-[9px] font-bold theme-text-muted font-outfit uppercase">{{ $leave->duration }} hari</span>
                        </div>
                        
                        <p class="text-[9px] font-bold theme-text-muted font-outfit uppercase">{{ $leave->start_date->format('d M Y') }}</p>
                    </div>
                </a>
            @empty
                <!-- Empty state -->
                <div class="rounded-3xl p-8 text-center glass-card theme-border">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-white/5 flex items-center justify-center theme-text-muted">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm theme-text-main">Tidak Ada Pengajuan</h3>
                    <p class="text-[10px] theme-text-muted mt-1">Belum ada pengajuan izin masuk dalam kategori filter ini.</p>
                </div>
            @endforelse
        </div>

        <!-- Paginations -->
        @if ($leaves->hasPages())
            <div class="mt-4 pt-1 flex justify-center text-xs">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</x-layouts.mobile>
