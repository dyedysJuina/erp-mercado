@php
    $progress = $p['progresso'];
    $isLate = ($type ?? 'recent') === 'late';
    $isUrgent = ($type ?? 'recent') === 'urgent';
    
    // Status text / SLA Mock
    $sla = $p['created_at']; // fallback
    if ($isLate) {
        $slaText = "Atrasado (Limite " . date('H:i', strtotime($p['created_at']) + 1800) . ")";
        $stripClass = "red";
        $badgeStyle = "background: #fee2e2; color: #ef4444;";
    } elseif ($isUrgent) {
        $slaText = "Próximo (Limite " . date('H:i', strtotime($p['created_at']) + 2700) . ")";
        $stripClass = "yellow";
        $badgeStyle = "background: #fef3c7; color: #d97706;";
    } else {
        $slaText = "Recente (SLA " . date('H:i', strtotime($p['created_at']) + 3600) . ")";
        $stripClass = "blue";
        $badgeStyle = "background: #dbeafe; color: #2563eb;";
    }
@endphp
<div class="order-card"
     style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,0.05);border:1px solid #f3f4f6;position:relative;overflow:hidden;margin-bottom:4px;"
     @mouseenter="$el.style.transform='translateY(-2px)';$el.style.boxShadow='0 8px 24px rgba(0,0,0,0.06)';"
     @mouseleave="$el.style.transform='translateY(0)';$el.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)';">
    
    {{-- Left color strip --}}
    <div class="strip {{ $stripClass }}" style="position:absolute;top:0;left:0;width:4px;height:100%;"></div>

    {{-- SLA Badge + Order ID --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;padding-left:4px;">
        <span style="font-size:11px;font-weight:800;padding:3px 10px;border-radius:6px;{!! $badgeStyle !!}">
            {{ $slaText }}
        </span>
        <span style="color:#9ca3af;font-size:13px;font-weight:700;">#{{ $p['id'] }}</span>
    </div>

    {{-- Customer Name --}}
    <h2 style="font-size:18px;font-weight:800;color:#1f2937;margin:0 0 4px 4px;line-height:1.2;">
        {{ $p['cliente_nome'] }}
    </h2>

    {{-- Details (Itens • Tipo de entrega) --}}
    <p style="color:#6b7280;font-size:13px;margin:0 0 16px 4px;font-weight:500;display:flex;align-items:center;gap:6px;">
        <i class="fa-solid fa-box-open" style="color:#9ca3af;font-size:12px;"></i>
        <span>{{ $p['total_itens'] }} itens</span>
        <span>•</span>
        <span>{{ ($p['tipo_entrega'] ?? 'retirada') === 'retirada' ? '🛍️ Retirada' : '🛵 Entrega' }}</span>
        <span>•</span>
        <span>{{ $progress }}%</span>
    </p>

    {{-- Actions Grid --}}
    <div style="display:grid;grid-template-columns:1fr auto;gap:8px;padding-left:4px;">
        <a href="/separacao/{{ $p['id'] }}" wire:navigate class="btn"
           style="flex:1;text-decoration:none;font-weight:700;font-size:14px;border-radius:8px;padding:11px 16px;text-align:center;display:flex;align-items:center;justify-content:center;gap:6px;transition:all 0.2s;
                  {{ !$p['ja_iniciou'] ? 'background:#1f2937;color:#fff;' : 'background:#f3f4f6;color:#4b5563;border:1px solid #e5e7eb;' }}"
           @mouseenter="{{ !$p['ja_iniciou'] ? '$el.style.background=\'#111827\'' : '$el.style.background=\'#e5e7eb\'' }}"
           @mouseleave="{{ !$p['ja_iniciou'] ? '$el.style.background=\'#1f2937\'' : '$el.style.background=\'#f3f4f6\'' }}">
            <i class="fas {{ $p['ja_iniciou'] ? 'fa-forward' : 'fa-play' }}" style="font-size:11px;"></i>
            {{ !$p['ja_iniciou'] ? 'Iniciar Coleta' : 'Continuar Coleta' }}
        </a>
        <button wire:click.stop="cancelarPedido({{ $p['id'] }})" onclick="return confirm('Cancelar pedido #{{ $p['id'] }}?')"
                style="background:none;border:none;color:#9ca3af;padding:10px 14px;cursor:pointer;font-size:16px;border-radius:8px;transition:all 0.2s;"
                @mouseenter="$el.style.background='#fee2e2';$el.style.color='#ef4444';"
                @mouseleave="$el.style.background='none';$el.style.color='#9ca3af';">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
