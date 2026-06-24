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
                <h1 class="page-title">Curva ABC</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Relatórios</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Curva ABC</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        @php $resumo = $this->resumo; @endphp
        <div class="metrics-grid" style="grid-template-columns:repeat(4,1fr);">
            <div class="metric-card" style="padding:14px;border-left:4px solid var(--primary-600);">
                <div><span class="metric-label">CLASSE A — Top</span><span class="metric-value" style="font-size:18px;">{{ $resumo['a_count'] }} itens</span><span class="metric-sub">R$ {{ number_format($resumo['a_valor'], 2, ',', '.') }} ({{ $resumo['total'] > 0 ? round($resumo['a_valor']/$resumo['total']*100) : 0 }}%)</span></div>
                <div class="metric-icon green" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-star"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;border-left:4px solid var(--warning);">
                <div><span class="metric-label">CLASSE B — Médio</span><span class="metric-value" style="font-size:18px;">{{ $resumo['b_count'] }} itens</span><span class="metric-sub">R$ {{ number_format($resumo['b_valor'], 2, ',', '.') }} ({{ $resumo['total'] > 0 ? round($resumo['b_valor']/$resumo['total']*100) : 0 }}%)</span></div>
                <div class="metric-icon amber" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-minus"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;border-left:4px solid var(--danger);">
                <div><span class="metric-label">CLASSE C — Baixo</span><span class="metric-value" style="font-size:18px;">{{ $resumo['c_count'] }} itens</span><span class="metric-sub">R$ {{ number_format($resumo['c_valor'], 2, ',', '.') }} ({{ $resumo['total'] > 0 ? round($resumo['c_valor']/$resumo['total']*100) : 0 }}%)</span></div>
                <div class="metric-icon" style="width:32px;height:32px;border-radius:8px;font-size:12px;background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);"><i class="fas fa-arrow-down"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div><span class="metric-label">Total Analisado</span><span class="metric-value" style="font-size:18px;">R$ {{ number_format($resumo['total'], 2, ',', '.') }}</span><span class="metric-sub">{{ $resumo['a_count'] + $resumo['b_count'] + $resumo['c_count'] }} produtos</span></div>
                <div class="metric-icon blue" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="width:150px;margin:0;"><label>Loja</label><select wire:model.live="lojaFiltro" style="height:38px;font-size:12px;"><option value="">Todas</option>@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
            <div class="field" style="width:180px;margin:0;"><label>Departamento</label><select wire:model.live="categoriaFiltro" style="height:38px;font-size:12px;"><option value="">Todos</option>@foreach ($this->categorias as $c)<option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>@endforeach</select></div>
            <div class="field" style="width:120px;margin:0;"><label>Período</label><select wire:model.live="periodo" style="height:38px;font-size:12px;"><option value="30">30 dias</option><option value="60">60 dias</option><option value="90">90 dias</option><option value="180">180 dias</option></select></div>
            <div class="field" style="width:120px;margin:0;"><label>Classe</label><select wire:model.live="classeFiltro" style="height:38px;font-size:12px;"><option value="">Todas</option><option value="A">Classe A</option><option value="B">Classe B</option><option value="C">Classe C</option></select></div>
        </div>

        <div class="data-table-wrap">
            <div style="overflow-x:auto;">
                @php $analise = $this->analise(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr>
                        <th class="data-table-th text-left">Produto</th>
                        <th class="data-table-th text-left">SKU</th>
                        <th class="data-table-th text-left">Depto</th>
                        <th class="data-table-th text-right">Valor</th>
                        <th class="data-table-th text-right">Quantidade</th>
                        <th class="data-table-th text-center">%</th>
                        <th class="data-table-th text-center">% Acum</th>
                        <th class="data-table-th text-center">Classe</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($analise as $item)
                            <tr class="data-table-tr" style="{{ $item['classe'] === 'A' ? 'background:color-mix(in srgb,var(--success)3%,transparent);' : ($item['classe'] === 'B' ? 'background:color-mix(in srgb,var(--warning)3%,transparent);' : '') }}">
                                <td class="data-table-td font-semibold" style="text-transform:uppercase;font-size:11px;">{{ $item['nome'] }}</td>
                                <td class="data-table-td font-mono text-muted" style="font-size:10px;">{{ $item['sku'] }}</td>
                                <td class="data-table-td text-muted" style="font-size:10px;">{{ $item['categoria'] }}</td>
                                <td class="data-table-td text-right font-bold">R$ {{ number_format($item['valor'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-right font-semibold">{{ number_format($item['qtd'], 3, ',', '.') }}</td>
                                <td class="data-table-td text-center">{{ $item['pct'] }}%</td>
                                <td class="data-table-td text-center text-muted">{{ $item['pct_acum'] }}%</td>
                                <td class="data-table-td text-center">
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;font-weight:900;font-size:13px;{{ $item['classe'] === 'A' ? 'background:color-mix(in srgb,var(--success)15%,transparent);color:var(--success);' : ($item['classe'] === 'B' ? 'background:color-mix(in srgb,var(--warning)15%,transparent);color:var(--warning);' : 'background:color-mix(in srgb,var(--text)8%,transparent);color:var(--muted);') }}">{{ $item['classe'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="data-table-empty">Nenhum dado encontrado para o período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
