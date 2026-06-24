@props([
    'label' => '',
    'name' => '',
    'placeholder' => '',
    'options' => [],
])

<div>
    @if ($label)
        <x-forms.label for="{{ $name }}">{{ $label }}</x-forms.label>
    @endif
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'input-field mt-1']) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>
    <x-forms.error name="{{ $name }}" />
</div>