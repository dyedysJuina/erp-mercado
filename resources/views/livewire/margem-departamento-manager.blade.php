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
                <h1 class="page-title">Margem por Departamento</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Relatórios</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Margem por Depto</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        @php $d = $this->dados; @endphp
        <div class="metrics-grid" style="grid-template-columns:repeat(4,1fr);">
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Receita Total</span><span class="metric-value" style="font-size:20px;color:var(--success);">R$ {{ number_format($d['total_receita'], 2, ',', '.') }}</span></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Custo Total</span><span class="metric-value" style="font-size:20px;color:var(--danger);">R$ {{ number_format($d['total_custo'], 2, ',', '.') }}</span></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Lucro Total</span><span class="metric-value" style="font-size:20px;{{ $d['total_lucro'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($d['total_lucro'], 2, ',', '.') }}</span></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Margem Média</span><span class="metric-value" style="font-size:20px;{{ $d['margem_media'] >= 20 ? 'color:var(--success);' : ($d['margem_media'] >= 10 ? 'color:var(--warning);' : 'color:var(--danger);') }}">{{ $d['margem_media'] }}%</span></div></div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="width:150px;margin:0;"><label>Loja</label><select wire:model.live="lojaFiltro" style="height:38px;font-size:12px;"><option value="">Todas</option>@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
            <div class="field" style="width:120px;margin:0;"><label>Período</label><select wire:model.live="periodo" style="height:38px;font-size:12px;"><option value="30">30 dias</option><option value="60">60 dias</option><option value="90">90 dias</option><option value="180">180 dias</option></select></div>
        </div>

        <div class="data-table-wrap">
            <div style="overflow-x:auto;">
                <table class="data-table" style="font-size:12px;">
                    <thead><tr>
                        <th class="data-table-th text-left">Departamento</th>
                        <th class="data-table-th text-right">Receita</th>
                        <th class="data-table-th text-right">Custo</th>
                        <th class="data-table-th text-right">Lucro</th>
                        <th class="data-table-th text-center">Margem</th>
                        <th class="data-table-th text-center">Participação</th>
                        <th class="data-table-th text-right">Vendas</th>
                        <th class="data-table-th text-right">Qtd Itens</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($d['departamentos'] as $dep)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold" style="text-transform:uppercase;">{{ $dep['categoria'] }}</td>
                                <td class="data-table-td text-right font-semibold" style="color:var(--success);">R$ {{ number_format($dep['receita'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-right font-semibold">R$ {{ number_format($dep['custo'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-right font-bold" style="{{ $dep['lucro'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($dep['lucro'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-center">
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:999px;font-size:10px;font-weight:700;{{ $dep['margem'] >= 20 ? 'background:color-mix(in srgb,var(--success)10%,transparent);color:var(--success);' : ($dep['margem'] >= 10 ? 'background:color-mix(in srgb,var(--warning)10%,transparent);color:var(--warning);' : 'background:color-mix(in srgb,var(--danger)10%,transparent);color:var(--danger);') }}">
                                        {{ $dep['margem'] }}%
                                    </span>
                                </td>
                                <td class="data-table-td text-center">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <span style="font-size:11px;">{{ $dep['participacao'] }}%</span>
                                        <div style="flex:1;height:6px;border-radius:3px;background:color-mix(in srgb,var(--text)8%,var(--surface));max-width:60px;overflow:hidden;">
                                            <div style="height:100%;border-radius:3px;width:{{ $dep['participacao'] }}%;background:var(--primary-600);"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="data-table-td text-right font-semibold">{{ $dep['vendas'] }}</td>
                                <td class="data-table-td text-right text-muted">{{ number_format($dep['qtd'], 0, ',', '.') }}</td>
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
