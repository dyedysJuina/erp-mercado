@props([
    'label' => '',
    'name' => '',
    'placeholder' => '',
    'rows' => 3,
])

<div>
    @if ($label)
        <x-forms.label for="{{ $name }}">{{ $label }}</x-forms.label>
    @endif
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'input-field mt-1']) }}
    >{{ $slot }}</textarea>
    <x-forms.error name="{{ $name }}" />
</div>