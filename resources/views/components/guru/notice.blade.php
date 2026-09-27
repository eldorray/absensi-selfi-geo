{{-- tone: info | warn | error | ok. Tambahkan role="alert"/"status" bila pesan mengumumkan perubahan. --}}
@props([
    'tone' => 'info',
    'title' => null,
    'icon' => null,
])

@php
    $icon ??= match ($tone) {
        'warn' => 'alert',
        'error' => 'alert',
        'ok' => 'check',
        default => 'info',
    };
@endphp

<div {{ $attributes->class(['g-notice', 'g-notice--'.$tone => $tone !== 'info']) }}>
    <span class="g-notice__icon"><x-guru.icon :name="$icon" :size="18" /></span>
    <div class="min-w-0">
        @if ($title)
            <strong>{{ $title }}</strong>
        @endif
        {{ $slot }}
    </div>
</div>
