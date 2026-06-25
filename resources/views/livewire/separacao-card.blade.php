<div style="background:var(--surface);border-radius:var(--sep-radius);padding:16px;border:1px solid var(--border);box-shadow:var(--sep-shadow);transition:all 0.25s;position:relative;overflow:hidden;{{ $danger ? 'border-left:4px solid #ef4444;' : 'border-left:4px solid #f59e0b;' }}"
     @mouseenter="$el.style.transform='translateY(-2px)';$el.style.boxShadow='0 8px 30px rgba(0,0,0,0.08)'"
     @mouseleave="$el.style.transform='translateY(0)';$el.style.boxShadow='var(--sep-shadow)'">

    {{-- Progress --}}
    <div style="height:5px;background:var(--border);border-radius:999px;overflow:hidden;margin-bottom:12px;">
        <div style="height:100%;border-radius:999px;width:{{ $p['progresso'] }}%;{{ $danger ? 'background:linear-gradient(90deg,#ef4444,#f87171);' : 'background:linear-gradient(90deg,#f59e0b,#fbbf24);' }}transition:width 0.6s ease;"></div>
    </div>

    {{-- Header: ID + Status + Tempo --}}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:4px;">
        <div>
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span style="font-weight:800;font-size:18px;color:var(--text);">#{{ $p['id'] }}</span>
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;
                    {{ $p['ja_iniciou'] ? 'background:color-mix(in srgb,#f59e0b 12%,transparent);color:#d97706;' : 'background:color-mix(in srgb,#3b82f6 10%,transparent);color:#2563eb;' }}">
                    {{ $p['ja_iniciou'] ? 'Separando' : 'Novo' }}
                </span>
            </div>
            <div style="font-weight:600;font-size:14px;color:var(--muted);margin-top:2px;">{{ $p['cliente_nome'] }}</div>
        </div>
        <div style="font-weight:700;font-size:13px;display:flex;align-items:center;gap:4px;white-space:nowrap;
            {{ $danger ? 'color:#ef4444;' : ($p['minutos_atraso'] > 15 ? 'color:#d97706;' : 'color:var(--success);') }}">
            <i class="far fa-clock" style="font-size:12px;"></i> {{ $p['tempo_atraso'] }}
        </div>
    </div>

    {{-- Meta --}}
    <div style="display:flex;flex-wrap:wrap;gap:4px;margin:8px 0 10px;">
        <span style="background:var(--background);padding:3px 10px;border-radius:999px;font-size:12px;color:var(--muted);display:flex;align-items:center;gap:4px;">
            <i class="fas fa-basket-shopping" style="font-size:11px;"></i> {{ $p['total_itens'] }} itens
        </span>
        <span style="background:var(--background);padding:3px 10px;border-radius:999px;font-size:12px;color:var(--muted);display:flex;align-items:center;gap:4px;">
            <i class="fas fa-tag" style="font-size:11px;"></i> R$ {{ number_format($p['total'], 2, ',', '.') }}
        </span>
    </div>

    {{-- Status tags --}}
    <div style="display:flex;gap:3px;margin-bottom:10px;flex-wrap:wrap;">
        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:color-mix(in srgb,#22c55e 10%,transparent);color:#22c55e;">
            <i class="fas fa-check" style="font-size:8px;"></i> {{ $p['total_separados'] }} OK
        </span>
        @if ($p['total_substituidos'] > 0)
        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:color-mix(in srgb,#f59e0b 10%,transparent);color:#f59e0b;">
            <i class="fas fa-exchange-alt" style="font-size:8px;"></i> {{ $p['total_substituidos'] }} Subst.
        </span>
        @endif
        @if ($p['total_faltou'] > 0)
        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:color-mix(in srgb,#ef4444 10%,transparent);color:#ef4444;">
            <i class="fas fa-times" style="font-size:8px;"></i> {{ $p['total_faltou'] }} Faltou
        </span>
        @endif
        @if ($p['total_pendentes'] > 0)
        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:#e5e7eb;color:#6b7280;">
            <i class="fas fa-hourglass-half" style="font-size:8px;"></i> {{ $p['total_pendentes'] }} Pend.
        </span>
        @endif
    </div>

    {{-- Actions --}}
    <div style="display:grid;grid-template-columns:1fr auto;gap:8px;">
        <a href="/separacao/{{ $p['id'] }}" wire:navigate style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;border:0;border-radius:var(--sep-radius-sm);background:var(--sep-gradient);color:#fff;font-weight:700;font-size:14px;cursor:pointer;text-decoration:none;box-shadow:0 4px 14px color-mix(in srgb,#f59e0b 25%,transparent);transition:all 0.2s;"
           @mouseenter="$el.style.transform='translateY(-1px)';$el.style.boxShadow='0 6px 20px color-mix(in srgb,#f59e0b 35%,transparent)'"
           @mouseleave="$el.style.transform='translateY(0)';$el.style.boxShadow='0 4px 14px color-mix(in srgb,#f59e0b 25%,transparent)'">
            <i class="fas {{ $p['ja_iniciou'] ? 'fa-forward' : 'fa-play' }}" style="font-size:12px;"></i>
            {{ $p['ja_iniciou'] ? 'Continuar' : 'Iniciar' }}
        </a>
        <button wire:click.stop="cancelarPedido({{ $p['id'] }})" onclick="return confirm('Cancelar pedido #{{ $p['id'] }}?')" style="padding:10px 14px;border:1px solid color-mix(in srgb,#ef4444 30%,transparent);border-radius:var(--sep-radius-sm);background:color-mix(in srgb,#ef4444 6%,transparent);color:#ef4444;font-weight:700;font-size:13px;cursor:pointer;transition:all 0.2s;"
           @mouseenter="$el.style.background='color-mix(in srgb,#ef4444 12%,transparent)'"
           @mouseleave="$el.style.background='color-mix(in srgb,#ef4444 6%,transparent)'">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
