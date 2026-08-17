<x-layouts.mobile title="Riwayat Absen" backUrl="{{ route('attendance.dashboard') }}" activeTab="riwayat">
    <x-slot:headerAction>
        <span class="text-[9px] font-bold theme-text-muted font-outfit uppercase bg-white/5 border border-white/10 px-2.5 py-1 rounded-full shadow-inner">
            {{ $attendances->total() }} Total
        </span>
    </x-slot:headerAction>

    <div class="space-y-3 pb-4">
        @forelse($attendances as $index => $attendance)
            <div class="interactive-card glass-card theme-border rounded-[24px] p-3.5 flex items-center gap-3.5 hover:scale-[1.01] transition-all duration-300 animate-stagger shadow-sm"
                 style="animation-delay: {{ min($index * 40, 240) }}ms;">
                <!-- Selfie Image Preview -->
                <div class="w-14 h-14 rounded-2xl overflow-hidden flex-none bg-slate-900 border border-white/10 shadow-inner">
                    <img src="{{ $attendance->image_url }}" alt="Selfie" class="w-full h-full object-cover">
                </div>

                <!-- Info Details -->
                <div class="flex-1 min-w-0 text-left">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-black text-xs theme-text-main font-display truncate capitalize">
                            {{ $attendance->created_at->locale('id')->isoFormat('dddd') }}
                        </p>
                        
                        <span class="px-2.5 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-full flex-none @if($attendance->status->value === 'present') theme-status-ok-card theme-status-ok-text status-badge-glow @else theme-status-late-card theme-status-late-text @endif">
                            {{ $attendance->status->label() }}
                        </span>
                    </div>
                    
                    <p class="text-[9px] theme-text-muted font-outfit mt-0.5">
                        {{ $attendance->created_at->format('d M Y') }}
                    </p>
                    
                    <div class="flex items-center gap-2 mt-2 text-[9px] font-bold theme-text-muted font-outfit">
                        <!-- Clock Info -->
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Masuk: {{ $attendance->created_at->format('H:i') }} WIB
                        </span>
                        
                        <span class="opacity-30">•</span>
                        
                        <!-- Geofence Info -->
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            {{ number_format($attendance->distance_meters, 0) }}m
                        </span>
                    </div>

                    @if($attendance->check_out_at)
                        <div class="mt-2 pt-2 theme-border-t flex items-center justify-between text-[9px] font-bold theme-text-muted font-outfit">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3.007-3L18 10.5m-3.007 3L18 13.5"/>
                                </svg>
                                Pulang: {{ $attendance->check_out_at->format('H:i') }} WIB
                            </span>
                            
                            @if($attendance->check_out_image_url)
                                <a href="{{ $attendance->check_out_image_url }}" target="_blank" class="theme-status-ok-text hover:underline text-[8px] uppercase tracking-wider font-black">
                                    Lihat Foto
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <!-- Empty state -->
            <div class="animate-stagger stagger-40 rounded-3xl p-8 text-center glass-card theme-border">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/5 flex items-center justify-center theme-text-muted">
                    <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="font-bold text-sm theme-text-main font-display">Belum Ada Riwayat</h3>
                <p class="text-[10px] theme-text-muted mt-1">Data riwayat kehadiran Anda hari ini dan sebelumnya akan dicantumkan di sini.</p>
            </div>
        @endforelse

        <!-- Simple Tailwind Pagination Customization -->
        @if ($attendances->hasPages())
            <div class="mt-4 pt-1 flex justify-center text-xs">
                {{ $attendances->links('pagination::simple-tailwind') }}
            </div>
        @endif
    </div>
</x-layouts.mobile>
