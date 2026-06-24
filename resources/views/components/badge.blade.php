@props(['variant' => 'success'])

@php
$class = match ($variant) {
    'success' => 'badge-success',
    'warning' => 'badge-warning',
    'danger' => 'badge-danger',
    default => 'badge-success',
};
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</span>