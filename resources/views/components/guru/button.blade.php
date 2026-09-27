@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'icon' => null,
])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['g-btn', 'g-btn--'.$variant]) }}>
        @if ($icon)<x-guru.icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['g-btn', 'g-btn--'.$variant]) }}>
        @if ($icon)<x-guru.icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
