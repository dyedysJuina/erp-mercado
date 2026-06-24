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
        <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Lotes e Validades</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Lotes</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
        <div class="metrics-grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="metric-card"><div class="metric-icon" style="background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);width:40px;height:40px;border-radius:10px;font-size:16px;"><i class="fas fa-times-circle"></i></div><div><span class="metric-label" style="font-size:10px;">Vencidos</span><span class="metric-value" style="font-size:22px;color:var(--danger);">{{ $this->vencidos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon" style="background:color-mix(in srgb,var(--warning)12%,transparent);color:var(--warning);width:40px;height:40px;border-radius:10px;font-size:16px;"><i class="fas fa-exclamation-triangle"></i></div><div><span class="metric-label" style="font-size:10px;">Vencem em 30 dias</span><span class="metric-value" style="font-size:22px;color:var(--warning);">{{ $this->expirando30dias }}</span></div></div>
            <div class="metric-card"><div class="metric-icon" style="background:color-mix(in srgb,var(--success)12%,transparent);color:var(--success);width:40px;height:40px;border-radius:10px;font-size:16px;"><i class="fas fa-box"></i></div><div><span class="metric-label" style="font-size:10px;">Total de Lotes</span><span class="metric-value" style="font-size:22px;">{{ $this->totalLotes }}</span></div></div>
        </div>
        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="flex:1;min-width:200px;margin:0;"><label style="font-size:10px;">Buscar produto</label><input wire:model.live.debounce.300ms="busca" placeholder="Nome do produto ou SKU..." style="height:38px;font-size:13px;"></div>
            <div class="field" style="width:140px;margin:0;"><label style="font-size:10px;">Status</label><select wire:model.live="filtroStatus" style="height:38px;font-size:12px;"><option value="todos">Todos</option><option value="vencido">Vencido</option><option value="expirando">Vence em 30 dias</option><option value="valido">Válido</option></select></div>
            <div class="field" style="width:130px;margin:0;"><label style="font-size:10px;">De</label><input wire:model="dataInicio" type="date" style="height:38px;font-size:12px;"></div>
            <div class="field" style="width:130px;margin:0;"><label style="font-size:10px;">Até</label><input wire:model="dataFim" type="date" style="height:38px;font-size:12px;"></div>
            <button wire:click="limparFiltros" class="btn-sm btn-secondary" style="height:38px;">Limpar</button>
        </div>
        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-calendar-alt"></i> Lotes</span></div>
            <div style="overflow-x:auto;">
                @php $lotes = $this->lotes(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr><th class="data-table-th text-left">Produto</th><th class="data-table-th text-left">Lote</th><th class="data-table-th text-center">Validade</th><th class="data-table-th text-right">Quantidade</th><th class="data-table-th text-center">Status</th></tr></thead>
                    <tbody>
                        @forelse ($lotes as $l)
                            @php
                                $validade = \Carbon\Carbon::parse($l['data_validade']);
                                $dias = now()->diffInDays($validade, false);
                                $status = $dias < 0 ? 'vencido' : ($dias <= 7 ? 'urgente' : ($dias <= 30 ? 'alerta' : 'ok'));
                                $statusLabel = $dias < 0 ? 'Vencido há ' . abs((int)$dias) . ' dias' : ($dias <= 7 ? '⚠️ Vence em ' . (int)$dias . ' dias' : ($dias <= 30 ? 'Vence em ' . (int)$dias . ' dias' : 'Válido'));
                            @endphp
                            <tr class="data-table-tr" style="{{ $status === 'vencido' ? 'opacity:0.7;' : '' }}">
                                <td class="data-table-td font-bold">{{ $l['variacao']['nome_completo'] ?? '—' }}<span style="display:block;font-size:10px;color:var(--muted);font-family:monospace;">{{ $l['variacao']['sku'] ?? '' }}</span></td>
                                <td class="data-table-td font-mono" style="font-size:11px;">{{ $l['numero_lote'] ?? '—' }}</td>
                                <td class="data-table-td text-center font-bold font-mono" style="{{ $dias < 0 ? 'color:var(--danger);' : ($dias <= 7 ? 'color:var(--danger);' : ($dias <= 30 ? 'color:var(--warning);' : '')) }}">{{ $validade->format('d/m/Y') }}</td>
                                <td class="data-table-td text-right font-bold">{{ number_format($l['quantidade_atual'], 3, ',', '.') }}</td>
                                <td class="data-table-td text-center"><span class="badge-sm {{ $status === 'vencido' ? 'badge-inativo' : ($status === 'urgente' ? 'badge-inativo' : ($status === 'alerta' ? 'badge-warning' : 'badge-ativo')) }}">{{ $statusLabel }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="data-table-empty">Nenhum lote encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($lotes->hasPages())
                <div style="padding:12px 16px;border-top:1px solid var(--border);">
                    {{ $lotes->links('livewire.pagination-custom') }}
                </div>
            @endif
        </div>
    </div>
</div>
