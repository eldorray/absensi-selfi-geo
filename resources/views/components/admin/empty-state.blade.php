@props(['title', 'hint' => null, 'icon' => null])

<div {{ $attributes->class(['admin-empty-state']) }}>
    @if ($icon)
        <span class="admin-empty-state-icon" aria-hidden="true"><x-admin.icon :name="$icon" size="20" /></span>
    @endif
    <p class="admin-empty-state-title">{{ $title }}</p>
    @if ($hint)
        <p>{{ $hint }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
