@if ($paginator->hasPages())
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:12px;">
        <span style="color:var(--muted);font-size:11px;">
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </span>
        <div style="display:flex;gap:4px;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;color:var(--muted);font-size:10px;font-weight:700;opacity:0.5;">&laquo;</span>
            @else
                <button type="button" wire:click="previousPage" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--surface);color:var(--text);font-size:10px;font-weight:700;cursor:pointer;">&laquo;</button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span style="padding:6px 10px;color:var(--muted);font-size:10px;font-weight:700;">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="padding:6px 10px;border-radius:6px;background:var(--primary-600);color:#fff;font-size:10px;font-weight:800;">{{ $page }}</span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }})" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--surface);color:var(--text);font-size:10px;font-weight:700;cursor:pointer;">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;background:var(--surface);color:var(--text);font-size:10px;font-weight:700;cursor:pointer;">&raquo;</button>
            @else
                <span style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;color:var(--muted);font-size:10px;font-weight:700;opacity:0.5;">&raquo;</span>
            @endif
        </div>
    </div>
@endif
