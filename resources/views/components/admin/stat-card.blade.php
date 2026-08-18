@props(['tone' => 'indigo', 'label', 'value', 'meta' => null])

<div {{ $attributes->class(["admin-glass-panel admin-stat-card admin-stat-tone-{$tone}"]) }}>
    <span class="admin-stat-icon admin-tone-{{ $tone }}" aria-hidden="true">{{ $slot }}</span>
    <div class="min-w-0">
        <span class="admin-label">{{ $label }}</span>
        <p class="admin-stat-value">{{ $value }}</p>
        @if ($meta)
            <p class="admin-hint">{{ $meta }}</p>
        @endif
    </div>
</div>
