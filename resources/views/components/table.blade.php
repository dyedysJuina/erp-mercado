@props(['headers' => [], 'rows' => [], 'actions' => false])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-xl border']) }} style="border-color: var(--border);">
    <table class="w-full text-sm">
        <thead>
            <tr style="background-color: var(--background);">
                @foreach ($headers as $header)
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--muted);">{{ $header }}</th>
                @endforeach
                @if ($actions)
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider" style="color: var(--muted);">Ações</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y" style="border-color: var(--border);">
            @forelse ($rows as $row)
                <tr class="transition" style="background-color: var(--surface);" wire:key="{{ $loop->index }}">
                    @foreach ($row as $cell)
                        <td class="px-4 py-3" style="color: var(--text);">{{ $cell }}</td>
                    @endforeach
                    @if ($actions)
                        <td class="px-4 py-3 text-right">{{ $actions($loop->index, $row) }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}" class="px-4 py-12 text-center" style="color: var(--muted);">
                        Nenhum registro encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>