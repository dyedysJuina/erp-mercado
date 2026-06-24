<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad">
        <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Pedidos de Compra</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Pedidos</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-shopping-bag"></i></div><div><span class="metric-label">Total Pedidos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalPedidos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-dollar-sign"></i></div><div><span class="metric-label">Total Gasto</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->totalGasto, 2, ',', '.') }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-clock"></i></div><div><span class="metric-label">Pendentes</span><span class="metric-value" style="font-size:20px;">{{ $this->pedidosPendentes }}</span></div></div>
            <div class="metric-card"><div class="metric-icon blue" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-calendar"></i></div><div><span class="metric-label">Este Mês</span><span class="metric-value" style="font-size:20px;">{{ $this->pedidosMes }} ped. / R$ {{ number_format($this->gastoMes, 2, ',', '.') }}</span></div></div>
        </div>
        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="flex:1;min-width:200px;margin:0;"><label style="font-size:10px;">Buscar</label><input wire:model.live.debounce.300ms="busca" placeholder="#ID ou fornecedor..." style="height:38px;font-size:13px;"></div>
            <div class="field" style="width:140px;margin:0;"><label style="font-size:10px;">Status</label><select wire:model.live="filtroStatus" style="height:38px;font-size:12px;"><option value="">Todos</option><option value="rascunho">Rascunho</option><option value="enviado">Enviado</option><option value="parcialmente_recebido">Parcial</option><option value="recebido">Recebido</option><option value="cancelado">Cancelado</option></select></div>
            <div class="field" style="width:200px;margin:0;"><label style="font-size:10px;">Fornecedor</label><select wire:model.live="filtroFornecedor" style="height:38px;font-size:12px;"><option value="">Todos</option>@foreach ($this->fornecedores as $f)<option value="{{ $f['id'] }}">{{ $f['razao_social'] }}</option>@endforeach</select></div>
            <div class="field" style="width:140px;margin:0;"><label style="font-size:10px;">De</label><input wire:model="dataInicio" type="date" style="height:38px;font-size:12px;"></div>
            <div class="field" style="width:140px;margin:0;"><label style="font-size:10px;">Até</label><input wire:model="dataFim" type="date" style="height:38px;font-size:12px;"></div>
            <button wire:click="limparFiltros" class="btn-sm btn-secondary" style="height:38px;">Limpar</button>
        </div>
        <div class="data-table-wrap" style="margin-bottom:20px;">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-list"></i> Pedidos</span></div>
            <div style="overflow-x:auto;">
                @php $pedidos = $this->pedidos(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr><th class="data-table-th text-left">#</th><th class="data-table-th text-left">Fornecedor</th><th class="data-table-th text-left">Data</th><th class="data-table-th text-right">Valor</th><th class="data-table-th text-center">Status</th><th class="data-table-th text-center" style="width:100px;">Ações</th></tr></thead>
                    <tbody>
                        @forelse ($pedidos as $p)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold">#{{ $p['id'] }}</td>
                                <td class="data-table-td font-semibold">{{ $p['fornecedor']['razao_social'] ?? '—' }}</td>
                                <td class="data-table-td text-muted">{{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}</td>
                                <td class="data-table-td text-right font-bold">R$ {{ number_format($p['total_pedido'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-center">@php $sc = ['rascunho'=>'badge-inativo','enviado'=>'badge-ativo','parcialmente_recebido'=>'badge-warning','recebido'=>'badge-ativo','cancelado'=>'badge-inativo'][$p['status']] ?? 'badge-inativo'; @endphp<span class="badge-sm {{ $sc }}">{{ $p['status'] }}</span></td>
                                <td class="data-table-td text-center"><a href="/compras?pedidoId={{ $p['id'] }}" class="btn-sm btn-secondary" style="padding:4px 12px;font-size:10px;text-decoration:none;"><i class="fas fa-eye"></i> Abrir</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="data-table-empty">Nenhum pedido encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pedidos->hasPages())
                <div style="padding:12px 16px;border-top:1px solid var(--border);">
                    {{ $pedidos->links('livewire.pagination-custom') }}
                </div>
            @endif
        </div>
        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-chart-bar"></i> Gasto por Fornecedor (Top 5)</span></div>
            <div style="padding:16px 18px;">
                @forelse ($this->gastoPorFornecedor as $g)
                    @php $pct = $this->totalGasto > 0 ? round(($g['total'] / $this->totalGasto) * 100, 1) : 0; @endphp
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                        <span style="font-weight:600;font-size:12px;color:var(--text);min-width:180px;">{{ $g['fornecedor']['razao_social'] ?? '—' }}</span>
                        <div style="flex:1;height:8px;border-radius:4px;background:color-mix(in srgb,var(--text)8%,var(--surface));overflow:hidden;"><div style="height:100%;border-radius:4px;width:{{ $pct }}%;background:color-mix(in srgb,#6366f1,#10b981);"></div></div>
                        <span style="font-weight:700;font-size:12px;color:var(--text);min-width:100px;text-align:right;">R$ {{ number_format($g['total'], 2, ',', '.') }}</span>
                        <span style="font-size:11px;color:var(--muted);min-width:40px;text-align:right;">{{ $pct }}%</span>
                    </div>
                @empty
                    <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum pedido registrado.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
