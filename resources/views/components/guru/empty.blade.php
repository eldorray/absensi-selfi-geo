@props([
    'icon' => 'inbox',
    'title',
])

<div {{ $attributes->class(['g-card', 'g-empty']) }}>
    <span class="g-empty__icon"><x-guru.icon :name="$icon" :size="24" /></span>
    <h2>{{ $title }}</h2>
    <p>{{ $slot }}</p>
    {{ $action ?? '' }}
</div>
