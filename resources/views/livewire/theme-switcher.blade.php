<?php
/**
 * @var \App\Livewire\ThemeSwitcher $this
 * @var array $temas
 */
?>

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="theme-hover flex w-full items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition" style="color: var(--sidebar-muted);">
        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
        </svg>
        <span class="flex-1 text-left truncate">{{ $temas[$current]['name'] ?? $current }}</span>
        <svg class="size-3 shrink-0 transition" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute bottom-full left-0 right-0 mb-1 rounded-xl border p-1 shadow-lg" style="background-color: var(--surface); border-color: var(--border);">
        @foreach ($temas as $slug => $tema)
            <button
                wire:click="setTheme('{{ $slug }}')"
                @click="open = false"
                class="theme-hover flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition"
                style="color: {{ $current === $slug ? 'var(--primary-600)' : 'var(--text)' }}; {{ $current === $slug ? 'font-weight: 700' : '' }};"
            >
                <span class="inline-block size-3 rounded-full border" style="border-color: var(--border); background-color: {{ $tema['color'] }};"></span>
                {{ $tema['name'] }}
            </button>
        @endforeach
        <hr class="my-1" style="border-color: var(--border);">
        <a href="{{ route('admin.themes') }}" wire:navigate class="theme-hover flex items-center gap-2 rounded-lg px-3 py-2 text-xs transition" style="color: var(--muted);">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
            </svg>
            Gerenciar temas
        </a>
    </div>
</div>
