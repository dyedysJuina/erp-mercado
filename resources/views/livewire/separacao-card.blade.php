<div style="background:var(--surface);border-radius:14px;border:1px solid var(--border);{{ $danger ? 'border-left:4px solid var(--danger);' : 'border-left:4px solid var(--warning);' }}padding:14px;box-shadow:0 2px 12px rgba(0,0,0,0.03);transition:box-shadow 0.2s,transform 0.15s;" @mouseenter="$el.style.boxShadow='0 4px 20px rgba(0,0,0,0.06)';$el.style.transform='translateY(-1px)'" @mouseleave="$el.style.boxShadow='0 2px 12px rgba(0,0,0,0.03)';$el.style.transform='translateY(0)'">

    {{-- Linha 1: ID + data | tempo --}}
    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px;">
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <span style="font-size:12px;font-weight:800;color:{{ $danger ? 'var(--danger)' : 'var(--warning)' }};">#{{ $p['id'] }}</span>
            <span style="font-size:10px;color:color-mix(in srgb,var(--muted)60%,transparent);">{{ $p['created_at'] }}</span>
        </div>
        <span style="font-size:11px;font-weight:600;display:flex;align-items:center;gap:4px;white-space:nowrap;
            {{ $danger ? 'color:var(--danger);background:color-mix(in srgb,var(--danger)8%,transparent);padding:2px 8px;border-radius:20px;' : 'color:var(--muted);' }}">
            <i class="far fa-clock" style="font-size:10px;"></i> {{ $p['tempo_atraso'] }}
        </span>
    </div>

    {{-- Cliente --}}
    <h4 style="margin:0 0 6px;font-weight:700;font-size:15px;color:var(--text);">{{ $p['cliente_nome'] }}</h4>

    {{-- Info adicional --}}
    <div style="font-size:12px;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:12px;">
        <span><i class="fas fa-shopping-cart" style="margin-right:4px;"></i> {{ $p['total_itens'] }} itens</span>
        <span><strong style="color:var(--text);">R$ {{ number_format($p['total'], 2, ',', '.') }}</strong></span>
    </div>

    {{-- Progress bar segmentada --}}
    @php
        $t = max(1, $p['total_itens']);
        $pOk = round(($p['total_separados'] / $t) * 100);
        $pFaltou = round(($p['total_faltou'] / $t) * 100);
        $pSubst = round(($p['total_substituidos'] / $t) * 100);
        $pPend = 100 - $pOk - $pFaltou - $pSubst;
    @endphp
    <div style="margin-bottom:8px;">
        <div style="height:6px;border-radius:3px;display:flex;gap:2px;overflow:hidden;">
            @if ($pOk > 0) <div style="height:100%;width:{{ $pOk }}%;background:var(--success);border-radius:3px;transition:width 0.3s;"></div> @endif
            @if ($pSubst > 0) <div style="height:100%;width:{{ $pSubst }}%;background:var(--warning);border-radius:3px;transition:width 0.3s;"></div> @endif
            @if ($pFaltou > 0) <div style="height:100%;width:{{ $pFaltou }}%;background:var(--danger);border-radius:3px;transition:width 0.3s;"></div> @endif
            @if ($pPend > 0) <div style="height:100%;flex:1;background:color-mix(in srgb,var(--text)8%,transparent);border-radius:3px;"></div> @endif
        </div>
    </div>

    {{-- Status tags --}}
    <div style="display:flex;gap:4px;margin-bottom:10px;flex-wrap:wrap;">
        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:color-mix(in srgb,var(--success)10%,transparent);color:var(--success);">
            <i class="fas fa-check" style="font-size:8px;margin-right:2px;"></i> {{ $p['total_separados'] }} OK
        </span>
        @if ($p['total_substituidos'] > 0)
            <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:color-mix(in srgb,var(--warning)10%,transparent);color:var(--warning);">
                <i class="fas fa-exchange-alt" style="font-size:8px;margin-right:2px;"></i> {{ $p['total_substituidos'] }} Subst.
            </span>
        @endif
        @if ($p['total_faltou'] > 0)
            <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:color-mix(in srgb,var(--danger)10%,transparent);color:var(--danger);">
                <i class="fas fa-times" style="font-size:8px;margin-right:2px;"></i> {{ $p['total_faltou'] }} Faltou
            </span>
        @endif
        @if ($p['total_pendentes'] > 0)
            <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;background:color-mix(in srgb,var(--text)6%,transparent);color:var(--muted);">
                <i class="fas fa-hourglass-half" style="font-size:8px;margin-right:2px;"></i> {{ $p['total_pendentes'] }} Pend.
            </span>
        @endif
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:8px;">
        <a href="/separacao/{{ $p['id'] }}" wire:navigate style="display:inline-flex;align-items:center;justify-content:center;gap:6px;flex:1;padding:10px 16px;border:0;border-radius:10px;background:linear-gradient(135deg,var(--warning),color-mix(in srgb,var(--warning)80%,#000));color:#fff;font-weight:800;font-size:13px;cursor:pointer;text-decoration:none;box-shadow:0 4px 14px color-mix(in srgb,var(--warning)25%,transparent);transition:transform 0.15s,box-shadow 0.15s;" @mouseenter="$el.style.transform='translateY(-1px)';$el.style.boxShadow='0 4px 20px color-mix(in srgb,var(--warning)35%,transparent)'" @mouseleave="$el.style.transform='translateY(0)';$el.style.boxShadow='0 4px 14px color-mix(in srgb,var(--warning)25%,transparent)'">
            <i class="fas {{ $p['ja_iniciou'] ? 'fa-forward' : 'fa-play' }}" style="font-size:12px;"></i>
            {{ $p['ja_iniciou'] ? 'Continuar' : 'Iniciar' }}
        </a>
        <button wire:click.stop="cancelarPedido({{ $p['id'] }})" onclick="return confirm('Cancelar pedido #{{ $p['id'] }}?')" style="padding:10px 14px;border:1px solid color-mix(in srgb,var(--danger)30%,transparent);border-radius:10px;background:color-mix(in srgb,var(--danger)6%,transparent);color:var(--danger);font-weight:700;font-size:12px;cursor:pointer;transition:background 0.15s;" @mouseenter="$el.style.background='color-mix(in srgb,var(--danger)12%,transparent)'" @mouseleave="$el.style.background='color-mix(in srgb,var(--danger)6%,transparent)'">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>