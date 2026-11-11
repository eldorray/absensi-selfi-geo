{{-- Baris daftar 64px: ikon netral, judul, keterangan, slot `end`, chevron bila tautan. --}}
@props([
    'href' => null,
    'icon' => null,
    'title',
    'desc' => null,
])

@php($tag = $href ? 'a' : 'div')

<{{ $tag }} {{ $attributes->class(['g-list__item'])->merge($href ? ['href' => $href] : []) }}>
    @if ($icon)
        <span class="g-list__icon"><x-guru.icon :name="$icon" /></span>
    @endif
    <span class="g-list__body">
        <span class="g-list__title">{{ $title }}</span>
        @if ($desc)
            <span class="g-list__desc">{{ $desc }}</span>
        @endif
        {{ $slot }}
    </span>
    {{ $end ?? '' }}
    @if ($href)
        <x-guru.icon name="chevron" :size="18" class="g-list__chev" />
    @endif
</{{ $tag }}>
