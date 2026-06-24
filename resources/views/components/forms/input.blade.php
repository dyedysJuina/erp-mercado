@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
])

<div>
    @if ($label)
        <x-forms.label for="{{ $name }}">{{ $label }}</x-forms.label>
    @endif
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'input-field mt-1']) }}
    >
    <x-forms.error name="{{ $name }}" />
</div>