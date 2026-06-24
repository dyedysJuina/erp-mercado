@props(['variant' => 'success'])

@php
$colors = match ($variant) {
    'success' => ['bg' => 'var(--success)', 'text' => 'var(--on-success)'],
    'warning' => ['bg' => 'var(--warning)', 'text' => 'var(--on-warning)'],
    'danger' => ['bg' => 'var(--danger)', 'text' => 'var(--on-danger)'],
    default => ['bg' => 'var(--success)', 'text' => 'var(--on-success)'],
};
@endphp

<div
    {{ $attributes->merge(['class' => 'pointer-events-auto rounded-xl px-4 py-3 text-sm font-bold shadow-lg']) }}
    style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }};"
    x-data="{ show: false }"
    x-init="setTimeout(() => show = true, 50)"
    x-show="show"
    x-transition
>
    {{ $slot }}
</div>
