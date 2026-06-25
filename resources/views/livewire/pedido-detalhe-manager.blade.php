<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad" x-data="{ tab: 'itens' }">
        @php $p = $this->pedido; @endphp
        @if (!$p)
            <div style="text-align:center;padding:60px;color:var(--muted);"><i class="fas fa-exclamation-circle" style="font-size:32px;margin-bottom:12px;"></i><div>Pedido não encontrado.</div></div>
        @else
            {{-- HEADER --}}
            <div class="header-row">
                <div>
                    <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                    <h1 class="page-title" style="display:flex;align-items:center;gap:10px;">
                        <a href="/pedidos" wire:navigate style="color:var(--muted);text-decoration:none;font-size:20px;"><i class="fas fa-arrow-left"></i></a>
                        Pedido #{{ $p->id }}
                        <span style="font-size:14px;font-weight:500;color:var(--muted);">{{ $p->fornecedor->razao_social ?? '—' }}</span>
                    </h1>
                    <div class="breadcrumb">
                        <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        <a href="/pedidos" wire:navigate style="color:var(--text);text-decoration:none;">Pedidos</a>
                        <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        <span class="breadcrumb-active">Pedido #{{ $p->id }}</span>
                    </div>
                </div>
                <div class="status-online"><span class="status-dot"></span> Conectado</div>
            </div>

            {{-- STATUS BAR --}}
            <div style="display:flex;gap:12px;align-items:center;padding:12px 16px;background:var(--surface);border-radius:12px;border:1px solid var(--border);margin-bottom:16px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:600;color:var(--muted);">Status:</span>
                    @php
                        $label = ['rascunho'=>'Rascunho','enviado'=>'Enviado','parcialmente_recebido'=>'Parcial','recebido'=>'Recebido','cancelado'=>'Cancelado'][$p->status] ?? $p->status;
                        $sc = ['rascunho'=>'badge-inativo','enviado'=>'badge-ativo','parcialmente_recebido'=>'badge-warning','recebido'=>'badge-ativo','cancelado'=>'badge-inativo'][$p->status] ?? 'badge-inativo';
                    @endphp
                    <span class="badge-sm {{ $sc }}" style="font-size:11px;">{{ $label }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:600;color:var(--muted);">Valor:</span>
                    <span style="font-weight:700;font-size:16px;">R$ {{ number_format($p->total_pedido, 2, ',', '.') }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;font-weight:600;color:var(--muted);">Data:</span>
                    <span>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</span>
                </div>
                <div style="flex:1;"></div>
                @if ($p->status === 'rascunho')
                    <button wire:click="enviar" class="btn-sm btn-primary" style="padding:8px 16px;font-size:12px;"><i class="fas fa-paper-plane"></i> Marcar Enviado</button>
                @endif
                @if (in_array($p->status, ['enviado', 'parcialmente_recebido', 'recebido']))
                    <a href="{{ route('pedidos.pdf', $p->id) }}" class="btn-sm btn-secondary" style="padding:8px 16px;font-size:12px;text-decoration:none;">
                        <i class="fas fa-file-pdf"></i> Baixar PDF
                    </a>
                @endif
            </div>

            {{-- PROGRESSO --}}
            @php
                $totalP = (float)\App\Models\CompraPedidoItem::where('compra_pedido_id', $p->id)->sum('quantidade_pedida');
                $totalR = (float)\App\Models\CompraPedidoItem::where('compra_pedido_id', $p->id)->sum('quantidade_recebida');
                $pct = $totalP > 0 ? round(($totalR / $totalP) * 100) : 0;
            @endphp
            <div style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:600;color:var(--muted);margin-bottom:4px;">
                    <span>Progresso de Recebimento</span>
                    <span>{{ $pct }}% ({{ number_format($totalR, 0, ',', '.') }} / {{ number_format($totalP, 0, ',', '.') }} unidades)</span>
                </div>
                <div style="height:8px;border-radius:4px;background:color-mix(in srgb,var(--text)8%,var(--surface));overflow:hidden;">
                    <div style="height:100%;border-radius:4px;width:{{ $pct }}%;background:{{ $pct >= 100 ? 'var(--success)' : 'var(--warning)' }};transition:width 0.3s;"></div>
                </div>
            </div>

            {{-- AÇÕES RÁPIDAS --}}
            @if (in_array($p->status, ['enviado', 'parcialmente_recebido']))
                <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                    <button wire:click="receberTodos" class="btn-sm btn-primary" style="padding:8px 16px;font-size:12px;">
                        <i class="fas fa-check-double"></i> Receber Todos Conforme Pedido
                    </button>
                </div>
            @endif

            {{-- ABAS --}}
            <div style="display:flex;gap:4px;margin-bottom:12px;border-bottom:1px solid var(--border);">
                <button @click="tab = 'itens'" :class="tab === 'itens' ? 'tab-btn tab-btn-active' : 'tab-btn'" style="padding:8px 16px;font-size:12px;">
                    <i class="fas fa-list"></i> Itens ({{ $this->totalItens }})
                </button>
                <button @click="tab = 'recebimentos'" :class="tab === 'recebimentos' ? 'tab-btn tab-btn-active' : 'tab-btn'" style="padding:8px 16px;font-size:12px;">
                    <i class="fas fa-history"></i> Histórico
                </button>
            </div>

            {{-- TAB ITENS --}}
            <div x-show="tab === 'itens'">
                @if (in_array($p->status, ['enviado', 'parcialmente_recebido']))
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <div style="font-size:11px;color:var(--muted);display:flex;gap:12px;">
                            <span>☑ <strong style="color:var(--text);">{{ $this->itensConferidos }}</strong> conferidos</span>
                            <span>☐ <strong style="color:var(--warning);">{{ $this->itensPendentes }}</strong> pendentes</span>
                            <span>Total: <strong>{{ $this->totalItens }}</strong> itens</span>
                        </div>
                    </div>
                @endif

                <div class="data-table-wrap">
                    <div style="overflow-x:auto;">
                        <table class="data-table" style="font-size:12px;">
                            <thead>
                                <tr>
                                    @if (in_array($p->status, ['enviado', 'parcialmente_recebido']))
                                        <th class="data-table-th text-center" style="width:32px;">Conf.</th>
                                    @endif
                                    <th class="data-table-th text-left">Produto</th>
                                    <th class="data-table-th text-right">Pedido</th>
                                    <th class="data-table-th text-right">Recebido</th>
                                    <th class="data-table-th text-right">Falta</th>
                                    @if (in_array($p->status, ['enviado', 'parcialmente_recebido']))
                                        <th class="data-table-th text-right" style="width:100px;">Receber</th>
                                        <th class="data-table-th text-right" style="width:80px;">Avaria</th>
                                        <th class="data-table-th text-center" style="width:70px;">Ñ Veio</th>
                                        <th class="data-table-th text-center" style="width:90px;">Lote</th>
                                        <th class="data-table-th text-center" style="width:110px;">Validade</th>
                                    @endif
                                    <th class="data-table-th text-right">Custo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->itens as $idx => $i)
                                    @php $pendente = max(0, $i['quantidade_pedida'] - $i['quantidade_recebida']); @endphp
                                    <tr class="data-table-tr" style="{{ !$i['conferido'] ? 'opacity:0.4;' : ($i['avaria'] > 0 ? 'background:color-mix(in srgb,var(--danger)4%,transparent);' : ($i['nao_veio'] ? 'background:color-mix(in srgb,var(--warning)4%,transparent);' : '')) }}">
                                        @if (in_array($p->status, ['enviado', 'parcialmente_recebido']))
                                            <td class="data-table-td text-center">
                                                <input type="checkbox" wire:model.live="itens.{{ $idx }}.conferido" style="cursor:pointer;">
                                            </td>
                                        @endif
                                        <td class="data-table-td font-semibold" style="text-transform:uppercase;font-size:11px;">{{ $i['nome'] }}</td>
                                        <td class="data-table-td text-right">{{ number_format($i['quantidade_pedida'], 3, ',', '.') }}</td>
                                        <td class="data-table-td text-right" style="color:var(--success);">{{ number_format($i['quantidade_recebida'], 3, ',', '.') }}</td>
                                        <td class="data-table-td text-right" style="color:{{ $pendente > 0 ? 'var(--danger)' : 'var(--success)' }};font-weight:700;">{{ number_format($pendente, 3, ',', '.') }}</td>
                                        @if (in_array($p->status, ['enviado', 'parcialmente_recebido']))
                                            <td class="data-table-td text-right">
                                                <div style="display:flex;align-items:center;gap:2px;">
                                                    <button wire:click="decrementar('receber',{{ $idx }})" style="width:22px;height:22px;border-radius:4px;border:1px solid var(--border);background:#fff;cursor:pointer;font-size:12px;line-height:1;">−</button>
                                                    <input type="text" inputmode="decimal" wire:model="itens.{{ $idx }}.receber"
                                                        style="width:46px;text-align:center;padding:2px 2px;border:1px solid var(--border);border-radius:4px;font-size:11px;{{ $i['conferido'] ? '' : 'opacity:0.4;' }}">
                                                    <button wire:click="incrementar('receber',{{ $idx }})" style="width:22px;height:22px;border-radius:4px;border:1px solid var(--border);background:#fff;cursor:pointer;font-size:12px;line-height:1;">+</button>
                                                </div>
                                            </td>
                                            <td class="data-table-td text-right">
                                                <div style="display:flex;align-items:center;gap:2px;">
                                                    <button wire:click="decrementar('avaria',{{ $idx }})" style="width:22px;height:22px;border-radius:4px;border:1px solid var(--danger);background:#fff;cursor:pointer;font-size:12px;line-height:1;color:var(--danger);">−</button>
                                                    <input type="text" inputmode="decimal" wire:model="itens.{{ $idx }}.avaria"
                                                        style="width:36px;text-align:center;padding:2px 2px;border:1px solid var(--danger);border-radius:4px;font-size:11px;color:var(--danger);{{ $i['conferido'] ? '' : 'opacity:0.4;' }}">
                                                    <button wire:click="incrementar('avaria',{{ $idx }})" style="width:22px;height:22px;border-radius:4px;border:1px solid var(--danger);background:#fff;cursor:pointer;font-size:12px;line-height:1;color:var(--danger);">+</button>
                                                </div>
                                            </td>
                                            <td class="data-table-td text-center">
                                                <button wire:click="naoVeioToggle({{ $idx }})" style="width:28px;height:28px;border-radius:6px;border:1px solid {{ $i['nao_veio'] ? 'var(--warning)' : 'var(--border)' }};background:{{ $i['nao_veio'] ? 'color-mix(in srgb,var(--warning)12%,transparent)' : '#fff' }};cursor:pointer;font-size:14px;">
                                                    {{ $i['nao_veio'] ? '🚫' : '✓' }}
                                                </button>
                                            </td>
                                            <td class="data-table-td text-center">
                                                <input type="text" wire:model="itens.{{ $idx }}.lote" placeholder="—"
                                                    style="width:80px;padding:2px 4px;border:1px solid var(--border);border-radius:4px;font-size:10px;{{ $i['conferido'] ? '' : 'opacity:0.4;' }}">
                                            </td>
                                            <td class="data-table-td text-center">
                                                <input type="date" wire:model="itens.{{ $idx }}.validade"
                                                    style="width:100px;padding:2px 4px;border:1px solid var(--border);border-radius:4px;font-size:10px;{{ $i['conferido'] ? '' : 'opacity:0.4;' }}">
                                            </td>
                                        @endif
                                        <td class="data-table-td text-right text-muted">R$ {{ number_format($i['custo'], 2, ',', '.') }}</td>
                                    </tr>
                                    @if ($i['nao_veio'] && in_array($p->status, ['enviado', 'parcialmente_recebido']))
                                        <tr style="background:color-mix(in srgb,var(--warning)3%,transparent);">
                                            <td colspan="2"></td>
                                            <td colspan="8" style="padding:2px 8px 6px;">
                                                <input type="text" wire:model="itens.{{ $idx }}.motivo" placeholder="Motivo do item não veio..." style="width:100%;padding:4px 8px;border:1px solid var(--warning);border-radius:4px;font-size:11px;">
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr><td colspan="12" class="data-table-empty">Nenhum item encontrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if (in_array($p->status, ['enviado', 'parcialmente_recebido']))
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:14px;border-top:1px solid var(--border);">
                        <div style="font-size:11px;color:var(--muted);display:flex;gap:16px;">
                            <span>☑ Conferido · 🚫 Não veio · <span style="color:var(--danger);">Avaria</span></span>
                        </div>
                        <button wire:click="confirmarRecebimento" class="btn btn-success" style="padding:10px 24px;font-size:13px;font-weight:800;border:0;border-radius:8px;color:#fff;cursor:pointer;">
                            <i class="fas fa-check-circle"></i> Confirmar Recebimento
                        </button>
                    </div>
                @endif
            </div>

            {{-- TAB HISTÓRICO --}}
            <div x-show="tab === 'recebimentos'" x-cloak>
                <div class="data-table-wrap">
                    @php $recebimentos = \App\Models\CompraRecebimento::with('itens.variacao')->where('compra_pedido_id', $p->id)->orderBy('created_at', 'desc')->get(); @endphp
                    @forelse ($recebimentos as $rec)
                        <div style="padding:12px 16px;border-bottom:1px solid var(--border);">
                            <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;margin-bottom:6px;">
                                <span>Recebimento #{{ $rec->id }} — {{ $rec->created_at->format('d/m/Y H:i') }}</span>
                                <span style="color:var(--muted);font-weight:400;">NF: {{ $rec->numero_nota ?? '—' }}</span>
                            </div>
                            <table style="width:100%;border-collapse:collapse;font-size:11px;">
                                <thead>
                                    <tr style="border-bottom:1px solid var(--border);">
                                        <th style="text-align:left;padding:4px 6px;color:var(--muted);">Produto</th>
                                        <th style="text-align:right;padding:4px 6px;color:var(--muted);">Qtd</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rec->itens as $ri)
                                        <tr style="border-bottom:1px solid color-mix(in srgb,var(--text)4%,transparent);">
                                            <td style="padding:4px 6px;">{{ $ri->variacao?->nome_completo ?? '#' . $ri->produto_variacao_id }}</td>
                                            <td style="padding:4px 6px;text-align:right;">{{ number_format($ri->quantidade_recebida, 3, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px;">
                            <i class="fas fa-box-open" style="font-size:28px;margin-bottom:8px;opacity:0.3;display:block;"></i>
                            Nenhum recebimento registrado ainda.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    {{-- ENVIAR MODAL --}}
    <div x-data="{ open: $wire.entangle('enviarModalOpen') }" x-show="open" x-cloak style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;padding:20px;">
        <div style="background:var(--surface);border-radius:16px;border:1px solid var(--border);width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.2);padding:24px;text-align:center;">
            <div style="font-size:48px;margin-bottom:12px;">📨</div>
            <h3 style="margin:0 0 6px;font-size:17px;font-weight:800;color:var(--text);">Enviar Pedido #{{ $p->id }}</h3>
            <p style="font-size:13px;color:var(--muted);margin-bottom:20px;">O pedido será marcado como enviado. Deseja baixar o PDF para enviar ao fornecedor?</p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" wire:click="$set('enviarModalOpen', false)" style="flex:1;padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;font-weight:700;color:var(--text);font-size:13px;min-width:120px;">Cancelar</button>
                <a href="{{ route('pedidos.pdf', $p->id) }}"
                   onclick="event.preventDefault(); window.location.href=this.href; setTimeout(() => window.Livewire.find('{{ $this->getId() }}').call('confirmarEnvio'), 500);"
                   style="flex:1;padding:10px;border:0;border-radius:8px;background:var(--primary-600);color:#fff;text-decoration:none;font-weight:800;font-size:13px;cursor:pointer;min-width:120px;display:inline-flex;align-items:center;justify-content:center;gap:6px;">
                    <i class="fas fa-file-pdf"></i> Baixar PDF e Enviar
                </a>
                <button wire:click="confirmarEnvio" style="flex:1;padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;font-weight:700;color:var(--text);font-size:13px;min-width:120px;">
                    Apenas Marcar Enviado
                </button>
            </div>
        </div>
    </div>
</div>
