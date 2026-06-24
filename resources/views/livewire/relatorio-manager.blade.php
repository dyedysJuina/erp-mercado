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
        <span class="toast-icon"><i class="fas fa-check"></i></span>
        <span x-text="msg"></span>
    </div>

    <div class="main-content-pad">

        {{-- HEADER --}}
        <div class="header-row">
            <div>
                <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                <h1 class="page-title">Relatórios</h1>
                <div class="breadcrumb">
                    <span>Início</span>
                    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Operacional</span>
                    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Relatórios</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        {{-- FILTRO PERÍODO --}}
        <div class="search-card" style="margin-bottom:20px;">
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding:4px;">
                <span style="font-size:13px;font-weight:700;color:var(--text);"><i class="fas fa-calendar"></i> Período</span>
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="date" wire:model.live="dataInicio" style="width:160px;height:40px;">
                    <span style="color:var(--muted);font-size:13px;">até</span>
                    <input type="date" wire:model.live="dataFim" style="width:160px;height:40px;">
                </div>
            </div>
        </div>

        {{-- TABS PRINCIPAIS --}}
        <div class="form-card" x-data="{ tab: 'vendas' }">
            <div class="tab-nav">
                <button @click="tab = 'vendas'" :class="tab === 'vendas' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-cash-register"></i> Vendas</button>
                <button @click="tab = 'compras'" :class="tab === 'compras' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-shopping-bag"></i> Compras</button>
                <button @click="tab = 'financeiro'" :class="tab === 'financeiro' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-chart-pie"></i> Financeiro</button>
                <button @click="tab = 'estoque'" :class="tab === 'estoque' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-warehouse"></i> Estoque</button>
                <button @click="tab = 'clientes'" :class="tab === 'clientes' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-users"></i> Clientes</button>
            </div>

            <div style="padding:20px;">

                {{-- TAB: VENDAS --}}
                <div x-show="tab === 'vendas'">
                    @php $v = $this->vendasResumo; @endphp
                    <div class="metrics-grid" style="margin-bottom:16px;">
                        <div class="metric-card">
                            <div class="metric-icon green"><i class="fas fa-dollar-sign"></i></div>
                            <div><span class="metric-label">Faturamento</span><span class="metric-value">R$ {{ number_format($v['total'], 2, ',', '.') }}</span><span class="metric-sub green">Total vendido</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon blue"><i class="fas fa-receipt"></i></div>
                            <div><span class="metric-label">Qtd. Vendas</span><span class="metric-value">{{ $v['quantidade'] }}</span><span class="metric-sub">Pedidos</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon purple"><i class="fas fa-calculator"></i></div>
                            <div><span class="metric-label">Ticket Médio</span><span class="metric-value">R$ {{ number_format($v['media'] ?? 0, 2, ',', '.') }}</span><span class="metric-sub">Por venda</span></div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        {{-- Vendas por Forma de Pagamento --}}
                        <div class="sub-card">
                            <div class="section-title">Por Forma de Pagamento</div>
                            @forelse ($this->vendasPorPagamento as $p)
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;">
                                    <span style="font-weight:600;">{{ \App\Models\FormaPagamento::find($p['forma_pagamento_id'])?->nome ?? '—' }}</span>
                                    <span style="font-weight:700;">R$ {{ number_format($p['total'], 2, ',', '.') }}</span>
                                </div>
                            @empty
                                <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum pagamento no período.</div>
                            @endforelse
                        </div>

                        {{-- Vendas por Dia --}}
                        <div class="sub-card" style="max-height:300px;overflow-y:auto;">
                            <div class="section-title">Vendas por Dia</div>
                            @forelse ($this->vendasPorDia as $d)
                                <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);font-size:12px;">
                                    <span>{{ \Carbon\Carbon::parse($d['dia'])->format('d/m/Y') }}</span>
                                    <span style="color:var(--muted);">{{ $d['qtd'] }} vendas</span>
                                    <span style="font-weight:700;">R$ {{ number_format($d['total'], 2, ',', '.') }}</span>
                                </div>
                            @empty
                                <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhuma venda no período.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Top Produtos --}}
                    <div class="sub-card">
                        <div class="section-title">Produtos Mais Vendidos</div>
                        <div style="overflow-x:auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th class="data-table-th text-left">Produto</th>
                                        <th class="data-table-th text-center">Qtd</th>
                                        <th class="data-table-th text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($this->vendasTopProdutos as $p)
                                        @php $var = \App\Models\ProdutoVariacao::find($p['produto_variacao_id']); @endphp
                                        <tr class="data-table-tr">
                                            <td class="data-table-td font-semibold">{{ $var?->nome_completo ?? '#' . $p['produto_variacao_id'] }}</td>
                                            <td class="data-table-td text-center">{{ $p['qtd'] }}</td>
                                            <td class="data-table-td text-right font-bold">R$ {{ number_format($p['total'], 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="data-table-empty">Nenhum produto vendido no período.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB: COMPRAS --}}
                <div x-show="tab === 'compras'">
                    @php $c = $this->comprasResumo; @endphp
                    <div class="metrics-grid" style="margin-bottom:16px;">
                        <div class="metric-card">
                            <div class="metric-icon amber"><i class="fas fa-dollar-sign"></i></div>
                            <div><span class="metric-label">Total Gasto</span><span class="metric-value">R$ {{ number_format($c['total'], 2, ',', '.') }}</span><span class="metric-sub amber">Em compras</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon blue"><i class="fas fa-file-invoice"></i></div>
                            <div><span class="metric-label">Pedidos</span><span class="metric-value">{{ $c['quantidade'] }}</span><span class="metric-sub">Realizados</span></div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div class="sub-card">
                            <div class="section-title">Por Fornecedor</div>
                            @forelse ($this->comprasPorFornecedor as $f)
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;">
                                    <span style="font-weight:600;">{{ \App\Models\Fornecedor::find($f['fornecedor_id'])?->nome ?? '—' }}</span>
                                    <span style="color:var(--muted);">{{ $f['qtd'] }} ped.</span>
                                    <span style="font-weight:700;">R$ {{ number_format($f['total'], 2, ',', '.') }}</span>
                                </div>
                            @empty
                                <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhuma compra no período.</div>
                            @endforelse
                        </div>
                        <div class="sub-card">
                            <div class="section-title">Por Status</div>
                            @forelse ($this->comprasPorStatus as $s)
                                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;">
                                    <span style="font-weight:600;">{{ $s['status'] }}</span>
                                    <span style="color:var(--muted);">{{ $s['qtd'] }} ped.</span>
                                    <span style="font-weight:700;">R$ {{ number_format($s['total'], 2, ',', '.') }}</span>
                                </div>
                            @empty
                                <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum pedido.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- TAB: FINANCEIRO --}}
                <div x-show="tab === 'financeiro'">
                    @php $f = $this->financeiroResumo; $ag = $this->financeiroAging; @endphp
                    <div class="metrics-grid" style="margin-bottom:16px;">
                        <div class="metric-card">
                            <div class="metric-icon green"><i class="fas fa-arrow-up"></i></div>
                            <div><span class="metric-label">Receitas</span><span class="metric-value">R$ {{ number_format($f['receitas'], 2, ',', '.') }}</span><span class="metric-sub green">Recebido</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon amber"><i class="fas fa-arrow-down"></i></div>
                            <div><span class="metric-label">Despesas</span><span class="metric-value">R$ {{ number_format($f['despesas'], 2, ',', '.') }}</span><span class="metric-sub amber">Pago</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon blue"><i class="fas fa-clock"></i></div>
                            <div><span class="metric-label">A Receber</span><span class="metric-value">R$ {{ number_format($f['a_receber'], 2, ',', '.') }}</span><span class="metric-sub">Pendente</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon purple"><i class="fas fa-clock"></i></div>
                            <div><span class="metric-label">A Pagar</span><span class="metric-value">R$ {{ number_format($f['a_pagar'], 2, ',', '.') }}</span><span class="metric-sub">Em aberto</span></div>
                        </div>
                    </div>

                    <div style="padding:12px;background:color-mix(in srgb,var(--text)1%,var(--surface));border:1px solid var(--border);border-radius:10px;margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:900;color:var(--text);">
                            <span>Resultado (Receitas - Despesas)</span>
                            <span style="color:{{ ($f['receitas'] - $f['despesas']) >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                                R$ {{ number_format($f['receitas'] - $f['despesas'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- Aging --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div class="sub-card">
                            <div class="section-title">Aging — A Receber</div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;color:var(--danger);"><span>Vencidos</span><span>R$ {{ number_format($ag['receber']['vencidos'], 2, ',', '.') }}</span></div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;"><span>0-30 dias</span><span>R$ {{ number_format($ag['receber']['ate_30'], 2, ',', '.') }}</span></div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;"><span>31-60 dias</span><span>R$ {{ number_format($ag['receber']['31_60'], 2, ',', '.') }}</span></div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;"><span>60+ dias</span><span>R$ {{ number_format($ag['receber']['60_mais'], 2, ',', '.') }}</span></div>
                        </div>
                        <div class="sub-card">
                            <div class="section-title">Aging — A Pagar</div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;color:var(--danger);"><span>Vencidos</span><span>R$ {{ number_format($ag['pagar']['vencidos'], 2, ',', '.') }}</span></div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;"><span>0-30 dias</span><span>R$ {{ number_format($ag['pagar']['ate_30'], 2, ',', '.') }}</span></div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;"><span>31-60 dias</span><span>R$ {{ number_format($ag['pagar']['31_60'], 2, ',', '.') }}</span></div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;"><span>60+ dias</span><span>R$ {{ number_format($ag['pagar']['60_mais'], 2, ',', '.') }}</span></div>
                        </div>
                    </div>

                    {{-- Por Categoria --}}
                    <div class="sub-card">
                        <div class="section-title">Despesas por Categoria</div>
                        @forelse ($this->financeiroPorCategoria as $l)
                            @if ($l['tipo'] === 'despesa')
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px;">
                                <span style="font-weight:600;">{{ \App\Models\FinanceiroCategoria::find($l['categoria_id'])?->nome ?? '—' }}</span>
                                <span style="font-weight:700;">R$ {{ number_format($l['total'], 2, ',', '.') }}</span>
                            </div>
                            @endif
                        @empty
                            <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhuma despesa no período.</div>
                        @endforelse
                    </div>
                </div>

                {{-- TAB: ESTOQUE --}}
                <div x-show="tab === 'estoque'">
                    @php $e = $this->estoqueResumo; @endphp
                    <div class="metrics-grid" style="margin-bottom:16px;">
                        <div class="metric-card">
                            <div class="metric-icon purple"><i class="fas fa-boxes"></i></div>
                            <div><span class="metric-label">Produtos</span><span class="metric-value">{{ $e['total_produtos'] }}</span><span class="metric-sub">Cadastrados</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon green"><i class="fas fa-check-circle"></i></div>
                            <div><span class="metric-label">Com Estoque</span><span class="metric-value">{{ $e['com_estoque'] }}</span><span class="metric-sub green">Disponíveis</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon amber"><i class="fas fa-exclamation-triangle"></i></div>
                            <div><span class="metric-label">Estoque Baixo</span><span class="metric-value">{{ $e['baixo'] }}</span><span class="metric-sub amber">≤ 5 unidades</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon green"><i class="fas fa-times-circle"></i></div>
                            <div><span class="metric-label">Zerados</span><span class="metric-value">{{ $e['zerados'] }}</span><span class="metric-sub">Sem estoque</span></div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="sub-card" style="max-height:300px;overflow-y:auto;">
                            <div class="section-title" style="color:var(--danger);">Vencendo (próximos 30 dias)</div>
                            @forelse ($this->estoqueLotesVencendo as $l)
                                <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);font-size:12px;">
                                    <span style="font-weight:600;">{{ $l['numero_lote'] }}</span>
                                    <span style="color:var(--warning);">{{ \Carbon\Carbon::parse($l['data_validade'])->format('d/m/Y') }}</span>
                                    <span>{{ $l['quantidade_atual'] }} un.</span>
                                </div>
                            @empty
                                <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum lote próximo do vencimento.</div>
                            @endforelse
                        </div>
                        <div class="sub-card" style="max-height:300px;overflow-y:auto;">
                            <div class="section-title" style="color:var(--danger);">Vencidos</div>
                            @forelse ($this->estoqueLotesVencidos as $l)
                                <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);font-size:12px;color:var(--danger);">
                                    <span style="font-weight:600;">{{ $l['numero_lote'] }}</span>
                                    <span>{{ \Carbon\Carbon::parse($l['data_validade'])->format('d/m/Y') }}</span>
                                    <span>{{ $l['quantidade_atual'] }} un.</span>
                                </div>
                            @empty
                                <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum lote vencido.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- TAB: CLIENTES --}}
                <div x-show="tab === 'clientes'">
                    @php $cl = $this->clientesResumo; @endphp
                    <div class="metrics-grid" style="margin-bottom:16px;">
                        <div class="metric-card">
                            <div class="metric-icon purple"><i class="fas fa-users"></i></div>
                            <div><span class="metric-label">Total</span><span class="metric-value">{{ $cl['total'] }}</span><span class="metric-sub">Cadastrados</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon blue"><i class="fas fa-user-plus"></i></div>
                            <div><span class="metric-label">Novos</span><span class="metric-value">{{ $cl['novos'] }}</span><span class="metric-sub">No período</span></div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon green"><i class="fas fa-circle-check"></i></div>
                            <div><span class="metric-label">Ativos</span><span class="metric-value">{{ $cl['ativos'] }}</span><span class="metric-sub green">Atualmente</span></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
