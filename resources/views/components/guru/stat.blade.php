@props([
    'value',
    'label',
    'tone' => 'ink',
])

<div {{ $attributes->class(['g-stat']) }}>
    <span class="g-stat__value g-stat__value--{{ $tone }}">{{ $value }}</span>
    <span class="g-stat__label">{{ $label }}</span>
</div>
