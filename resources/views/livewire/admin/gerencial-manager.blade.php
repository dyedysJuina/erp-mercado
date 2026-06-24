<div>
    <div class="main-content-pad">
        <div class="header-row">
            <div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Dashboard Gerencial</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Admin</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Gerencial</span></div></div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        {{-- FILTROS --}}
        <div class="search-card" style="margin-bottom:20px;">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:4px;">
                <span style="font-size:13px;font-weight:700;color:var(--text);"><i class="fas fa-filter"></i> Filtros</span>
                <input type="date" wire:model.live="dataInicio" style="width:150px;height:38px;font-size:13px;">
                <span style="color:var(--muted);">até</span>
                <input type="date" wire:model.live="dataFim" style="width:150px;height:38px;font-size:13px;">
                <select wire:model.live="lojaFiltro" style="width:200px;height:38px;font-size:13px;">
                    <option value="">Todas as lojas</option>
                    @foreach ($this->lojas as $l)
                        <option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- MÉTRICAS GLOBAIS --}}
        @php $vh = $this->vendasResumo; $vhj = $this->vendasHoje; $vc = $this->totaisCancelados; @endphp
        <div class="metrics-grid" style="margin-bottom:24px;">
            <div class="metric-card"><div class="metric-icon purple"><i class="fas fa-dollar-sign"></i></div><div><span class="metric-label">Faturamento</span><span class="metric-value">R$ {{ number_format($vh['total'], 2, ',', '.') }}</span><span class="metric-sub">No período</span></div></div>
            <div class="metric-card"><div class="metric-icon blue"><i class="fas fa-receipt"></i></div><div><span class="metric-label">Vendas</span><span class="metric-value">{{ $vh['qtd'] }}</span><span class="metric-sub">No período</span></div></div>
            <div class="metric-card"><div class="metric-icon green"><i class="fas fa-calculator"></i></div><div><span class="metric-label">Ticket Médio</span><span class="metric-value">R$ {{ number_format($vh['media'], 2, ',', '.') }}</span><span class="metric-sub">Por venda</span></div></div>
            <div class="metric-card"><div class="metric-icon green"><i class="fas fa-sack-dollar"></i></div><div><span class="metric-label">Vendas Hoje</span><span class="metric-value">R$ {{ number_format($vhj['total'], 2, ',', '.') }}</span><span class="metric-sub green">{{ $vhj['qtd'] }} vendas</span></div></div>
            <div class="metric-card"><div class="metric-icon amber"><i class="fas fa-ban"></i></div><div><span class="metric-label">Cancelados</span><span class="metric-value">{{ $vc['qtd'] }}</span><span class="metric-sub amber">R$ {{ number_format($vc['total'], 2, ',', '.') }}</span></div></div>
        </div>

        {{-- GRID: Operadores + Gráfico --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            {{-- Performance por Operador --}}
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas fa-users"></i> Performance por Operador</span></div>
                <div class="form-body" style="padding:12px 16px;">
                    <table class="data-table" style="font-size:12px;">
                        <thead><tr><th class="data-table-th text-left">Operador</th><th class="data-table-th text-center">Vendas</th><th class="data-table-th text-right">Total</th><th class="data-table-th text-right">Ticket</th></tr></thead>
                        <tbody>
                            @forelse ($this->vendasPorOperador as $op)
                                @php $user = \App\Models\User::find($op['usuario_id']); @endphp
                                <tr class="data-table-tr">
                                    <td class="data-table-td font-semibold">{{ $user?->name ?? '#' . $op['usuario_id'] }}</td>
                                    <td class="data-table-td text-center">{{ $op['qtd'] }}</td>
                                    <td class="data-table-td text-right font-bold">R$ {{ number_format($op['total'], 2, ',', '.') }}</td>
                                    <td class="data-table-td text-right">R$ {{ number_format($op['ticket_medio'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="data-table-empty">Nenhuma venda no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Gráfico Vendas 7 dias --}}
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas fa-chart-line"></i> Vendas 7 Dias</span></div>
                <div class="form-body" style="padding:16px;">
                    @php $maxVal = max(array_column($this->vendas7dias, 'total') ?: [1]); @endphp
                    <div style="display:flex;align-items:end;gap:8px;height:160px;">
                        @foreach ($this->vendas7dias as $v)
                            @php $alt = $maxVal > 0 ? max(4, ($v['total'] / $maxVal) * 140) : 4; @endphp
                            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;">
                                <div style="width:100%;border-radius:6px 6px 0 0;background:var(--primary-500);height:{{ $alt }}px;" title="R$ {{ number_format($v['total'], 2, ',', '.') }}"></div>
                                <span style="font-size:9px;color:var(--muted);margin-top:4px;font-weight:600;">{{ $v['data'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- GRID: Pagamento + Horas + Produtos --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
            {{-- Por Pagamento --}}
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas fa-credit-card"></i> Formas de Pagamento</span></div>
                <div class="form-body" style="padding:12px 16px;">
                    @forelse ($this->vendasPorPagamento as $p)
                        @php $fp = \App\Models\FormaPagamento::find($p['forma_pagamento_id']); @endphp
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                            <span style="font-weight:600;">{{ $fp?->nome ?? '—' }}</span>
                            <span style="font-weight:700;">R$ {{ number_format($p['total'], 2, ',', '.') }}</span>
                        </div>
                    @empty
                        <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum pagamento.</div>
                    @endforelse
                </div>
            </div>

            {{-- Por Hora --}}
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas fa-clock"></i> Fluxo por Hora</span></div>
                <div class="form-body" style="padding:16px;">
                    @php $maxHora = max(array_column($this->vendasPorHora, 'qtd') ?: [1]); @endphp
                    <div style="display:flex;align-items:end;gap:4px;height:120px;">
                        @foreach ($this->vendasPorHora as $h)
                            @php $altH = $maxHora > 0 ? max(3, ($h['qtd'] / $maxHora) * 100) : 3; @endphp
                            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;">
                                <div style="width:100%;border-radius:3px 3px 0 0;background:#6366f1;height:{{ $altH }}px;" title="{{ $h['qtd'] }} vendas"></div>
                                <span style="font-size:6px;color:var(--muted);margin-top:2px;">{{ $h['hora'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Top Produtos --}}
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas fa-crown"></i> Top Produtos</span></div>
                <div class="form-body" style="padding:12px 16px;">
                    @forelse ($this->topProdutos as $tp)
                        @php $var = \App\Models\ProdutoVariacao::find($tp['produto_variacao_id']); @endphp
                        <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border);font-size:11px;">
                            <span style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;">{{ $var?->nome_completo ?? '#' . $tp['produto_variacao_id'] }}</span>
                            <span style="font-weight:700;">R$ {{ number_format($tp['total'], 2, ',', '.') }}</span>
                        </div>
                    @empty
                        <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum produto.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ESTATÍSTICAS RÁPIDAS --}}
        <div class="form-card">
            <div class="form-card-header"><span class="form-card-title"><i class="fas fa-info-circle"></i> Resumo Rápido</span></div>
            <div style="padding:16px 20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;font-size:13px;">
                <div><span style="color:var(--muted);display:block;">Total de produtos</span><span style="font-weight:900;font-size:18px;">{{ \App\Models\ProdutoVariacao::count() }}</span></div>
                <div><span style="color:var(--muted);display:block;">Clientes cadastrados</span><span style="font-weight:900;font-size:18px;">{{ \App\Models\Cliente::count() }}</span></div>
                <div><span style="color:var(--muted);display:block;">Fornecedores</span><span style="font-weight:900;font-size:18px;">{{ \App\Models\Fornecedor::count() }}</span></div>
                <div><span style="color:var(--muted);display:block;">Funcionários ativos</span><span style="font-weight:900;font-size:18px;">{{ \App\Models\User::where('ativo', true)->count() }}</span></div>
                <div><span style="color:var(--muted);display:block;">Estoque crítico</span><span style="font-weight:900;font-size:18px;color:var(--danger);">{{ \App\Models\EstoqueSaldo::whereColumn('quantidade_atual', '<=', 'estoque_minimo')->where('estoque_minimo', '>', 0)->count() }}</span></div>
            </div>
        </div>
    </div>
</div>
