@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'toggleLabel' => null,
])
@php
    $errorId = $name.'-error';
@endphp
<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    @if ($toggleLabel)
        <div class="input-wrap">
    @endif
    <input id="{{ $name }}" @class(['text-input', 'has-trailing-action' => $toggleLabel]) type="{{ $type }}" name="{{ $name }}" @if (! is_null($value)) value="{{ $value }}" @endif @error($name) aria-invalid="true" aria-describedby="{{ $errorId }}" @enderror {{ $attributes }}>
    @if ($toggleLabel)
            <button type="button" class="password-toggle" data-password-toggle aria-controls="{{ $name }}" aria-label="Tampilkan {{ $toggleLabel }}" aria-pressed="false" data-label-show="Tampilkan {{ $toggleLabel }}" data-label-hide="Sembunyikan {{ $toggleLabel }}">
                <svg class="eye-open" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                    <circle cx="12" cy="12" r="2.8"></circle>
                </svg>
                <svg class="eye-closed" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="m3 3 18 18M10.6 6.1c.5-.1.9-.1 1.4-.1 6 0 9.5 6 9.5 6a15 15 0 0 1-2.2 2.8M6.2 6.2A15.2 15.2 0 0 0 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.4 4-1"></path>
                </svg>
            </button>
        </div>
    @endif
    @error($name)
        <p id="{{ $errorId }}" class="field-error">{{ $message }}</p>
    @enderror
</div>
