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
                <h1 class="page-title">DRE — Resultado do Exercício</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Relatórios</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">DRE</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="width:150px;margin:0;"><label>Loja</label><select wire:model.live="lojaFiltro" style="height:38px;font-size:12px;"><option value="">Todas</option>@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
            <div class="field" style="width:100px;margin:0;"><label>Mês</label><select wire:model.live="mes" style="height:38px;font-size:12px;">@foreach (range(1,12) as $m)<option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>@endforeach</select></div>
            <div class="field" style="width:100px;margin:0;"><label>Ano</label><select wire:model.live="ano" style="height:38px;font-size:12px;">@for ($a = now()->year - 2; $a <= now()->year; $a++)<option value="{{ $a }}">{{ $a }}</option>@endfor</select></div>
        </div>

        @php $d = $this->dre; @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
            {{-- DEMONSTRATIVO --}}
            <div class="card" style="padding:20px;">
                <h3 style="margin:0 0 16px;font-size:16px;font-weight:800;color:var(--text);">Demonstrativo — {{ $d['mes'] }}/{{ $d['ano'] }}</h3>

                <div style="padding:12px 0;border-bottom:2px solid var(--text);">
                    <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;color:var(--text);text-transform:uppercase;">
                        <span>Receita Bruta (Vendas)</span>
                        <span style="color:var(--success);">R$ {{ number_format($d['receita_bruta'], 2, ',', '.') }}</span>
                    </div>
                    @if ($d['receitas_financeiras'] > 0)
                        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--muted);margin-top:4px;">
                            <span>Receitas Financeiras</span>
                            <span style="color:var(--success);">R$ {{ number_format($d['receitas_financeiras'], 2, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text);">
                        <span>(−) Custo das Mercadorias (CMV)</span>
                        <span style="color:var(--danger);">− R$ {{ number_format($d['cmv'], 2, ',', '.') }}</span>
                    </div>
                </div>

                <div style="padding:12px 0;border-bottom:2px solid var(--text);">
                    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:700;color:var(--text);">
                        <span>= Lucro Bruto</span>
                        <span style="{{ $d['lucro_bruto'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($d['lucro_bruto'], 2, ',', '.') }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--muted);text-align:right;">Margem Bruta: {{ $d['margem_bruta'] }}%</div>
                </div>

                <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text);">
                        <span>(−) Despesas Operacionais</span>
                        <span style="color:var(--danger);">− R$ {{ number_format($d['despesas'], 2, ',', '.') }}</span>
                    </div>
                </div>

                <div style="padding:16px 0 0;">
                    <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:900;">
                        <span>Resultado Líquido</span>
                        <span style="{{ $d['resultado_liquido'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">
                            R$ {{ number_format($d['resultado_liquido'], 2, ',', '.') }}
                        </span>
                    </div>
                    <div style="font-size:13px;color:var(--muted);text-align:right;margin-top:4px;">Margem Líquida: {{ $d['margem_liquida'] }}%</div>
                </div>
            </div>

            {{-- MÉTRICAS --}}
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div class="metric-card" style="padding:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div><span class="metric-label" style="font-size:10px;">Margem Bruta</span><span class="metric-value" style="font-size:24px;{{ $d['margem_bruta'] >= 20 ? 'color:var(--success);' : ($d['margem_bruta'] >= 10 ? 'color:var(--warning);' : 'color:var(--danger);') }}">{{ $d['margem_bruta'] }}%</span></div>
                        <div class="metric-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-percentage"></i></div>
                    </div>
                    <div style="margin-top:8px;height:6px;border-radius:3px;background:color-mix(in srgb,var(--text)8%,var(--surface));overflow:hidden;">
                        <div style="height:100%;border-radius:3px;width:{{ min(100, $d['margem_bruta'] * 3) }}%;background:{{ $d['margem_bruta'] >= 20 ? 'var(--success)' : ($d['margem_bruta'] >= 10 ? 'var(--warning)' : 'var(--danger)') }};"></div>
                    </div>
                </div>

                <div class="metric-card" style="padding:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div><span class="metric-label" style="font-size:10px;">Margem Líquida</span><span class="metric-value" style="font-size:24px;{{ $d['margem_liquida'] >= 0 ? 'color:var(--success);' : 'color:var(--danger);' }}">{{ $d['margem_liquida'] }}%</span></div>
                        <div class="metric-icon blue" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>

                <div class="metric-card" style="padding:16px;">
                    <div><span class="metric-label" style="font-size:10px;">Contas a Pagar (mês)</span><span class="metric-value" style="font-size:20px;color:var(--danger);">R$ {{ number_format($d['a_pagar'], 2, ',', '.') }}</span></div>
                </div>

                <div class="metric-card" style="padding:16px;">
                    <div><span class="metric-label" style="font-size:10px;">Custo Total (CMV + Despesas)</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($d['cmv'] + $d['despesas'], 2, ',', '.') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
