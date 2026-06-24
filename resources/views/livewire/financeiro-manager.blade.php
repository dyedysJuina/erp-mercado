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
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad">
        <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Financeiro</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Financeiro</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
        <div class="metrics-grid" style="grid-template-columns:repeat(5,1fr);">
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Saldo em Caixa</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->saldoEmCaixa, 2, ',', '.') }}</span><span class="metric-sub">Receitas - Despesas pagas</span></div><div class="metric-icon green" style="width:32px;height:32px;font-size:12px;flex-shrink:0;"><i class="fas fa-wallet"></i></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">A Receber</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->aReceber, 2, ',', '.') }}</span><span class="metric-sub">Títulos pendentes</span></div><div class="metric-icon blue" style="width:32px;height:32px;font-size:12px;flex-shrink:0;"><i class="fas fa-arrow-down"></i></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">A Pagar</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->aPagar, 2, ',', '.') }}</span><span class="metric-sub">Fornecedores pendentes</span></div><div class="metric-icon" style="width:32px;height:32px;border-radius:8px;font-size:12px;background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);flex-shrink:0;"><i class="fas fa-arrow-up"></i></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Resultado do Mês</span><span class="metric-value" style="font-size:20px;color:{{ $this->resultadoMes >=0 ? 'var(--success)' : 'var(--danger)' }};">R$ {{ number_format($this->resultadoMes, 2, ',', '.') }}</span><span class="metric-sub">Receitas - Despesas</span></div><div class="metric-icon purple" style="width:32px;height:32px;font-size:12px;flex-shrink:0;"><i class="fas fa-chart-pie"></i></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Fluxo Caixa 7d</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->fluxoCaixa7d, 2, ',', '.') }}</span><span class="metric-sub">Previsão 7 dias</span></div><div class="metric-icon amber" style="width:32px;height:32px;font-size:12px;flex-shrink:0;"><i class="fas fa-scale-balanced"></i></div></div>
        </div>
        <div class="grid-2col-custom" style="align-items:start;">
            {{-- LEFT: Lançamentos --}}
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="data-table-wrap">
                    <div class="data-table-header"><span class="data-table-title" style="font-size:10px;"><i class="fas fa-receipt"></i> Lançamentos</span>
                        <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
                            <button wire:click="abrirModal" class="btn-sm btn-primary" style="height:28px;font-size:10px;padding:0 8px;"><i class="fas fa-plus"></i> Novo</button>
                            <input wire:model.live.debounce.300ms="busca" placeholder="Buscar..." style="height:28px;padding:0 6px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:120px;">
                            <select wire:model.live="filtroTipo" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:70px;"><option value="">Todos</option><option value="receita">Rec.</option><option value="despesa">Desp.</option></select>
                            <select wire:model.live="filtroStatus" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:80px;"><option value="">Todos</option><option value="pendente">Pend.</option><option value="pago">Pago</option><option value="cancelado">Canc.</option></select>
                            <input wire:model="dataInicio" type="date" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:95px;">
                            <input wire:model="dataFim" type="date" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:95px;">
                        </div>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="data-table" style="font-size:12px;">
                            <thead><tr><th class="data-table-th text-left">Data</th><th class="data-table-th text-left">Descrição</th><th class="data-table-th text-left">Categoria</th><th class="data-table-th text-center">Tipo</th><th class="data-table-th text-right">Valor</th><th class="data-table-th text-center">Status</th><th class="data-table-th text-center" style="width:80px;">Ações</th></tr></thead>
                            <tbody>
                                @php $lancamentos = $this->lancamentos; @endphp
                                @forelse ($lancamentos as $l)
                                    <tr class="data-table-tr">
                                        <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ \Carbon\Carbon::parse($l['data_vencimento'])->format('d/m/Y') }}</td>
                                        <td class="data-table-td font-semibold">{{ $l['descricao'] }}</td>
                                        <td class="data-table-td text-muted" style="font-size:11px;">{{ $l['categoria']['nome'] ?? '—' }}</td>
                                        <td class="data-table-td text-center"><span style="width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;{{ $l['tipo'] === 'receita' ? 'background:color-mix(in srgb, var(--success) 12%, transparent);color:var(--success);' : 'background:color-mix(in srgb, var(--danger) 12%, transparent);color:var(--danger);' }}"><i class="fas {{ $l['tipo'] === 'receita' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i></span></td>
                                        <td class="data-table-td text-right font-bold" style="{{ $l['tipo'] === 'receita' ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($l['valor'], 2, ',', '.') }}</td>
                                        <td class="data-table-td text-center"><span class="badge-sm {{ $l['status'] === 'pago' ? 'badge-ativo' : ($l['status'] === 'cancelado' ? 'badge-inativo' : 'badge-warning') }}" style="font-size:9px;">{{ $l['status'] }}</span></td>
                                        <td class="data-table-td text-center"><div style="display:flex;gap:4px;">@if($l['status']==='pendente')<button wire:click="pagar({{ $l['id'] }})" style="font-size:10px;background:none;border:1px solid var(--border);border-radius:4px;padding:2px 6px;cursor:pointer;" title="Pagar">✓</button>@elseif($l['status']==='pago')<button wire:click="estornar({{ $l['id'] }})" style="font-size:10px;background:none;border:1px solid var(--border);border-radius:4px;padding:2px 6px;cursor:pointer;" title="Estornar">↩</button>@endif<button wire:click="selecionar({{ $l['id'] }})" style="font-size:10px;background:none;border:1px solid var(--border);border-radius:4px;padding:2px 6px;cursor:pointer;" title="Editar">✎</button><button wire:click="excluir({{ $l['id'] }})" wire:confirm="Excluir?" style="font-size:10px;background:none;border:1px solid var(--border);border-radius:4px;padding:2px 6px;cursor:pointer;color:var(--danger);" title="Excluir">✕</button></div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="data-table-empty">Nenhum lançamento encontrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($lancamentos->hasPages())
                        <div style="padding:12px 16px;border-top:1px solid var(--border);">
                            {{ $lancamentos->links('livewire.pagination-custom') }}
                        </div>
                    @endif
                </div>
            </div>
            {{-- RIGHT: Sidebar --}}
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-calendar-check"></i> Próximos Vencimentos</div>
                    <div class="sidebar-card-body" style="padding:8px 12px;">
                        @forelse ($this->proximosVencimentos as $v)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;{{ $v['tipo'] === 'despesa' ? 'background:color-mix(in srgb, var(--danger) 10%, transparent);color:var(--danger);' : 'background:color-mix(in srgb, var(--success) 10%, transparent);color:var(--success);' }}"><i class="fas {{ $v['tipo'] === 'despesa' ? 'fa-file-invoice' : 'fa-hand-holding' }}"></i></span>
                                    <div><span style="font-weight:600;color:var(--text);display:block;">{{ $v['descricao'] }}</span><span style="font-size:10px;color:var(--muted);">Venc: {{ \Carbon\Carbon::parse($v['data_vencimento'])->format('d/m/Y') }}</span></div>
                                </div>
                                <div style="text-align:right;"><span style="font-weight:700;color:var(--text);display:block;">R$ {{ number_format($v['valor'], 2, ',', '.') }}</span><span class="badge-sm {{ $v['status'] === 'pendente' && \Carbon\Carbon::parse($v['data_vencimento'])->isPast() ? 'badge-inativo' : ($v['status'] === 'pendente' && \Carbon\Carbon::parse($v['data_vencimento'])->isToday() ? 'badge-warning' : ($v['status'] === 'pendente' ? 'badge-warning' : 'badge-ativo')) }}" style="font-size:8px;">{{ \Carbon\Carbon::parse($v['data_vencimento'])->isPast() && $v['status'] === 'pendente' ? 'Vencido' : (\Carbon\Carbon::parse($v['data_vencimento'])->isToday() && $v['status'] === 'pendente' ? 'Vence hoje' : $v['status']) }}</span></div>
                            </div>
                        @empty
                            <div style="padding:20px;text-align:center;color:var(--muted);font-size:12px;">Nenhum vencimento próximo.</div>
                        @endforelse
                    </div>
                </div>
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-chart-pie"></i> Resumo por Categoria</div>
                    <div class="sidebar-card-body" style="padding:12px 16px;">
                        @php $totalReceitas = \App\Models\FinanceiroLancamento::where('tipo', 'receita')->sum('valor'); $totalDespesas = \App\Models\FinanceiroLancamento::where('tipo', 'despesa')->sum('valor'); $totalGeral = $totalReceitas + $totalDespesas; @endphp
                        <div style="margin-bottom:8px;"><div style="display:flex;justify-content:space-between;font-size:11px;font-weight:600;margin-bottom:2px;"><span style="color:var(--success);">Receitas</span><span style="color:var(--text);">R$ {{ number_format($totalReceitas, 2, ',', '.') }} <span style="color:var(--muted);font-weight:400;">{{ $totalGeral > 0 ? round(($totalReceitas/$totalGeral)*100) : 0 }}%</span></span></div><div style="height:6px;border-radius:3px;background:color-mix(in srgb, var(--text) 8%, var(--surface));overflow:hidden;"><div style="height:100%;border-radius:3px;width:{{ $totalGeral > 0 ? round(($totalReceitas/$totalGeral)*100) : 0 }}%;background:var(--success);"></div></div></div>
                        <div style="margin-bottom:8px;"><div style="display:flex;justify-content:space-between;font-size:11px;font-weight:600;margin-bottom:2px;"><span style="color:var(--danger);">Despesas</span><span style="color:var(--text);">R$ {{ number_format($totalDespesas, 2, ',', '.') }} <span style="color:var(--muted);font-weight:400;">{{ $totalGeral > 0 ? round(($totalDespesas/$totalGeral)*100) : 0 }}%</span></span></div><div style="height:6px;border-radius:3px;background:color-mix(in srgb, var(--text) 8%, var(--surface));overflow:hidden;"><div style="height:100%;border-radius:3px;width:{{ $totalGeral > 0 ? round(($totalDespesas/$totalGeral)*100) : 0 }}%;background:var(--danger);"></div></div></div>
                        <div style="border-top:2px solid var(--border);padding-top:8px;display:flex;justify-content:space-between;font-weight:900;font-size:14px;color:var(--text);"><span>Saldo Líquido</span><span style="color:{{ ($totalReceitas - $totalDespesas) >= 0 ? 'var(--success)' : 'var(--danger)' }};">R$ {{ number_format($totalReceitas - $totalDespesas, 2, ',', '.') }}</span></div>
                    </div>
                </div>
                <div class="sidebar-card" style="padding:14px;">
                    <span class="section-title" style="font-size:10px;display:block;margin-bottom:10px;"><i class="fas fa-bolt"></i> Ações Rápidas</span>
                    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:4px;">
                        <button wire:click="abrirModal('receita')" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb, var(--success) 8%, transparent);border:0;cursor:pointer;"><span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--success);font-size:14px;background:color-mix(in srgb, var(--success) 12%, transparent);"><i class="fas fa-hand-holding-dollar"></i></span><span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;line-height:1.2;">Receber</span></button>
                        <button wire:click="abrirModal('despesa')" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb, var(--danger) 8%, transparent);border:0;cursor:pointer;"><span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--danger);font-size:14px;background:color-mix(in srgb, var(--danger) 12%, transparent);"><i class="fas fa-file-invoice-dollar"></i></span><span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;line-height:1.2;">Pagar</span></button>
                        <a href="/pedidos" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb, #3b82f6 8%, transparent);border:0;cursor:pointer;text-decoration:none;"><span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#3b82f6;font-size:14px;background:color-mix(in srgb, #3b82f6 12%, transparent);"><i class="fas fa-shopping-bag"></i></span><span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;line-height:1.2;">Pedidos</span></a>
                        <a href="/relatorios" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb, #8b5cf6 8%, transparent);border:0;cursor:pointer;text-decoration:none;"><span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#8b5cf6;font-size:14px;background:color-mix(in srgb, #8b5cf6 12%, transparent);"><i class="fas fa-chart-bar"></i></span><span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;line-height:1.2;">Relatórios</span></a>
                        <a href="/financeiro/conciliacao" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb, #f59e0b 8%, transparent);border:0;cursor:pointer;text-decoration:none;"><span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:14px;background:color-mix(in srgb, #f59e0b 12%, transparent);"><i class="fas fa-check-double"></i></span><span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;line-height:1.2;">Conciliação</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;visibility:hidden;">
        <div x-data="{ open: $wire.entangle('modalOpen') }" x-show="open" x-cloak style="position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;padding:20px;visibility:visible;">
            <div style="background:var(--surface);border-radius:16px;border:1px solid var(--border);width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--border);">
                    <h3 style="margin:0;font-size:15px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px;"><i class="fas {{ $this->editandoId ? 'fa-edit' : 'fa-plus' }}" style="color:var(--primary-600);"></i> {{ $this->editandoId ? 'Editar' : 'Novo' }} Lançamento</h3>
                    <button type="button" wire:click="fecharModal" style="background:none;border:0;color:var(--muted);cursor:pointer;font-size:24px;">&times;</button>
                </div>
                <form wire:submit="salvar" style="padding:16px 20px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;"><div class="field"><label>Descrição *</label><input wire:model="descricao" placeholder="Ex: Compra de mercadorias">@error('descricao')<div class="err">{{ $message }}</div>@enderror</div><div class="field"><label>Valor *</label><input wire:model="valor" placeholder="0,00">@error('valor')<div class="err">{{ $message }}</div>@enderror</div></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:10px;"><div class="field"><label>Tipo</label><select wire:model="tipo"><option value="receita">Receita</option><option value="despesa">Despesa</option></select></div><div class="field"><label>Status</label><select wire:model="status"><option value="pendente">Pendente</option><option value="pago">Pago</option><option value="cancelado">Cancelado</option></select></div><div class="field"><label>Vencimento *</label><input wire:model="data_vencimento" type="date">@error('data_vencimento')<div class="err">{{ $message }}</div>@enderror</div></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:10px;"><div class="field"><label>Categoria</label><select wire:model="categoria_id"><option value="">Selecione...</option>@foreach ($this->categorias as $c)<option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>@endforeach</select></div><div class="field"><label>Conta</label><select wire:model="conta_id"><option value="">Selecione...</option>@foreach ($this->contas as $c)<option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>@endforeach</select></div><div class="field"><label>Centro Custo</label><select wire:model="centro_custo_id"><option value="">Selecione...</option>@foreach ($this->centrosCusto as $c)<option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>@endforeach</select></div></div>
                    <div style="margin-bottom:10px;"><div class="field"><label>Observação</label><input wire:model="observacao" placeholder="Observações adicionais..."></div></div>
                    <div style="display:flex;gap:8px;border-top:1px solid var(--border);padding-top:14px;"><button type="button" wire:click="fecharModal" style="flex:1;padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;font-weight:700;color:var(--text);font-size:13px;">Cancelar</button><button type="submit" style="flex:1;padding:10px;border:0;border-radius:8px;background:var(--success);color:#fff;cursor:pointer;font-weight:800;font-size:13px;"><span wire:loading.remove>{{ $this->editandoId ? 'Atualizar' : 'Salvar' }}</span><span wire:loading>Salvando...</span></button></div>
                </form>
            </div>
        </div>
    </div>
</div>
