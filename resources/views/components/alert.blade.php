@props(['variant' => 'success', 'dismissible' => false])

@php
$colors = match ($variant) {
    'success' => ['bg' => 'color-mix(in srgb, var(--success) 8%, transparent)', 'text' => 'var(--success)'],
    'warning' => ['bg' => 'color-mix(in srgb, var(--warning) 8%, transparent)', 'text' => 'var(--warning)'],
    'danger' => ['bg' => 'color-mix(in srgb, var(--danger) 8%, transparent)', 'text' => 'var(--danger)'],
    'info' => ['bg' => 'color-mix(in srgb, var(--primary-500) 8%, transparent)', 'text' => 'var(--primary-700)'],
    default => ['bg' => 'color-mix(in srgb, var(--success) 8%, transparent)', 'text' => 'var(--success)'],
};
@endphp

<div
    {{ $attributes->merge(['class' => 'rounded-xl border px-4 py-3 text-sm font-medium']) }}
    style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; border-color: {{ $colors['text'] }}30;"
    x-data="{ show: true }"
    x-show="show"
>
    <div class="flex items-start justify-between gap-3">
        <span>{{ $slot }}</span>
        @if ($dismissible)
            <button @click="show = false" type="button" class="shrink-0 opacity-60 hover:opacity-100">&times;</button>
        @endif
    </div>
</div>