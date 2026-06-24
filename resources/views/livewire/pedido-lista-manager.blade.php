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
                <h1 class="page-title">Pedidos de Compra</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Pedidos</span>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <a href="/compras" wire:navigate class="btn-sm btn-primary" style="padding:8px 16px;text-decoration:none;">
                    <i class="fas fa-plus"></i> Novo Pedido
                </a>
                <div class="status-online"><span class="status-dot"></span> Conectado</div>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-shopping-bag"></i></div><div><span class="metric-label">Total Pedidos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalPedidos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-dollar-sign"></i></div><div><span class="metric-label">Total Gasto</span><span class="metric-value" style="font-size:20px;">R$ {{ number_format($this->totalGasto, 2, ',', '.') }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-clock"></i></div><div><span class="metric-label">Aguardando</span><span class="metric-value" style="font-size:20px;">{{ $this->pedidosPendentes }}</span></div></div>
            <div class="metric-card"><div class="metric-icon blue" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-calendar"></i></div><div><span class="metric-label">Este Mês</span><span class="metric-value" style="font-size:20px;">{{ $this->pedidosMes }} ped. / R$ {{ number_format($this->gastoMes, 2, ',', '.') }}</span></div></div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="flex:1;min-width:200px;margin:0;"><label style="font-size:10px;">Buscar</label><input wire:model.live.debounce.300ms="busca" placeholder="#ID ou fornecedor..." style="height:38px;font-size:13px;"></div>
            <div class="field" style="width:140px;margin:0;"><label style="font-size:10px;">Status</label><select wire:model.live="filtroStatus" style="height:38px;font-size:12px;"><option value="">Todos</option><option value="rascunho">Rascunho</option><option value="enviado">Enviado</option><option value="parcialmente_recebido">Parcial</option><option value="recebido">Recebido</option><option value="cancelado">Cancelado</option></select></div>
            <div class="field" style="width:200px;margin:0;"><label style="font-size:10px;">Fornecedor</label><select wire:model.live="filtroFornecedor" style="height:38px;font-size:12px;"><option value="">Todos</option>@foreach ($this->fornecedores as $f)<option value="{{ $f['id'] }}">{{ $f['razao_social'] }}</option>@endforeach</select></div>
            <div class="field" style="width:140px;margin:0;"><label style="font-size:10px;">De</label><input wire:model="dataInicio" type="date" style="height:38px;font-size:12px;"></div>
            <div class="field" style="width:140px;margin:0;"><label style="font-size:10px;">Até</label><input wire:model="dataFim" type="date" style="height:38px;font-size:12px;"></div>
            <button wire:click="limparFiltros" class="btn-sm btn-secondary" style="height:38px;">Limpar</button>
        </div>

        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-list"></i> Pedidos</span></div>
            <div style="overflow-x:auto;">
                @php $pedidos = $this->pedidos(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr>
                        <th class="data-table-th text-left">#</th>
                        <th class="data-table-th text-left">Fornecedor</th>
                        <th class="data-table-th text-left">Data</th>
                        <th class="data-table-th text-right">Valor</th>
                        <th class="data-table-th text-center" style="width:160px;">Progresso</th>
                        <th class="data-table-th text-center">Status</th>
                        <th class="data-table-th text-center" style="width:100px;">Ações</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($pedidos as $p)
                            <tr class="data-table-tr" style="cursor:pointer;" wire:click="verDetalhe({{ $p['id'] }})">
                                <td class="data-table-td font-bold">#{{ $p['id'] }}</td>
                                <td class="data-table-td font-semibold">{{ $p['fornecedor']['razao_social'] ?? '—' }}</td>
                                <td class="data-table-td text-muted">{{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}</td>
                                <td class="data-table-td text-right font-bold">R$ {{ number_format($p['total_pedido'], 2, ',', '.') }}</td>
                                <td class="data-table-td text-center">
                                    @php
                                        $pedidoId = $p['id'];
                                        $resumo = \App\Models\CompraPedidoItem::where('compra_pedido_id', $pedidoId)
                                            ->selectRaw('COALESCE(SUM(quantidade_pedida),0) as total_pedido, COALESCE(SUM(quantidade_recebida),0) as total_recebido')
                                            ->first();
                                        $totalPedido = (float)($resumo->total_pedido ?? 0);
                                        $totalRecebido = (float)($resumo->total_recebido ?? 0);
                                        $pct = $totalPedido > 0 ? round(($totalRecebido / $totalPedido) * 100) : 0;
                                    @endphp
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <div style="flex:1;height:6px;border-radius:3px;background:color-mix(in srgb,var(--text)8%,var(--surface));overflow:hidden;">
                                            <div style="height:100%;border-radius:3px;width:{{ $pct }}%;background:{{ $pct >= 100 ? 'var(--success)' : 'var(--warning)' }};"></div>
                                        </div>
                                        <span style="font-size:10px;font-weight:600;white-space:nowrap;min-width:36px;">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="data-table-td text-center">
                                    @php
                                        $label = ['rascunho'=>'Rascunho','enviado'=>'Enviado','parcialmente_recebido'=>'Parcial','recebido'=>'Recebido','cancelado'=>'Cancelado'][$p['status']] ?? $p['status'];
                                        $sc = ['rascunho'=>'badge-inativo','enviado'=>'badge-ativo','parcialmente_recebido'=>'badge-warning','recebido'=>'badge-ativo','cancelado'=>'badge-inativo'][$p['status']] ?? 'badge-inativo';
                                    @endphp
                                    <span class="badge-sm {{ $sc }}">{{ $label }}</span>
                                </td>
                                <td class="data-table-td text-center" style="white-space:nowrap;">
                                    @if ($p['status'] === 'rascunho')
                                        <button wire:click="enviar({{ $p['id'] }})" class="btn-sm btn-primary" style="padding:4px 10px;font-size:10px;" onclick="event.stopPropagation();"><i class="fas fa-paper-plane"></i></button>
                                        <button wire:click="$set('detalhePedidoId', {{ $p['id'] }})" class="btn-sm btn-secondary" style="padding:4px 10px;font-size:10px;" onclick="event.stopPropagation();"><i class="fas fa-eye"></i></button>
                                    @elseif ($p['status'] === 'enviado' || $p['status'] === 'parcialmente_recebido')
                                        <button wire:click="abrirReceber({{ $p['id'] }})" class="btn-sm btn-success" style="padding:4px 10px;font-size:10px;" onclick="event.stopPropagation();"><i class="fas fa-check"></i> Receber</button>
                                    @elseif ($p['status'] === 'recebido')
                                        <span style="font-size:10px;color:var(--success);">✅</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($this->detalhePedidoId === $p['id'])
                                <tr>
                                    <td colspan="7" style="padding:0;border-bottom:2px solid var(--border);">
                                        @php $itens = \App\Models\CompraPedidoItem::with('variacao')->where('compra_pedido_id', $p['id'])->get(); @endphp
                                        <div style="padding:12px 16px;background:color-mix(in srgb,var(--text)2%,var(--surface));">
                                            <div style="font-size:11px;font-weight:700;color:var(--text);margin-bottom:8px;">Itens do Pedido #{{ $p['id'] }}</div>
                                            <table style="width:100%;border-collapse:collapse;font-size:11px;">
                                                <thead>
                                                    <tr style="border-bottom:1px solid var(--border);">
                                                        <th style="text-align:left;padding:6px 8px;color:var(--muted);font-weight:600;">Produto</th>
                                                        <th style="text-align:right;padding:6px 8px;color:var(--muted);font-weight:600;">Pedido</th>
                                                        <th style="text-align:right;padding:6px 8px;color:var(--muted);font-weight:600;">Recebido</th>
                                                        <th style="text-align:right;padding:6px 8px;color:var(--muted);font-weight:600;">Falta</th>
                                                        <th style="text-align:right;padding:6px 8px;color:var(--muted);font-weight:600;">Custo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($itens as $i)
                                                        @php $pendente = max(0, (float)$i->quantidade_pedida - (float)$i->quantidade_recebida); @endphp
                                                        <tr style="border-bottom:1px solid color-mix(in srgb,var(--text)6%,transparent);">
                                                            <td style="padding:6px 8px;font-weight:600;">{{ $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id }}</td>
                                                            <td style="padding:6px 8px;text-align:right;">{{ number_format((float)$i->quantidade_pedida, 3, ',', '.') }}</td>
                                                            <td style="padding:6px 8px;text-align:right;color:var(--success);">{{ number_format((float)$i->quantidade_recebida, 3, ',', '.') }}</td>
                                                            <td style="padding:6px 8px;text-align:right;color:{{ $pendente > 0 ? 'var(--danger)' : 'var(--success)' }};font-weight:700;">{{ number_format($pendente, 3, ',', '.') }}</td>
                                                            <td style="padding:6px 8px;text-align:right;">R$ {{ number_format((float)$i->custo_unitario, 2, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="7" class="data-table-empty">Nenhum pedido encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pedidos->hasPages())
                <div style="padding:12px 16px;border-top:1px solid var(--border);">
                    {{ $pedidos->links('livewire.pagination-custom') }}
                </div>
            @endif
        </div>
    </div>

    {{-- RECEBER MODAL --}}
    <div x-data="{ open: $wire.entangle('receberModalOpen') }" x-show="open" x-cloak style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;padding:20px;">
        <div style="background:var(--surface);border-radius:16px;border:1px solid var(--border);width:100%;max-width:700px;max-height:80vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--border);">
                <h3 style="margin:0;font-size:15px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px;"><i class="fas fa-check-circle" style="color:var(--success);"></i> Receber Pedido #{{ $this->receberPedidoId }}</h3>
                <button type="button" wire:click="$set('receberModalOpen', false)" style="background:none;border:0;color:var(--muted);cursor:pointer;font-size:24px;">&times;</button>
            </div>
            <div style="padding:16px 20px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                    <div class="field"><label>Nº Nota Fiscal</label><input wire:model="receberNota" placeholder="Opcional"></div>
                    <div class="field"><label>Chave NF-e</label><input wire:model="receberChave" placeholder="Opcional"></div>
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border);">
                            <th style="text-align:left;padding:6px 4px;color:var(--muted);font-weight:600;">Produto</th>
                            <th style="text-align:right;padding:6px 4px;color:var(--muted);font-weight:600;">Pedido</th>
                            <th style="text-align:right;padding:6px 4px;color:var(--muted);font-weight:600;">Receber</th>
                            <th style="text-align:center;padding:6px 4px;color:var(--muted);font-weight:600;">Lote</th>
                            <th style="text-align:center;padding:6px 4px;color:var(--muted);font-weight:600;">Validade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->receberItens as $idx => $r)
                            <tr style="border-bottom:1px solid color-mix(in srgb,var(--text)6%,transparent);">
                                <td style="padding:6px 4px;font-weight:600;">{{ $r['nome'] }}</td>
                                <td style="padding:6px 4px;text-align:right;">{{ number_format($r['pedido'], 3, ',', '.') }}</td>
                                <td style="padding:6px 4px;text-align:right;"><input type="text" inputmode="decimal" wire:model="receberItens.{{ $idx }}.receber" style="width:70px;text-align:right;padding:3px 6px;border:1px solid var(--border);border-radius:5px;font-size:12px;"></td>
                                <td style="padding:6px 4px;text-align:center;"><input type="text" wire:model="receberItens.{{ $idx }}.lote" placeholder="—" style="width:90px;padding:3px 6px;border:1px solid var(--border);border-radius:5px;font-size:11px;"></td>
                                <td style="padding:6px 4px;text-align:center;"><input type="date" wire:model="receberItens.{{ $idx }}.validade" style="width:120px;padding:3px 6px;border:1px solid var(--border);border-radius:5px;font-size:11px;"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="display:flex;gap:8px;margin-top:16px;border-top:1px solid var(--border);padding-top:14px;">
                    <button type="button" wire:click="$set('receberModalOpen', false)" style="flex:1;padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;font-weight:700;color:var(--text);font-size:13px;">Cancelar</button>
                    <button type="button" wire:click="confirmarRecebimento" style="flex:1;padding:10px;border:0;border-radius:8px;background:var(--success);color:#fff;cursor:pointer;font-weight:800;font-size:13px;">Confirmar Recebimento</button>
                </div>
            </div>
        </div>
    </div>
</div>
