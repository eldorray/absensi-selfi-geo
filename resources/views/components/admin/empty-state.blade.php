@props(['title', 'hint' => null, 'icon' => null])

<div {{ $attributes->class(['admin-empty-state']) }}>
    @if ($icon)
        <span class="admin-empty-state-icon" aria-hidden="true">@svg($icon, 'w-5 h-5')</span>
    @endif
    <p class="admin-empty-state-title">{{ $title }}</p>
    @if ($hint)
        <p>{{ $hint }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
