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
        <div class="header-row">
            <div>
                <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                <h1 class="page-title">Conciliação Bancária</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Financeiro</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Conciliação</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        <div class="metrics-grid" style="grid-template-columns:repeat(4,1fr);">
            <div class="metric-card" style="padding:14px;">
                <div>
                    <span class="metric-label">A Conciliar</span>
                    <span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->totais['pendente_total'], 2, ',', '.') }}</span>
                    <span class="metric-sub">{{ $this->totais['pendente_count'] }} lançamento(s)</span>
                </div>
                <div class="metric-icon amber" style="width:32px;height:32px;font-size:12px;flex-shrink:0;"><i class="fas fa-clock"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div>
                    <span class="metric-label">Conciliado</span>
                    <span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->totais['recon_total'], 2, ',', '.') }}</span>
                    <span class="metric-sub">{{ $this->totais['recon_count'] }} lançamento(s)</span>
                </div>
                <div class="metric-icon green" style="width:32px;height:32px;font-size:12px;flex-shrink:0;"><i class="fas fa-check-double"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div>
                    <span class="metric-label">Conciliação (%)</span>
                    <span class="metric-value" style="font-size:20px;">{{ $this->totais['pendente_total'] + $this->totais['recon_total'] > 0 ? round(($this->totais['recon_total'] / ($this->totais['pendente_total'] + $this->totais['recon_total'])) * 100) : 0 }}%</span>
                    <span class="metric-sub">Pagos conciliados</span>
                </div>
                <div class="metric-icon purple" style="width:32px;height:32px;font-size:12px;flex-shrink:0;"><i class="fas fa-percent"></i></div>
            </div>
            <div class="metric-card" style="padding:14px;">
                <div>
                    <span class="metric-label">Data Conciliação</span>
                    <span style="display:block;margin-top:4px;">
                        <input wire:model="dataConcilia" type="date" style="padding:4px 6px;border:1px solid var(--border);border-radius:5px;font-size:11px;width:100%;">
                    </span>
                    <span class="metric-sub">Usada nos registros</span>
                </div>
                <div class="metric-icon blue" style="width:32px;height:32px;font-size:12px;flex-shrink:0;"><i class="fas fa-calendar"></i></div>
            </div>
        </div>

        <div class="tab-nav" style="margin-bottom:16px;">
            <button wire:click="$set('aba', 'pendentes')" class="{{ $this->aba === 'pendentes' ? 'tab-active' : '' }}"><i class="fas fa-clock"></i> Pendentes</button>
            <button wire:click="$set('aba', 'reconciliados')" class="{{ $this->aba === 'reconciliados' ? 'tab-active' : '' }}"><i class="fas fa-check-double"></i> Conciliados</button>
        </div>

        <div class="grid-2col-custom" style="align-items:start;">
            <div style="display:flex;flex-direction:column;gap:16px;">
                @if ($this->aba === 'pendentes')
                    <div class="data-table-wrap">
                        <div class="data-table-header">
                            <span class="data-table-title" style="font-size:10px;"><i class="fas fa-clock"></i> Lançamentos a Conciliar</span>
                            <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
                                <input wire:model.live.debounce.300ms="busca" placeholder="Buscar..." style="height:28px;padding:0 6px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:120px;">
                                <select wire:model.live="filtroTipo" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:70px;"><option value="">Todos</option><option value="receita">Rec.</option><option value="despesa">Desp.</option></select>
                                <input wire:model="dataInicio" type="date" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:95px;">
                                <input wire:model="dataFim" type="date" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:95px;">
                                @if (count($this->loteIds) > 0)
                                    <button wire:click="conciliarLote" class="btn-sm btn-primary" style="height:28px;font-size:10px;padding:0 8px;"><i class="fas fa-check"></i> Conciliar {{ count($this->loteIds) }}</button>
                                @endif
                            </div>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="data-table" style="font-size:12px;">
                                <thead>
                                    <tr>
                                        <th class="data-table-th text-center" style="width:32px;"><input type="checkbox" wire:model.live="selectAll" style="accent-color:var(--success);"></th>
                                        <th class="data-table-th text-left">Data Pag.</th>
                                        <th class="data-table-th text-left">Descrição</th>
                                        <th class="data-table-th text-center">Tipo</th>
                                        <th class="data-table-th text-right">Valor</th>
                                        <th class="data-table-th text-center" style="width:60px;">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $pendentesLista = $this->pendentes(); @endphp
                                    @forelse ($pendentesLista as $l)
                                        <tr class="data-table-tr">
                                            <td class="data-table-td text-center"><input type="checkbox" wire:model="loteIds" value="{{ $l['id'] }}" style="accent-color:var(--success);"></td>
                                            <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ $l['data_pagamento'] ? \Carbon\Carbon::parse($l['data_pagamento'])->format('d/m/Y') : '—' }}</td>
                                            <td class="data-table-td font-semibold">{{ $l['descricao'] }}</td>
                                            <td class="data-table-td text-center">
                                                <span style="width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;{{ $l['tipo'] === 'receita' ? 'background:color-mix(in srgb, var(--success) 12%, transparent);color:var(--success);' : 'background:color-mix(in srgb, var(--danger) 12%, transparent);color:var(--danger);' }}">
                                                    <i class="fas {{ $l['tipo'] === 'receita' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                </span>
                                            </td>
                                            <td class="data-table-td text-right font-bold" style="{{ $l['tipo'] === 'receita' ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($l['valor'], 2, ',', '.') }}</td>
                                            <td class="data-table-td text-center">
                                                <button wire:click="conciliar({{ $l['id'] }})" style="background:color-mix(in srgb, var(--success) 10%, transparent);border:0;border-radius:5px;padding:3px 8px;color:var(--success);cursor:pointer;font-size:10px;font-weight:700;"><i class="fas fa-check"></i></button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="data-table-empty">Nenhum lançamento pendente de conciliação.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($pendentesLista->hasPages())
                            <div style="padding:12px 16px;border-top:1px solid var(--border);">
                                {{ $pendentesLista->links('livewire.pagination-custom') }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="data-table-wrap">
                        <div class="data-table-header">
                            <span class="data-table-title" style="font-size:10px;"><i class="fas fa-check-double"></i> Lançamentos Conciliados</span>
                            <div style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
                                <select wire:model.live="filtroTipo" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:70px;"><option value="">Todos</option><option value="receita">Rec.</option><option value="despesa">Desp.</option></select>
                                <input wire:model="dataInicio" type="date" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:95px;">
                                <input wire:model="dataFim" type="date" style="height:28px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:10px;width:95px;">
                            </div>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="data-table" style="font-size:12px;">
                                <thead>
                                    <tr>
                                        <th class="data-table-th text-left">Data Conc.</th>
                                        <th class="data-table-th text-left">Descrição</th>
                                        <th class="data-table-th text-center">Tipo</th>
                                        <th class="data-table-th text-right">Valor</th>
                                        <th class="data-table-th text-left">Por</th>
                                        <th class="data-table-th text-center" style="width:60px;">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $reconciliadosLista = $this->reconciliados(); @endphp
                                    @forelse ($reconciliadosLista as $l)
                                        <tr class="data-table-tr">
                                            <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ \Carbon\Carbon::parse($l['reconcilied_at'])->format('d/m/Y H:i') }}</td>
                                            <td class="data-table-td font-semibold">{{ $l['descricao'] }}</td>
                                            <td class="data-table-td text-center">
                                                <span style="width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;{{ $l['tipo'] === 'receita' ? 'background:color-mix(in srgb, var(--success) 12%, transparent);color:var(--success);' : 'background:color-mix(in srgb, var(--danger) 12%, transparent);color:var(--danger);' }}">
                                                    <i class="fas {{ $l['tipo'] === 'receita' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                </span>
                                            </td>
                                            <td class="data-table-td text-right font-bold" style="{{ $l['tipo'] === 'receita' ? 'color:var(--success);' : 'color:var(--danger);' }}">R$ {{ number_format($l['valor'], 2, ',', '.') }}</td>
                                            <td class="data-table-td text-muted" style="font-size:11px;">{{ $l['reconcilied_by']['name'] ?? '—' }}</td>
                                            <td class="data-table-td text-center">
                                                <button wire:click="estornarConciliacao({{ $l['id'] }})" wire:confirm="Estornar conciliação?" style="background:none;border:0;color:var(--danger);cursor:pointer;font-size:12px;padding:4px;" title="Estornar"><i class="fas fa-undo"></i></button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="data-table-empty">Nenhum lançamento conciliado.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($reconciliadosLista->hasPages())
                            <div style="padding:12px 16px;border-top:1px solid var(--border);">
                                {{ $reconciliadosLista->links('livewire.pagination-custom') }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-info-circle"></i> O que é Conciliação?</div>
                    <div class="sidebar-card-body" style="padding:12px 16px;font-size:12px;color:var(--text);line-height:1.6;">
                        <p style="margin:0 0 8px;">A conciliação bancária confere se os lançamentos pagos no sistema correspondem às movimentações reais da conta bancária.</p>
                        <p style="margin:0;"><strong>Passos:</strong></p>
                        <ol style="margin:4px 0 0 16px;padding-left:16px;">
                            <li>Marque um ou mais lançamentos pagos</li>
                            <li>Clique em "Conciliar" para confirmar</li>
                            <li>O sistema registra a data e quem conciliou</li>
                        </ol>
                    </div>
                </div>
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-bolt"></i> Ações Rápidas</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;padding:12px 14px;">
                        <a href="/financeiro" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb, #3b82f6 8%, transparent);border:0;cursor:pointer;text-decoration:none;">
                            <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#3b82f6;font-size:14px;background:color-mix(in srgb, #3b82f6 12%, transparent);"><i class="fas fa-receipt"></i></span>
                            <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;line-height:1.2;">Lançamentos</span>
                        </a>
                        <a href="/relatorios" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px;border-radius:8px;background:color-mix(in srgb, #8b5cf6 8%, transparent);border:0;cursor:pointer;text-decoration:none;">
                            <span style="width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#8b5cf6;font-size:14px;background:color-mix(in srgb, #8b5cf6 12%, transparent);"><i class="fas fa-chart-bar"></i></span>
                            <span style="font-size:8px;font-weight:700;color:var(--text);text-align:center;line-height:1.2;">Relatórios</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
