<x-layouts.mobile title="Perizinan Saya" showNav="true">
    <x-slot:headerAction>
        <a href="{{ route('attendance.leaves.create') }}"
            class="px-3 py-1.5 rounded-lg bg-gradient-to-r from-cyan-500 to-emerald-500 hover:scale-105 active:scale-95 text-white text-[10px] font-bold uppercase tracking-wider transition-all duration-300 font-outfit shadow-sm">
            + Ajukan
        </a>
    </x-slot:headerAction>

    <div class="space-y-3 pb-4">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs theme-status-ok-text text-left font-bold">
                {{ session('success') }}
            </div>
        @endif

        @forelse($leaves as $leave)
            <a href="{{ route('attendance.leaves.show', $leave) }}"
                class="block glass-card theme-border rounded-[22px] p-4 text-left hover:scale-[1.01] transition-transform duration-300">
                
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <!-- Type Badge -->
                        <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-full bg-white/10 theme-text-main">
                            {{ $leave->type_label }}
                        </span>
                        
                        <!-- Status Badge -->
                        <span class="px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-full @if($leave->status === 'approved') theme-status-ok-card theme-status-ok-text @elseif($leave->status === 'rejected') theme-status-late-card theme-status-late-text @else bg-amber-400/10 text-amber-500 border border-amber-500/20 @endif">
                            {{ $leave->status_label }}
                        </span>
                    </div>
                    
                    <div class="theme-text-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </div>
                </div>

                <!-- Reason description -->
                <p class="font-bold text-xs theme-text-main font-display truncate mb-2">{{ $leave->reason }}</p>
                
                <!-- Date range duration -->
                <div class="flex items-center gap-1.5 text-[9px] font-bold theme-text-muted font-outfit uppercase">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                    <span>
                        {{ $leave->start_date->format('d M') }}
                        @if ($leave->start_date != $leave->end_date)
                            - {{ $leave->end_date->format('d M Y') }}
                        @else
                            {{ $leave->start_date->format('Y') }}
                        @endif
                    </span>
                    <span class="opacity-30">•</span>
                    <span class="theme-status-ok-text">{{ $leave->duration }} Hari</span>
                </div>
            </a>
        @empty
            <!-- Empty State -->
            <div class="rounded-3xl p-8 text-center glass-card theme-border">
                <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-white/5 flex items-center justify-center theme-text-muted">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-sm theme-text-main">Belum Ada Pengajuan</h3>
                <p class="text-[10px] theme-text-muted mt-1">Daftar izin, cuti, atau sakit yang Anda ajukan akan ditampilkan di sini.</p>
                
                <a href="{{ route('attendance.leaves.create') }}"
                    class="mt-6 flex items-center justify-center gap-1.5 w-full py-3 bg-gradient-to-r from-cyan-500 to-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-300 font-outfit shadow-sm">
                    Ajukan Izin Sekarang
                </a>
            </div>
        @endforelse

        <!-- Paginations -->
        @if ($leaves->hasPages())
            <div class="mt-4 pt-1 flex justify-center text-xs">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</x-layouts.mobile>
