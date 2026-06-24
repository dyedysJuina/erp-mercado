@props([
    'variant' => 'primary',
    'type' => 'submit',
    'loading' => false,
    'disabled' => false,
])

@php
$class = match ($variant) {
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'danger' => 'btn-danger',
    default => 'btn-primary',
};
@endphp

<button
    type="{{ $type }}"
    {{ $disabled || $loading ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $class]) }}
>
    @if ($loading)
        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
    @endif
    {{ $slot }}
</button>