@props([
    'title',
    'href' => null,
    'link' => null,
])

<div {{ $attributes->class(['g-section']) }}>
    <h2>{{ $title }}</h2>
    @if ($href && $link)
        <a href="{{ $href }}">{{ $link }}</a>
    @endif
</div>
