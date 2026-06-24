@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'flex items-center gap-1.5 text-sm']) }}>
    @foreach ($items as $label => $url)
        @if ($loop->last)
            <span class="font-semibold" style="color: var(--text);">{{ $label }}</span>
        @else
            <a href="{{ $url }}" class="transition hover:underline" style="color: var(--muted);">{{ $label }}</a>
            <svg class="size-3.5" style="color: var(--muted);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        @endif
    @endforeach
</nav>