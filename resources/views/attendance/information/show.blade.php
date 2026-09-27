<x-layouts.mobile title="Informasi" backUrl="{{ route('attendance.dashboard') }}">
    <article class="g-card g-card--flush">
        @if ($announcement->image_url)
            <img src="{{ $announcement->image_url }}" alt="" class="aspect-video w-full object-cover">
        @endif
        <div class="flex flex-col gap-3 p-5">
            <x-guru.chip tone="ok" class="self-start">Pengumuman · {{ $announcement->created_at->locale('id')->isoFormat('D MMMM Y') }}</x-guru.chip>
            <h2 class="g-card__title">{{ $announcement->title }}</h2>
            @if ($announcement->summary)
                <p class="text-guru-muted">{{ $announcement->summary }}</p>
            @endif
            <p class="whitespace-pre-line border-t border-guru-divider pt-4 leading-relaxed">{{ $announcement->body }}</p>
        </div>
    </article>
</x-layouts.mobile>
