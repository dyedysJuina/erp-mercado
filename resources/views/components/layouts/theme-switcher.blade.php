@props(['current' => 'verde'])

@php
$temas = [
    'verde' => 'Verde Operacional',
    'azul' => 'Azul Profissional',
    'ambar' => 'Âmbar Mercado',
    'escuro' => 'Escuro',
];
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex w-full items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-sidebar-muted transition hover:bg-sidebar-active-bg/5">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
        </svg>
        <span class="flex-1 text-left">Tema: {{ $temas[$current] ?? $current }}</span>
        <svg class="size-3 transition" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="absolute bottom-full left-0 right-0 mb-1 rounded-xl border border-border bg-surface p-1 shadow-lg" x-cloak>
        @foreach ($temas as $key => $label)
            <button
                wire:click="setTheme('{{ $key }}')"
                @click="open = false"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition hover:bg-primary/10 {{ $current === $key ? 'font-bold text-primary' : 'text-text' }}"
            >
                <span class="inline-block size-3 rounded-full border border-border" style="background-color: var(--color-primary-500)"></span>
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>
