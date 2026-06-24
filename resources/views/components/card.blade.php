@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'card' . ($padding ? '' : ' !p-0')]) }}>
    {{ $slot }}
</div>