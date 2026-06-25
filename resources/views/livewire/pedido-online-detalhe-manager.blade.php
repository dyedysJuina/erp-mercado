<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    @php $p = $this->pedido; @endphp
    <div class="main-content-pad">
        @if (!$p)
            <div style="text-align:center;padding:60px;color:var(--muted);">Pedido não encontrado.</div>
        @else
            <div class="header-row">
                <div>
                    <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                    <h1 class="page-title" style="display:flex;align-items:center;gap:10px;">
                        <a href="/pedidos-online" wire:navigate style="color:var(--muted);text-decoration:none;font-size:20px;"><i class="fas fa-arrow-left"></i></a>
                        Pedido {{ $p->codigo }}
                        <span style="font-size:14px;font-weight:500;color:var(--muted);">{{ $p->cliente->nome ?? '—' }}</span>
                    </h1>
                    <div class="breadcrumb">
                        <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        <a href="/pedidos-online" wire:navigate style="color:var(--text);text-decoration:none;">Pedidos Online</a>
                        <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        <span class="breadcrumb-active">{{ $p->codigo }}</span>
                    </div>
                </div>
                <div class="status-online"><span class="status-dot"></span> Conectado</div>
            </div>

            {{-- STATUS BAR --}}
            <div style="display:flex;gap:12px;align-items:center;padding:12px 16px;background:var(--surface);border-radius:12px;border:1px solid var(--border);margin-bottom:16px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:600;color:var(--muted);">Status:</span>
                    @php
                        $label = ['recebido'=>'Recebido','confirmado'=>'Confirmado','em_separacao'=>'Separando','pronto_retirada'=>'Pronto Retirada','pronto_entrega'=>'Pronto Entrega','entregue'=>'Entregue','cancelado'=>'Cancelado'][$p->status] ?? $p->status;
                        $sc = ['recebido'=>'badge-warning','confirmado'=>'badge-ativo','em_separacao'=>'badge-ativo','pronto_retirada'=>'badge-ativo','pronto_entrega'=>'badge-ativo','entregue'=>'badge-ativo','cancelado'=>'badge-inativo'][$p->status] ?? 'badge-inativo';
                    @endphp
                    <span class="badge-sm {{ $sc }}" style="font-size:11px;">{{ $label }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:600;color:var(--muted);">Valor:</span>
                    <span style="font-weight:700;font-size:16px;">R$ {{ number_format($p->total, 2, ',', '.') }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:600;color:var(--muted);">Data:</span>
                    <span>{{ $p->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:600;color:var(--muted);">Tipo:</span>
                    <span>{{ $p->tipo_entrega === 'retirada' ? 'Retirada' : 'Entrega' }}</span>
                </div>
                <div style="flex:1;"></div>
                @if ($p->cliente)
                    <a href="/clientes?busca={{ $p->cliente->id }}" wire:navigate style="font-size:11px;color:var(--primary-600);text-decoration:none;"><i class="fas fa-user"></i> {{ $p->cliente->nome }}</a>
                @endif
            </div>

            {{-- STATUS ACTIONS --}}
            <div style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;">
                @foreach (['recebido','confirmado','em_separacao','pronto_retirada','pronto_entrega','entregue'] as $s)
                    @if ($p->status !== $s)
                        <button wire:click="alterarStatus('{{ $s }}')" class="btn-sm btn-secondary" style="padding:6px 12px;font-size:11px;">{{ $s }}</button>
                    @else
                        <span class="btn-sm btn-primary" style="padding:6px 12px;font-size:11px;opacity:0.6;">{{ $s }} ✓</span>
                    @endif
                @endforeach
                @if ($p->status !== 'cancelado')
                    <button wire:click="alterarStatus('cancelado')" style="padding:6px 12px;font-size:11px;border:1px solid var(--danger);border-radius:6px;background:transparent;color:var(--danger);cursor:pointer;">Cancelar</button>
                @endif
            </div>

            {{-- ITENS --}}
            <div class="data-table-wrap">
                <div style="overflow-x:auto;">
                    <table class="data-table" style="font-size:12px;">
                        <thead>
                            <tr>
                                <th class="data-table-th text-left">Produto</th>
                                <th class="data-table-th text-right">Qtd</th>
                                <th class="data-table-th text-right">Preço</th>
                                <th class="data-table-th text-right">Total</th>
                                <th class="data-table-th text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($p->itens as $item)
                                <tr class="data-table-tr">
                                    <td class="data-table-td font-semibold" style="text-transform:uppercase;font-size:11px;">{{ $item->variacao?->nome_completo ?? '#' . $item->produto_variacao_id }}</td>
                                    <td class="data-table-td text-right">{{ number_format((float)$item->quantidade_solicitada, 3, ',', '.') }}</td>
                                    <td class="data-table-td text-right">R$ {{ number_format((float)$item->preco_unitario, 2, ',', '.') }}</td>
                                    <td class="data-table-td text-right font-bold">R$ {{ number_format((float)$item->total_item, 2, ',', '.') }}</td>
                                    <td class="data-table-td text-center">
                                        @php $st = ['pendente'=>'Aguardando','separado'=>'Separado','substituido'=>'Substituído','cancelado'=>'Cancelado'][$item->status_item] ?? $item->status_item; @endphp
                                        <span class="badge-sm badge-warning" style="font-size:9px;">{{ $st }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="data-table-empty">Nenhum item.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGAMENTOS --}}
            @if ($p->pagamentos->count() > 0)
                <div class="data-table-wrap" style="margin-top:12px;">
                    <div class="data-table-header"><span class="data-table-title"><i class="fas fa-credit-card"></i> Pagamentos</span></div>
                    <div style="padding:8px 12px;">
                        @foreach ($p->pagamentos as $pg)
                            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12px;border-bottom:1px solid var(--border);">
                                <span>{{ $pg->formaPagamento?->nome ?? '—' }}</span>
                                <span style="font-weight:700;">R$ {{ number_format((float)$pg->valor, 2, ',', '.') }}</span>
                                <span style="color:{{ $pg->status === 'pago' ? 'var(--success)' : 'var(--warning)' }};">{{ $pg->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
