<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad">
        <div class="header-row">
            <div>
                <h1 class="page-title" style="font-size:22px;">Dashboard Executivo</h1>
                <div class="breadcrumb">
                    <span>Inicio</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Dashboard</span>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <div class="field" style="margin:0;width:180px;"><select wire:model.live="lojaFiltro" style="height:36px;font-size:12px;"><option value="">Todas as Lojas</option>@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
            </div>
        </div>

        @php $d = $this->dados; @endphp

        <div class="metrics-grid" style="grid-template-columns:repeat(4,1fr);">
            <div class="metric-card" style="padding:18px;">
                <div><span class="metric-label" style="font-size:10px;">Faturamento Hoje</span>
                    <span class="metric-value" style="font-size:24px;color:var(--success);">R$ {{ number_format($d['fat_hoje'], 2, ',', '.') }}</span>
                    <span class="metric-sub">{{ $d['vendas_hoje'] }} venda(s) - Ticket medio R$ {{ number_format($d['ticket_medio'], 2, ',', '.') }}</span>
                </div>
                <div class="metric-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-cash-register"></i></div>
            </div>
            <div class="metric-card" style="padding:18px;">
                <div><span class="metric-label" style="font-size:10px;">Faturamento do Mes</span>
                    <span class="metric-value" style="font-size:24px;">R$ {{ number_format($d['fat_mes'], 2, ',', '.') }}</span>
                    <span class="metric-sub">{{ now()->format('F/Y') }}</span>
                </div>
                <div class="metric-icon blue" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-calendar"></i></div>
            </div>
            <div class="metric-card" style="padding:18px;">
                <div><span class="metric-label" style="font-size:10px;">Produtos em Estoque Critico</span>
                    <span class="metric-value" style="font-size:24px;color:var(--warning);">{{ count($d['estoque_critico']) }}</span>
                    <span class="metric-sub">Precisam de reposicao</span>
                </div>
                <div class="metric-icon amber" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="metric-card" style="padding:18px;">
                <div><span class="metric-label" style="font-size:10px;">Contas Atrasadas (Receber)</span>
                    <span class="metric-value" style="font-size:24px;color:var(--danger);">{{ count($d['inadimplentes']) }}</span>
                    <span class="metric-sub">Valor total: R$ {{ number_format(collect($d['inadimplentes'])->sum('valor'), 2, ',', '.') }}</span>
                </div>
                <div class="metric-icon" style="width:40px;height:40px;border-radius:10px;font-size:16px;background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);"><i class="fas fa-hand-holding"></i></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
            <div class="card" style="padding:16px;">
                <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:var(--text);">Vendas - Ultimos 7 Dias</h4>
                <div style="display:flex;align-items:end;gap:6px;height:120px;padding:10px 0;">
                    @php $maxValor = max(1, collect($d['grafico_7dias'])->max('total')); @endphp
                    @foreach ($d['grafico_7dias'] as $g)
                        @php $altura = max(4, ($g['total'] / $maxValor) * 100); @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
                            <span style="font-size:9px;font-weight:700;color:var(--muted);">R$ {{ number_format($g['total'], 0, ',', '.') }}</span>
                            <div style="width:100%;border-radius:4px 4px 0 0;background:var(--primary-600);height:{{ $altura }}px;min-height:4px;transition:height 0.3s;"></div>
                            <span style="font-size:8px;color:var(--muted);">{{ $g['dia'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card" style="padding:16px;">
                <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:var(--text);">Top 10 Produtos (Mes)</h4>
                <div style="max-height:220px;overflow-y:auto;">
                    @forelse ($d['top_produtos'] as $i => $p)
                        <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                            <span style="width:22px;height:22px;border-radius:50%;background:color-mix(in srgb,var(--primary-500)10%,transparent);color:var(--primary-600);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:10px;flex-shrink:0;">{{ $i + 1 }}</span>
                            <span style="flex:1;font-weight:600;text-transform:uppercase;font-size:11px;">{{ $p->nome_completo }}</span>
                            <span style="font-weight:700;color:var(--text);">{{ number_format($p->qtd, 0, ',', '.') }} un</span>
                            <span style="color:var(--success);font-weight:700;font-size:11px;">R$ {{ number_format($p->total, 2, ',', '.') }}</span>
                        </div>
                    @empty
                        <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum produto vendido no mes.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
            <div class="card" style="padding:16px;">
                <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:var(--text);"><i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Estoque Critico</h4>
                @forelse ($d['estoque_critico'] as $e)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                        <span style="font-weight:600;text-transform:uppercase;font-size:11px;flex:1;">{{ $e->nome_completo }}</span>
                        <span style="color:var(--danger);font-weight:700;">{{ number_format($e->quantidade_atual, 3, ',', '.') }}</span>
                        <span style="color:var(--muted);font-size:10px;">/ min {{ number_format($e->estoque_minimo, 3, ',', '.') }}</span>
                    </div>
                @empty
                    <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum produto com estoque critico.</div>
                @endforelse
            </div>

            <div class="card" style="padding:16px;">
                <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:var(--text);"><i class="fas fa-hand-holding" style="color:var(--danger);"></i> Inadimplencia - Contas Atrasadas</h4>
                @forelse ($d['inadimplentes'] as $i)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                        <div style="flex:1;">
                            <span style="font-weight:600;display:block;font-size:11px;">{{ $i->descricao }}</span>
                            <span style="font-size:10px;color:var(--muted);">Venc: {{ \Carbon\Carbon::parse($i->data_vencimento)->format('d/m/Y') }}</span>
                        </div>
                        <span style="color:var(--danger);font-weight:700;">R$ {{ number_format($i->valor, 2, ',', '.') }}</span>
                    </div>
                @empty
                    <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhuma conta atrasada.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
