{{-- Kontrol di slot wajib memakai id="{{ $for }}" dan, saat error,
     aria-invalid="true" + aria-describedby="{{ $for }}-error". --}}
@props([
    'label',
    'for',
    'error' => null,
    'hint' => null,
    'required' => false,
])

<div {{ $attributes->class(['g-field']) }}>
    <label for="{{ $for }}" class="g-label">{{ $label }}@if ($required) <span class="g-req" aria-hidden="true">*</span>@endif</label>
    {{ $slot }}
    @if ($hint)
        <p id="{{ $for }}-hint" class="g-hint">{{ $hint }}</p>
    @endif
    @if ($error)
        @error($error)
            <p id="{{ $for }}-error" class="g-error">{{ $message }}</p>
        @enderror
    @endif
</div>
