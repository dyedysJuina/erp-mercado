@props([
    'label' => '',
    'name' => '',
    'checked' => false,
])

<label class="flex items-center gap-2">
    <input
        type="checkbox"
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $checked ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'h-4 w-4 rounded']) }}
        style="border-color: var(--border); color: var(--primary-600);"
    >
    <span class="text-sm" style="color: var(--text);">{{ $label }}</span>
</label>
<x-forms.error name="{{ $name }}" />