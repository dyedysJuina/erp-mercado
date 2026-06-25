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
                <h1 class="page-title">Pedidos de Compra</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Pedidos</span>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <a href="/compras" wire:navigate class="btn-sm btn-primary" style="padding:8px 16px;text-decoration:none;">
                    <i class="fas fa-plus"></i> Novo Pedido
                </a>
                <div class="status-online"><span class="status-dot"></span> Conectado</div>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-shopping-bag"></i></div><div><span class="metric-label">Total Pedidos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalPedidos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-dollar-sign"></i></div><div><span class="metric-label">Total Gasto</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->totalGasto, 2, ',', '.') }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-clock"></i></div><div><span class="metric-label">Aguardando</span><span class="metric-value" style="font-size:20px;">{{ $this->pedidosPendentes }}</span></div></div>
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

        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-list"></i> Pedidos</span></div>
            <div style="overflow-x:auto;">
                @php $pedidos = $this->pedidos(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr>
                        <th class="data-table-th text-left">#</th>
                        <th class="data-table-th text-left">Fornecedor</th>
                        <th class="data-table-th text-left">Data</th>
                        <th class="data-table-th text-right">Valor</th>
                        <th class="data-table-th text-center" style="width:160px;">Progresso</th>
                        <th class="data-table-th text-center">Status</th>
                        <th class="data-table-th text-center" style="width:100px;">Ações</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($pedidos as $p)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold">#{{ $p['id'] }}</td>
                                <td class="data-table-td font-semibold">{{ $p['fornecedor']['razao_social'] ?? '—' }}</td>
                                <td class="data-table-td text-muted">{{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}</td>
                                <td class="data-table-td text-right font-bold">R$ {{ number_format($p['total_pedido'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-center">
                                    @php
                                        $totalPedido = (float)($p['total_pedido_qtd'] ?? 0);
                                        $totalRecebido = (float)($p['total_recebido_qtd'] ?? 0);
                                        $pct = $totalPedido > 0 ? round(($totalRecebido / $totalPedido) * 100) : 0;
                                    @endphp
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <div style="flex:1;height:6px;border-radius:3px;background:color-mix(in srgb,var(--text)8%,var(--surface));overflow:hidden;">
                                            <div style="height:100%;border-radius:3px;width:{{ $pct }}%;background:{{ $pct >= 100 ? 'var(--success)' : 'var(--warning)' }};"></div>
                                        </div>
                                        <span style="font-size:10px;font-weight:600;white-space:nowrap;min-width:36px;">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="data-table-td text-center">
                                    @php
                                        $label = ['rascunho'=>'Rascunho','enviado'=>'Enviado','parcialmente_recebido'=>'Parcial','recebido'=>'Recebido','cancelado'=>'Cancelado'][$p['status']] ?? $p['status'];
                                        $sc = ['rascunho'=>'badge-inativo','enviado'=>'badge-ativo','parcialmente_recebido'=>'badge-warning','recebido'=>'badge-ativo','cancelado'=>'badge-inativo'][$p['status']] ?? 'badge-inativo';
                                    @endphp
                                    <span class="badge-sm {{ $sc }}">{{ $label }}</span>
                                </td>
                                <td class="data-table-td text-center" style="white-space:nowrap;">
                                    <a href="/pedidos/{{ $p['id'] }}" wire:navigate class="btn-sm btn-secondary" style="padding:4px 10px;font-size:10px;text-decoration:none;">
                                        <i class="fas fa-eye"></i> Detalhe
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="data-table-empty">Nenhum pedido encontrado.</td></tr>
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
    </div>
</div>
