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
                <h1 class="page-title">Fluxo de Caixa</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Financeiro</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Fluxo de Caixa</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        @php $d = $this->dados; @endphp
        <div class="metrics-grid" style="grid-template-columns:repeat(6,1fr);">
            <div class="metric-card" style="padding:12px;"><div><span class="metric-label">Saldo Atual</span><span class="metric-value" style="font-size:16px;{{ $d['saldo_real'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($d['saldo_real'], 2, ',', '.') }}</span></div></div>
            <div class="metric-card" style="padding:12px;"><div><span class="metric-label">A Receber</span><span class="metric-value" style="font-size:16px;color:var(--success);">R$ {{ number_format($d['a_receber'], 2, ',', '.') }}</span></div></div>
            <div class="metric-card" style="padding:12px;"><div><span class="metric-label">A Pagar</span><span class="metric-value" style="font-size:16px;color:var(--danger);">R$ {{ number_format($d['a_pagar'], 2, ',', '.') }}</span></div></div>
            <div class="metric-card" style="padding:12px;"><div><span class="metric-label">Saldo Projetado</span><span class="metric-value" style="font-size:16px;{{ $d['saldo_projetado_final'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($d['saldo_projetado_final'], 2, ',', '.') }}</span></div></div>
            <div class="metric-card" style="padding:12px;"><div><span class="metric-label">Diferença</span><span class="metric-value" style="font-size:16px;">R$ {{ number_format($d['saldo_projetado_final'] - $d['saldo_real'], 2, ',', '.') }}</span></div></div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="width:150px;margin:0;"><label>Loja</label><select wire:model.live="lojaFiltro" style="height:38px;font-size:12px;"><option value="">Todas</option>@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
            <div class="field" style="width:130px;margin:0;"><label>Projeção</label><select wire:model.live="diasProjecao" style="height:38px;font-size:12px;"><option value="15">15 dias</option><option value="30">30 dias</option><option value="60">60 dias</option><option value="90">90 dias</option></select></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
            {{-- PROJEÇÃO DIÁRIA --}}
            <div class="card" style="padding:16px;">
                <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:var(--text);">Projeção Diária</h4>
                <div style="max-height:500px;overflow-y:auto;">
                    <table class="data-table" style="font-size:11px;">
                        <thead><tr style="position:sticky;top:0;background:var(--surface);"><th class="data-table-th text-left">Data</th><th class="data-table-th text-right">Entradas</th><th class="data-table-th text-right">Saídas</th><th class="data-table-th text-right">Saldo</th></tr></thead>
                        <tbody>
                            @foreach ($d['projecao'] as $p)
                                @php $isFimSemana = in_array($p['dia_semana'], [6,7]); @endphp
                                <tr class="data-table-tr" style="{{ $isFimSemana ? 'opacity:0.6;' : '' }}{{ $p['saldo'] < 0 ? 'background:color-mix(in srgb,var(--danger)5%,transparent);' : '' }}">
                                    <td class="data-table-td font-mono" style="font-size:10px;">{{ $p['dia'] }}</td>
                                    <td class="data-table-td text-right font-semibold" style="color:var(--success);">@if($p['entradas'] > 0)R$ {{ number_format($p['entradas'], 2, ',', '.') }}@endif</td>
                                    <td class="data-table-td text-right font-semibold" style="color:var(--danger);">@if($p['saidas'] > 0)R$ {{ number_format($p['saidas'], 2, ',', '.') }}@endif</td>
                                    <td class="data-table-td text-right font-bold" style="{{ $p['saldo'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($p['saldo'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PRÓXIMOS VENCIMENTOS --}}
            <div class="card" style="padding:16px;">
                <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:var(--text);">Contas a Pagar — Próximos 7 Dias</h4>
                @forelse ($d['proximos_vencimentos'] as $v)
                    @php $diasRestantes = \Carbon\Carbon::parse($v['data_vencimento'])->diffInDays(now(), false); @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;background:color-mix(in srgb,var(--danger)10%,transparent);color:var(--danger);"><i class="fas fa-file-invoice"></i></span>
                            <div><span style="font-weight:600;color:var(--text);display:block;">{{ $v['descricao'] }}</span><span style="font-size:10px;color:var(--muted);">{{ \Carbon\Carbon::parse($v['data_vencimento'])->format('d/m/Y') }}</span></div>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-weight:700;color:var(--danger);">R$ {{ number_format($v['valor'], 2, ',', '.') }}</span>
                            <span style="font-size:9px;display:block;color:var(--muted);">{{ $diasRestantes < 0 ? 'Vencido há ' . abs($diasRestantes) . ' dias' : ($diasRestantes == 0 ? 'Vence hoje' : 'Em ' . $diasRestantes . ' dias') }}</span>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:24px;color:var(--muted);font-size:12px;">Nenhuma conta a pagar nos próximos 7 dias.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
