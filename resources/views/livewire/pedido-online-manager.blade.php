<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad">
        <div class="header-row">
            <div>
                <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                <h1 class="page-title">Central de Pedidos Online</h1>
                <div class="breadcrumb">
                    <span>Inicio</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Pedidos Online</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        @php $t = $this->totais; @endphp
        <div class="metrics-grid" style="grid-template-columns:repeat(6,1fr);">
            <div class="metric-card" style="padding:10px;"><div><span class="metric-label" style="font-size:9px;">Pedidos Hoje</span><span class="metric-value" style="font-size:18px;">{{ $t['hoje'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;border-left:3px solid var(--primary-500);"><div><span class="metric-label" style="font-size:9px;">Novos</span><span class="metric-value" style="font-size:18px;color:var(--primary-600);">{{ $t['novos'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;border-left:3px solid var(--warning);"><div><span class="metric-label" style="font-size:9px;">Em Separacao</span><span class="metric-value" style="font-size:18px;color:var(--warning);">{{ $t['separacao'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;border-left:3px solid var(--success);"><div><span class="metric-label" style="font-size:9px;">Prontos</span><span class="metric-value" style="font-size:18px;color:var(--success);">{{ $t['prontos'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;border-left:3px solid var(--danger);"><div><span class="metric-label" style="font-size:9px;">Atrasados</span><span class="metric-value" style="font-size:18px;color:var(--danger);">{{ $t['atrasados'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;"><div><span class="metric-label" style="font-size:9px;">Faturamento</span><span class="metric-value" style="font-size:18px;">R$ {{ number_format($t['faturamento'], 0, ',', '.') }}</span></div></div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="flex:1;min-width:180px;margin:0;"><label>Buscar</label><input wire:model.live.debounce.300ms="busca" placeholder="Codigo ou cliente..." style="height:34px;font-size:12px;"></div>
            <div class="field" style="width:130px;margin:0;"><label>Status</label><select wire:model.live="filtroStatus" style="height:34px;font-size:11px;"><option value="">Todos</option><option value="recebido">Recebido</option><option value="confirmado">Confirmado</option><option value="em_separacao">Em Separacao</option><option value="pronto_retirada">Pronto Retirada</option><option value="pronto_entrega">Pronto Entrega</option><option value="entregue">Entregue</option><option value="cancelado">Cancelado</option></select></div>
            <div class="field" style="width:110px;margin:0;"><label>Periodo</label><select wire:model.live="periodo" style="height:34px;font-size:11px;"><option value="hoje">Hoje</option><option value="semana">Semana</option><option value="personalizado">Personalizado</option></select></div>
            @if ($periodo === 'personalizado')
                <div class="field" style="width:100px;margin:0;"><input wire:model="dataInicio" type="date" style="height:34px;font-size:11px;"></div>
                <div class="field" style="width:100px;margin:0;"><input wire:model="dataFim" type="date" style="height:34px;font-size:11px;"></div>
            @endif
            <div class="field" style="width:110px;margin:0;"><label>Entrega</label><select wire:model.live="filtroEntrega" style="height:34px;font-size:11px;"><option value="">Todas</option><option value="retirada">Retirada</option><option value="entrega">Entrega</option></select></div>
        </div>

        @php $lista = $this->listagem(); @endphp
        <div class="data-table-wrap">
            @if (count($lista) > 0)
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;padding:12px;">
                    @foreach ($lista as $p)
                        @php $st = $this->statusDoPedido($p); @endphp
                        <a href="/pedidos-online/{{ $p->id }}" wire:navigate style="text-decoration:none;color:inherit;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:14px;transition:all 0.15s;{{ $st['atrasado'] ? 'border-left:4px solid var(--danger);' : '' }}" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.boxShadow='none'">
                            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                                <div>
                                    <span style="font-weight:800;font-size:14px;color:var(--text);">#{{ $p->id }}</span>
                                    <span style="font-size:11px;color:var(--muted);font-family:monospace;margin-left:4px;">{{ $p->codigo }}</span>
                                </div>
                                @php $badgeClass = match($p->status) { 'recebido' => 'badge-ativo', 'em_separacao' => 'badge-warning', 'pronto_retirada','pronto_entrega','entregue' => 'badge-ativo', 'cancelado' => 'badge-inativo', default => 'badge-warning' }; @endphp
                                <span class="badge-sm {{ $badgeClass }}" style="font-size:8px;">{{ $p->status }}</span>
                            </div>
                            <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px;">{{ $p->cliente?->nome ?? '—' }}</div>
                            <div style="display:flex;gap:12px;font-size:11px;color:var(--muted);margin-bottom:6px;">
                                <span><i class="fas fa-box"></i> {{ $p->itens->count() }} itens</span>
                                <span><i class="fas fa-dollar-sign"></i> R$ {{ number_format($p->total, 2, ',', '.') }}</span>
                                <span><i class="fas {{ $p->tipo_entrega === 'entrega' ? 'fa-truck' : 'fa-store' }}"></i> {{ $p->tipo_entrega }}</span>
                            </div>
                            @if ($st['atrasado'])
                                <div style="font-size:11px;font-weight:700;color:var(--danger);display:flex;align-items:center;gap:4px;">
                                    <i class="fas fa-exclamation-circle"></i> ATRASADO {{ $st['minutos_atraso'] }}min
                                </div>
                            @endif
                            @if ($p->separador)
                                <div style="font-size:10px;color:var(--muted);margin-top:4px;">
                                    <i class="fas fa-user-cog"></i> Sep: {{ $p->separador->name }}
                                    @if ($p->entregador) | <i class="fas fa-motorcycle"></i> Ent: {{ $p->entregador->name }} @endif
                                </div>
                            @endif
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
                                <span style="font-size:9px;color:var(--muted);">{{ $p->created_at->format('d/m/Y H:i') }}</span>
                                <span style="font-size:10px;font-weight:600;color:var(--primary-600);">Ver detalhe →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:40px;color:var(--muted);">
                    <i class="fas fa-inbox" style="font-size:40px;display:block;margin-bottom:10px;opacity:0.3;"></i>
                    <p style="font-size:16px;font-weight:600;color:var(--text);margin:0 0 4px;">Nenhum pedido encontrado</p>
                    <p style="font-size:13px;margin:0;">Os pedidos feitos no site aparecerao aqui.</p>
                </div>
            @endif
            @if ($lista->hasPages())
                <div style="padding:12px 16px;border-top:1px solid var(--border);">{{ $lista->links('livewire.pagination-custom') }}</div>
            @endif
        </div>
    </div>
</div>
