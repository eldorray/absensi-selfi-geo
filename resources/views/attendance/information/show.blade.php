<x-layouts.mobile title="Detail Informasi" backUrl="{{ route('attendance.dashboard') }}" showNav="true">
    <div class="space-y-4 pb-4">

        <!-- Hero image / fallback -->
        <div class="glass-card theme-border rounded-[24px] overflow-hidden">
            <div class="relative aspect-[16/9] w-full overflow-hidden">
                @if ($announcement->image_url)
                    <img src="{{ $announcement->image_url }}" alt="" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center p-5">
                        <span class="text-white font-black text-lg text-center">{{ $announcement->title }}</span>
                    </div>
                @endif
            </div>

            <div class="p-5">
                <p class="text-[8px] uppercase font-bold tracking-wider theme-text-muted opacity-70">
                    {{ $announcement->created_at->translatedFormat('d F Y') }}
                </p>
                <h1 class="font-black text-lg theme-text-main font-display mt-1 leading-snug">{{ $announcement->title }}</h1>

                @if ($announcement->summary)
                    <p class="text-xs theme-text-muted mt-2 font-outfit">{{ $announcement->summary }}</p>
                @endif

                <div class="mt-4 pt-4 theme-border-t">
                    <p class="text-sm theme-text-main leading-relaxed font-outfit whitespace-pre-line">{{ $announcement->body }}</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.mobile>
