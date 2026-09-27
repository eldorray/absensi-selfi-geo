@props([
    'as' => 'section',
    'variant' => null,
])

<{{ $as }} {{ $attributes->class(['g-card', 'g-card--'.$variant => $variant]) }}>{{ $slot }}</{{ $as }}>
