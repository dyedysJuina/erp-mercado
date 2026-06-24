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
                <h1 class="page-title">Histórico de Preços</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Preços</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Histórico</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        <div class="metrics-grid" style="grid-template-columns:repeat(4,1fr);">
            <div class="metric-card" style="padding:14px;">
                <div>
                    <span class="metric-label">Ajustes no Período</span>
                    <span class="metric-value" style="font-size:22px;">{{ $this->totais['total_ajustes'] }}</span>
                    <span class="metric-sub">{{ $this->dataInicio ? 'Filtrado' : 'Últimos ' . $this->periodo . ' dias' }}</span>
                </div>
                <div class="metric-icon blue" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-history"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div>
                    <span class="metric-label">Variação Média</span>
                    <span class="metric-value" style="font-size:22px;{{ $this->totais['media_variacao'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">
                        {{ $this->totais['media_variacao'] >= 0 ? '+' : '' }}R$ {{ number_format($this->totais['media_variacao'], 2, ',', '.') }}
                    </span>
                    <span class="metric-sub">Por ajuste</span>
                </div>
                <div class="metric-icon purple" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-chart-line"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div>
                    <span class="metric-label">Maior Subida</span>
                    <span class="metric-value" style="font-size:22px;color:var(--success);">+R$ {{ number_format($this->totais['maior_subida'], 2, ',', '.') }}</span>
                    <span class="metric-sub">Em um único ajuste</span>
                </div>
                <div class="metric-icon green" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-arrow-up"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div>
                    <span class="metric-label">Maior Queda</span>
                    <span class="metric-value" style="font-size:22px;color:var(--danger);">R$ {{ number_format($this->totais['maior_queda'], 2, ',', '.') }}</span>
                    <span class="metric-sub">Em um único ajuste</span>
                </div>
                <div class="metric-icon" style="width:32px;height:32px;border-radius:8px;font-size:12px;background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="flex:1;min-width:200px;margin:0;">
                <label style="font-size:10px;">Buscar Produto</label>
                <input wire:model.live.debounce.300ms="busca" placeholder="Nome ou SKU..." style="height:38px;font-size:13px;">
            </div>
            <div class="field" style="width:150px;margin:0;">
                <label style="font-size:10px;">Loja</label>
                <select wire:model.live="lojaFiltro" style="height:38px;font-size:12px;">
                    <option value="">Todas</option>
                    @foreach ($this->lojas as $l)
                        <option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="width:130px;margin:0;">
                <label style="font-size:10px;">Período</label>
                <select wire:model.live="periodo" style="height:38px;font-size:12px;">
                    <option value="">Todos</option>
                    <option value="7">7 dias</option>
                    <option value="30">30 dias</option>
                    <option value="90">90 dias</option>
                    <option value="180">180 dias</option>
                    <option value="0">Personalizado</option>
                </select>
            </div>
            @if ($periodo === '0' || $periodo === '')
                <div class="field" style="width:130px;margin:0;">
                    <label style="font-size:10px;">De</label>
                    <input wire:model="dataInicio" type="date" style="height:38px;font-size:12px;">
                </div>
                <div class="field" style="width:130px;margin:0;">
                    <label style="font-size:10px;">Até</label>
                    <input wire:model="dataFim" type="date" style="height:38px;font-size:12px;">
                </div>
            @endif
            <button wire:click="$set('busca',''); $set('lojaFiltro',''); $set('periodo',''); $set('dataInicio',''); $set('dataFim','')" class="btn-sm btn-secondary" style="height:38px;">Limpar</button>
        </div>

        @if ($this->produtoDetalhe)
            <div class="card" style="margin-bottom:16px;padding:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <h3 style="margin:0;font-size:15px;font-weight:800;color:var(--text);"><i class="fas fa-chart-simple" style="color:var(--primary-600);"></i> Evolução: {{ $this->produtoDetalheNome }}</h3>
                    <button wire:click="fecharDetalhe" style="background:none;border:0;color:var(--muted);cursor:pointer;font-size:18px;">&times;</button>
                </div>
                @php $evolucao = $this->evolucaoProduto; @endphp
                @if (count($evolucao) > 0)
                    <div style="overflow-x:auto;">
                        <table class="data-table" style="font-size:12px;min-width:400px;">
                            <thead><tr><th class="data-table-th text-left">Data</th><th class="data-table-th text-right">Anterior</th><th class="data-table-th text-right">Novo</th><th class="data-table-th text-center">Diferença</th><th class="data-table-th text-left">Motivo</th><th class="data-table-th text-left">Por</th></tr></thead>
                            <tbody>
                                @foreach ($evolucao as $e)
                                    @php $diff = (float)$e['preco_novo'] - (float)$e['preco_anterior']; @endphp
                                    <tr class="data-table-tr">
                                        <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ \Carbon\Carbon::parse($e['created_at'])->format('d/m/Y H:i') }}</td>
                                        <td class="data-table-td text-right font-semibold">R$ {{ number_format($e['preco_anterior'], 2, ',', '.') }}</td>
                                        <td class="data-table-td text-right font-bold">R$ {{ number_format($e['preco_novo'], 2, ',', '.') }}</td>
                                        <td class="data-table-td text-center font-bold" style="{{ $diff >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">{{ $diff >= 0 ? '+' : '' }}R$ {{ number_format($diff, 2, ',', '.') }}</td>
                                        <td class="data-table-td text-muted" style="font-size:11px;">{{ $e['motivo'] ?? '—' }}</td>
                                        <td class="data-table-td text-muted" style="font-size:11px;">{{ $e['usuario']['name'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align:center;padding:24px;color:var(--muted);font-size:13px;">Nenhum histórico para este produto.</div>
                @endif
            </div>
        @endif

        <div class="data-table-wrap">
            <div class="data-table-header">
                <span class="data-table-title"><i class="fas fa-clock-rotate-left"></i> Ajustes de Preços</span>
            </div>
            <div style="overflow-x:auto;">
                @php $historico = $this->historico(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr><th class="data-table-th text-left">Data</th><th class="data-table-th text-left">Produto</th><th class="data-table-th text-left">Loja</th><th class="data-table-th text-right">Anterior</th><th class="data-table-th text-right">Novo</th><th class="data-table-th text-center">Diferença</th><th class="data-table-th text-left">Motivo</th><th class="data-table-th text-left">Por</th></tr></thead>
                    <tbody>
                        @forelse ($historico as $h)
                            @php $diff = (float)$h['preco_novo'] - (float)$h['preco_anterior']; @endphp
                            <tr class="data-table-tr" style="cursor:pointer;" wire:click="verDetalhe({{ $h['produto_variacao_id'] }}, '{{ addslashes($h['variacao']['nome_completo']) }}')">
                                <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ \Carbon\Carbon::parse($h['created_at'])->format('d/m/Y H:i') }}</td>
                                <td class="data-table-td font-semibold" style="text-transform:uppercase;font-size:11px;">{{ $h['variacao']['nome_completo'] ?? '—' }}</td>
                                <td class="data-table-td text-muted" style="font-size:11px;">{{ $h['loja']['nome'] ?? '—' }}</td>
                                <td class="data-table-td text-right font-semibold">R$ {{ number_format($h['preco_anterior'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-right font-bold">R$ {{ number_format($h['preco_novo'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-center font-bold" style="{{ $diff >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">{{ $diff >= 0 ? '+' : '' }}R$ {{ number_format($diff, 2, ',', '.') }}</td>
                                <td class="data-table-td text-muted" style="font-size:11px;">{{ $h['motivo'] ?? '—' }}</td>
                                <td class="data-table-td text-muted" style="font-size:11px;">{{ $h['usuario']['name'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="data-table-empty">Nenhum ajuste de preço registrado no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($historico->hasPages())
                <div style="padding:12px 16px;border-top:1px solid var(--border);">
                    {{ $historico->links('livewire.pagination-custom') }}
                </div>
            @endif
        </div>
    </div>
</div>
