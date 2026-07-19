@props(['kicker' => null, 'title', 'description' => null, 'count' => null])

<div {{ $attributes->class(['admin-page-header flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="min-w-0">
        @if ($kicker)
            <span class="admin-kicker">{{ $kicker }}</span>
        @endif
        <div class="mt-1.5 flex flex-wrap items-center gap-3">
            <h1>{{ $title }}</h1>
            @if (!is_null($count))
                <span class="admin-chip">{{ $count }}</span>
            @endif
        </div>
        @if ($description)
            <p class="mt-1.5 text-sm">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-none flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
