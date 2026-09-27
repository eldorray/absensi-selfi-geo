@props(['tone' => 'neutral'])

<span {{ $attributes->class(['g-chip', 'g-chip--'.$tone]) }}>{{ $slot }}</span>
