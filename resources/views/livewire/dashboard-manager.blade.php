<div>
    {{-- TOAST --}}
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
                <h1 class="page-title">Dashboard</h1>
                <div class="breadcrumb">
                    <span>Início</span>
                    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Dashboard</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        {{-- 6 MÉTRICAS --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div class="metric-card" style="padding:20px;min-height:100px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="metric-label" style="font-size:11px;">Vendas Hoje</span>
                    <span style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;background:color-mix(in srgb,var(--success)10%,transparent);color:var(--success);flex-shrink:0;"><i class="fas fa-shopping-basket"></i></span>
                </div>
                <div class="metric-value" style="font-size:24px;margin:8px 0;">R$ {{ number_format($this->vendasHoje['total'],2,',','.') }}</div>
                <span class="metric-sub" style="font-size:12px;">{{ $this->vendasHoje['qtd'] }} vendas</span>
            </div>
            <div class="metric-card" style="padding:20px;min-height:100px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="metric-label" style="font-size:11px;">Vendas Mês</span>
                    <span style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;background:color-mix(in srgb,#6366f1 10%,transparent);color:#6366f1;flex-shrink:0;"><i class="fas fa-chart-line"></i></span>
                </div>
                <div class="metric-value" style="font-size:24px;margin:8px 0;">R$ {{ number_format($this->vendasMes['total'],2,',','.') }}</div>
                <span class="metric-sub" style="font-size:12px;">{{ $this->vendasMes['qtd'] }} vendas</span>
            </div>
            <div class="metric-card" style="padding:20px;min-height:100px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="metric-label" style="font-size:11px;">Ticket Médio</span>
                    <span style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;background:color-mix(in srgb,#3b82f6 10%,transparent);color:#3b82f6;flex-shrink:0;"><i class="fas fa-hand-holding-dollar"></i></span>
                </div>
                <div class="metric-value" style="font-size:24px;margin:8px 0;">R$ {{ number_format($this->ticketMedio,2,',','.') }}</div>
                <span class="metric-sub" style="font-size:12px;">Valor médio por venda</span>
            </div>
            <div class="metric-card" style="padding:20px;min-height:100px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="metric-label" style="font-size:11px;">Margem Bruta</span>
                    <span style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;background:color-mix(in srgb,var(--warning)10%,transparent);color:var(--warning);flex-shrink:0;"><i class="fas fa-percent"></i></span>
                </div>
                <div class="metric-value" style="font-size:24px;margin:8px 0;">{{ number_format($this->margemBruta,1,',','.') }}%</div>
                <span class="metric-sub" style="font-size:12px;">Sobre o custo</span>
            </div>
            <div class="metric-card" style="padding:20px;min-height:100px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="metric-label" style="font-size:11px;">Estoque Baixo</span>
                    <span style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;background:color-mix(in srgb,var(--danger)10%,transparent);color:var(--danger);flex-shrink:0;"><i class="fas fa-exclamation-triangle"></i></span>
                </div>
                <div class="metric-value" style="font-size:24px;margin:8px 0;color:var(--danger);">{{ $this->estoqueCritico }}</div>
                <span class="metric-sub" style="font-size:12px;">Produtos críticos</span>
            </div>
            <div class="metric-card" style="padding:20px;min-height:100px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span class="metric-label" style="font-size:11px;">Contas Pendentes</span>
                    <span style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;background:color-mix(in srgb,#6366f1 10%,transparent);color:#6366f1;flex-shrink:0;"><i class="fas fa-file-invoice-dollar"></i></span>
                </div>
                <div class="metric-value" style="font-size:24px;margin:8px 0;">R$ {{ number_format($this->contasPendentesTotal,2,',','.') }}</div>
                <span class="metric-sub" style="font-size:12px;">A pagar/receber</span>
            </div>
        </div>

        {{-- GRID CENTRAL --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:20px;">
            {{-- Vendas 7 Dias --}}
            <div class="card" style="padding:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <span class="section-title" style="margin-bottom:0;font-size:10px;"><i class="fas fa-chart-line"></i> Vendas 7 Dias</span>
                    <span style="font-size:9px;background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--muted);padding:2px 8px;border-radius:4px;font-weight:700;">7 dias</span>
                </div>
                <div style="display:flex;gap:6px;align-items:end;height:140px;">
                    @php $maxVal = max(array_column($this->vendas7dias,'total') ?: [1]); @endphp
                    @foreach ($this->vendas7dias as $v)
                        @php $alt = $maxVal > 0 ? (($v['total']/$maxVal)*120) : 0; @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;">
                            <div style="width:100%;max-width:30px;border-radius:4px 4px 0 0;background:var(--primary-500);height:{{ max(4,$alt) }}px;transition:height 0.3s;" title="R$ {{ number_format($v['total'],2,',','.') }}"></div>
                            <span style="font-size:7px;color:var(--muted);margin-top:2px;font-weight:600;">{{ $v['data'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Resumo Financeiro --}}
            <div class="card" style="padding:16px;">
                <div class="section-title" style="font-size:10px;margin-bottom:10px;"><i class="fas fa-coins"></i> Resumo Financeiro</div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;"><span style="color:var(--muted);">A Receber</span><span style="font-weight:700;color:var(--success);">R$ {{ number_format($this->aReceber,2,',','.') }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;"><span style="color:var(--muted);">A Pagar</span><span style="font-weight:700;color:var(--danger);">R$ {{ number_format($this->aPagar,2,',','.') }}</span></div>
                <div style="border-top:1px solid var(--border);padding-top:6px;margin-top:6px;display:flex;justify-content:space-between;font-weight:900;font-size:14px;">
                    <span style="font-size:11px;">Resultado (mês)</span>
                    <span style="color:{{ $this->resultadoLiquido >= 0 ? 'var(--success)' : 'var(--danger)' }};">R$ {{ number_format($this->resultadoLiquido,2,',','.') }}</span>
                </div>
                <div style="margin-top:10px;">
                    <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:600;margin-bottom:2px;"><span style="color:var(--muted);">Margem</span><span style="color:var(--text);">{{ number_format($this->margemBruta,1,',','.') }}%</span></div>
                    <div style="height:6px;border-radius:3px;background:color-mix(in srgb,var(--text)8%,var(--surface));overflow:hidden;">
                        <div style="height:100%;border-radius:3px;width:{{ min(100,$this->margemBruta) }}%;background:#6366f1;"></div>
                    </div>
                </div>
            </div>

            {{-- Resumo --}}
            <div class="card" style="padding:16px;">
                <span class="section-title" style="font-size:10px;margin-bottom:10px;display:block;"><i class="fas fa-chart-pie"></i> Resumo</span>
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:80px;height:80px;border-radius:50%;background:conic-gradient(#7c3aed 0deg 100deg,#f59e0b 100deg 190deg,#3b82f6 190deg 244deg,#0ea5e9 244deg 288deg,#10b981 288deg 360deg);flex-shrink:0;"></div>
                    <div style="flex:1;font-size:10px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span><span style="color:#7c3aed;font-weight:700;">■</span> Vendas</span><span style="font-weight:700;">62%</span></div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span><span style="color:#f59e0b;font-weight:700;">■</span> Compras</span><span style="font-weight:700;">33%</span></div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span><span style="color:#3b82f6;font-weight:700;">■</span> Fixas</span><span style="font-weight:700;">5%</span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW INFERIOR --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:20px;">
            {{-- Estoque Baixo --}}
            <div class="card" style="padding:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="section-title" style="margin-bottom:0;font-size:10px;"><i class="fas fa-boxes-packing"></i> Estoque Baixo</span>
                    <a href="/estoque" style="font-size:9px;color:var(--primary-600);font-weight:700;text-decoration:none;">Ver →</a>
                </div>
                @forelse ($this->estoqueBaixo as $item)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;">
                        <span style="font-weight:600;color:var(--text);">{{ $item['variacao']['nome_completo'] ?? '—' }}</span>
                        <span style="font-weight:700;color:var(--danger);font-size:10px;">{{ number_format($item['quantidade_atual'],0,',','.') }}/{{ $item['estoque_minimo'] }}</span>
                    </div>
                @empty
                    <div style="font-size:11px;color:var(--muted);padding:12px 0;text-align:center;">Nenhum produto com estoque baixo.</div>
                @endforelse
            </div>

            {{-- Vencimentos --}}
            <div class="card" style="padding:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="section-title" style="margin-bottom:0;font-size:10px;"><i class="fas fa-calendar-check"></i> Vencimentos</span>
                    <a href="/lotes" style="font-size:9px;color:var(--primary-600);font-weight:700;text-decoration:none;">Ver →</a>
                </div>
                @forelse ($this->lotesVencendo as $lote)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;">
                        <span style="font-weight:600;color:var(--text);">{{ $lote['variacao']['nome_completo'] ?? '—' }}</span>
                        <span style="font-weight:700;color:var(--warning);font-size:10px;">{{ \Carbon\Carbon::parse($lote['data_validade'])->format('d/m') }}</span>
                    </div>
                @empty
                    <div style="font-size:11px;color:var(--muted);padding:12px 0;text-align:center;">Nenhum lote próximo do vencimento.</div>
                @endforelse
            </div>

            {{-- Últimas Vendas --}}
            <div class="card" style="padding:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="section-title" style="margin-bottom:0;font-size:10px;"><i class="fas fa-cash-register"></i> Últimas Vendas</span>
                    <a href="/vendas" style="font-size:9px;color:var(--primary-600);font-weight:700;text-decoration:none;">Ver →</a>
                </div>
                @forelse ($this->vendasRecentes as $v)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;">
                        <span style="font-weight:600;color:var(--text);">#{{ $v['id'] }}</span>
                        <span style="font-weight:700;color:var(--success);">R$ {{ number_format($v['total'],2,',','.') }}</span>
                    </div>
                @empty
                    <div style="font-size:11px;color:var(--muted);padding:12px 0;text-align:center;">Nenhuma venda recente.</div>
                @endforelse
            </div>
        </div>

        {{-- AÇÕES RÁPIDAS --}}
        <div class="card" style="padding:14px;">
            <span class="section-title" style="font-size:10px;display:block;margin-bottom:10px;"><i class="fas fa-bolt"></i> Ações Rápidas</span>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:6px;">
                <a href="/vendas" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb,var(--success)8%,transparent);text-decoration:none;">
                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--success);font-size:13px;background:color-mix(in srgb,var(--success)12%,transparent);"><i class="fas fa-cart-plus"></i></span>
                    <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;">Nova Venda</span>
                </a>
                <a href="/compras" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb,#3b82f6 8%,transparent);text-decoration:none;">
                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#3b82f6;font-size:13px;background:color-mix(in srgb,#3b82f6 12%,transparent);"><i class="fas fa-file-signature"></i></span>
                    <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;">Novo Pedido</span>
                </a>
                <a href="/compras" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb,#8b5cf6 8%,transparent);text-decoration:none;">
                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#8b5cf6;font-size:13px;background:color-mix(in srgb,#8b5cf6 12%,transparent);"><i class="fas fa-truck"></i></span>
                    <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;">Receber</span>
                </a>
                <a href="/financeiro" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb,var(--danger)8%,transparent);text-decoration:none;">
                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--danger);font-size:13px;background:color-mix(in srgb,var(--danger)12%,transparent);"><i class="fas fa-file-invoice-dollar"></i></span>
                    <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;">Pagar</span>
                </a>
                <a href="/pedidos" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb,#f59e0b 8%,transparent);text-decoration:none;">
                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:13px;background:color-mix(in srgb,#f59e0b 12%,transparent);"><i class="fas fa-chart-pie"></i></span>
                    <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;">Relatórios</span>
                </a>
                <a href="/financeiro" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb,var(--warning)8%,transparent);text-decoration:none;">
                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--warning);font-size:13px;background:color-mix(in srgb,var(--warning)12%,transparent);"><i class="fas fa-arrow-up"></i></span>
                    <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;">Despesas</span>
                </a>
                <a href="/financeiro" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb,var(--success)8%,transparent);text-decoration:none;">
                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--success);font-size:13px;background:color-mix(in srgb,var(--success)12%,transparent);"><i class="fas fa-arrow-down"></i></span>
                    <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;">Receitas</span>
                </a>
                <a href="/admin/fiscal" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb,#6366f1 8%,transparent);text-decoration:none;">
                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#6366f1;font-size:13px;background:color-mix(in srgb,#6366f1 12%,transparent);"><i class="fas fa-cog"></i></span>
                    <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;">Config.</span>
                </a>
            </div>
        </div>
    </div>
</div>
