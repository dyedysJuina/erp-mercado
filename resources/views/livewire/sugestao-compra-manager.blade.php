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
                <h1 class="page-title">Compras</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Compras</span>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                @if ($this->pedidoCriado)
                    <a href="/pedidos" wire:navigate class="btn-sm btn-primary" style="padding:8px 16px;text-decoration:none;">
                        <i class="fas fa-eye"></i> Pedido #{{ $this->pedidoCriado }} criado
                    </a>
                @endif
                <a href="/pedidos" wire:navigate class="btn-sm btn-secondary" style="padding:8px 16px;text-decoration:none;">
                    <i class="fas fa-list"></i> Pedidos
                </a>
                <div class="status-online"><span class="status-dot"></span> Conectado</div>
            </div>
        </div>

        @php $resumo = $this->resumo; @endphp
        <div class="metrics-grid" style="grid-template-columns:repeat(5,1fr);">
            <div class="metric-card" style="padding:14px;border-left:4px solid var(--danger);">
                <div><span class="metric-label">Críticos</span><span class="metric-value" style="font-size:20px;color:var(--danger);">{{ $resumo['criticos'] }}</span><span class="metric-sub">Precisam comprar urgente</span></div>
            </div>
            <div class="metric-card" style="padding:14px;border-left:4px solid var(--warning);">
                <div><span class="metric-label">Atenção</span><span class="metric-value" style="font-size:20px;color:var(--warning);">{{ $resumo['media'] }}</span><span class="metric-sub">Vao acabar em breve</span></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div><span class="metric-label">Sugestão Total</span><span class="metric-value" style="font-size:20px;">{{ number_format($resumo['sugestao_total'], 0, ',', '.') }} un</span><span class="metric-sub">Total de itens sugeridos</span></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div><span class="metric-label">Custo Total</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($resumo['custo_total'], 2, ',', '.') }}</span><span class="metric-sub">Investimento necessario</span></div>
            </div>
            <div class="metric-card" style="padding:14px;border-left:4px solid var(--primary-600);">
                <div><span class="metric-label">Geral</span><span class="metric-value" style="font-size:20px;">{{ $resumo['total'] }}</span><span class="metric-sub">Produtos analisados</span></div>
            </div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="width:150px;margin:0;"><label>Loja *</label><select wire:model.live="lojaFiltro" style="height:38px;font-size:12px;"><option value="">Selecione...</option>@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
            <div class="field" style="width:160px;margin:0;"><label>Fornecedor *</label><select wire:model.live="fornecedorFiltro" style="height:38px;font-size:12px;"><option value="">Selecione...</option>@foreach ($this->fornecedores as $f)<option value="{{ $f['id'] }}">{{ $f['razao_social'] }}</option>@endforeach</select></div>
            <div class="field" style="width:150px;margin:0;"><label>Departamento</label><select wire:model.live="categoriaFiltro" style="height:38px;font-size:12px;"><option value="">Todos</option>@foreach ($this->categorias as $c)<option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>@endforeach</select></div>
            <div class="field" style="width:100px;margin:0;"><label>Base (dias)</label><select wire:model.live="diasBase" style="height:38px;font-size:12px;"><option value="15">15</option><option value="30">30</option><option value="60">60</option><option value="90">90</option></select></div>
            <div class="field" style="width:110px;margin:0;"><label>Lead Time</label><select wire:model.live="diasReposicao" style="height:38px;font-size:12px;"><option value="7">7 dias</option><option value="15">15 dias</option><option value="21">21 dias</option><option value="30">30 dias</option></select></div>
            <div class="field" style="width:120px;margin:0;"><label>Urgencia</label><select wire:model.live="filtroUrgencia" style="height:38px;font-size:12px;"><option value="">Todas</option><option value="critica">Critica</option><option value="media">Atencao</option></select></div>
            <button wire:click="gerarPedido" style="height:38px;padding:0 16px;border:0;border-radius:8px;background:var(--success);color:#fff;font-weight:700;font-size:12px;cursor:pointer;white-space:nowrap;" @disabled(!$this->lojaFiltro || !$this->fornecedorFiltro)>
                <i class="fas fa-file-invoice"></i> Gerar Pedido
            </button>
        </div>

        <div class="data-table-wrap">
            <div style="overflow-x:auto;">
                @php $sugestoes = $this->sugestoes(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr>
                        <th class="data-table-th text-center" style="width:32px;">
                            <input type="checkbox" wire:model.live="selecionarTodos" style="cursor:pointer;">
                        </th>
                        <th class="data-table-th text-left">Produto</th>
                        <th class="data-table-th text-left">Depto</th>
                        <th class="data-table-th text-right">Venda Media</th>
                        <th class="data-table-th text-right">Estoque</th>
                        <th class="data-table-th text-right">Minimo</th>
                        <th class="data-table-th text-center">Dias</th>
                        <th class="data-table-th text-right">Sugerido</th>
                        <th class="data-table-th text-right">Custo</th>
                        <th class="data-table-th text-center">Status</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($sugestoes as $s)
                            <tr class="data-table-tr" style="{{ $s['urgencia'] === 'critica' ? 'background:color-mix(in srgb,var(--danger)4%,transparent);' : ($s['urgencia'] === 'media' ? 'background:color-mix(in srgb,var(--warning)3%,transparent);' : '') }}">
                                <td class="data-table-td text-center">
                                    <input type="checkbox" wire:model.live="selecionados" value="{{ $s['variacao_id'] }}" style="cursor:pointer;">
                                </td>
                                <td class="data-table-td text-muted" style="font-size:10px;">{{ $s['categoria'] }}</td>
                                <td class="data-table-td text-right font-semibold">{{ number_format($s['venda_media'], 3, ',', '.') }}/dia</td>
                                <td class="data-table-td text-right font-bold {{ $s['estoque_atual'] <= $s['estoque_min'] ? 'text-danger' : '' }}">{{ number_format($s['estoque_atual'], 3, ',', '.') }}</td>
                                <td class="data-table-td text-right text-muted">{{ number_format($s['estoque_min'], 3, ',', '.') }}</td>
                                <td class="data-table-td text-center font-bold" style="{{ $s['dias_ate_zero'] <= 7 ? 'color:var(--danger);' : ($s['dias_ate_zero'] <= 15 ? 'color:var(--warning);' : 'color:var(--muted);') }}">{{ $s['dias_ate_zero'] }} dias</td>
                                <td class="data-table-td text-right font-bold" style="color:var(--primary-600);">{{ number_format($s['sugestao'], 0, ',', '.') }} un</td>
                                <td class="data-table-td text-right text-muted">R$ {{ number_format($s['custo'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-center">
                                    @if ($s['urgencia'] === 'critica')
                                        <span class="badge-sm badge-inativo" style="font-size:9px;">Critico</span>
                                    @elseif ($s['urgencia'] === 'media')
                                        <span class="badge-sm badge-warning" style="font-size:9px;">Atencao</span>
                                    @else
                                        <span class="badge-sm badge-ativo" style="font-size:9px;">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="data-table-empty">Nenhum produto encontrado. Selecione uma loja com vendas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>