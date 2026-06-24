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
            <div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Compras</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">{{ $this->modo === 'create' ? 'Novo Pedido' : 'Pedido #' . $this->pedidoId }}</span></div></div>
            <div style="display:flex;gap:8px;"><button wire:click="novoPedido" class="btn-primary btn-sm" style="height:40px;"><i class="fas fa-plus"></i> Novo Pedido</button></div>
        </div>

        @if ($this->mostrarRecebimento)
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas fa-truck"></i> Receber Pedido #{{ $this->pedidoId }}</span></div>
                <div class="form-body">
                    <div class="grid-2" style="margin-bottom:12px;"><div class="field"><label>Nº Nota Fiscal</label><input wire:model="numero_nota" placeholder="Ex: 000123"></div><div class="field"><label>Chave NFe</label><input wire:model="chave_nfe" placeholder="44 dígitos"></div></div>
                    <table class="table-min"><thead><tr><th>Produto</th><th style="text-align:center;">Pedido</th><th style="text-align:center;">Receber</th><th style="text-align:right;">Custo</th><th>Lote</th><th>Validade</th></tr></thead>
                        <tbody>@foreach ($this->recebimento as $idx => $r)<tr><td>{{ $r['nome'] }}</td><td style="text-align:center;">{{ $r['pendente'] }}</td><td><input wire:model="recebimento.{{ $idx }}.receber" type="number" step="0.001" min="0" style="width:60px;padding:4px;border:1px solid var(--border);border-radius:6px;text-align:center;font-size:12px;"></td><td style="text-align:right;">R$ {{ number_format($r['custo'], 2, ',', '.') }}</td><td><input wire:model="recebimento.{{ $idx }}.lote" placeholder="Lote" style="width:90px;padding:4px;border:1px solid var(--border);border-radius:6px;font-size:11px;"></td><td><input wire:model="recebimento.{{ $idx }}.validade" type="date" style="width:110px;padding:4px;border:1px solid var(--border);border-radius:6px;font-size:11px;"></td></tr>@endforeach</tbody>
                    </table>
                    <div style="display:flex;gap:8px;margin-top:14px;"><button wire:click="confirmarRecebimento" class="btn-primary btn-lg">Confirmar Recebimento</button><button wire:click="$set('mostrarRecebimento', false)" class="btn-secondary btn-lg">Cancelar</button></div>
                </div>
            </div>
        @else
            {{-- LISTA DE PEDIDOS --}}
            <div class="form-card" style="margin-bottom:20px;" x-data="{ open: false }">
                <div @click="open = !open" style="display:flex;justify-content:space-between;align-items:center;padding:12px 18px;cursor:pointer;border-bottom:1px solid var(--border);">
                    <div style="display:flex;align-items:center;gap:8px;"><i class="fas fa-list"></i><span style="font-size:12px;font-weight:700;color:var(--text);">Todos os Pedidos ({{ $this->totalPedidos }})</span><span style="font-size:10px;color:var(--muted);" x-text="open ? '▲' : '▼'"></span></div>
                    <div style="display:flex;gap:8px;align-items:center;" @click.stop><input wire:model.live.debounce.300ms="buscaPedidos" placeholder="Buscar por # ou fornecedor..." style="width:200px;height:32px;padding:0 10px;border:1px solid var(--border);border-radius:6px;font-size:12px;outline:none;"><select wire:model.live="filtroStatus" style="height:32px;padding:0 8px;border:1px solid var(--border);border-radius:6px;font-size:11px;"><option value="">Todos</option><option value="rascunho">Rascunho</option><option value="enviado">Enviado</option><option value="parcialmente_recebido">Parcial</option><option value="recebido">Recebido</option><option value="cancelado">Cancelado</option></select></div>
                </div>
                <div x-show="open" x-collapse style="max-height:400px;overflow-y:auto;">
                    @forelse ($this->pedidos as $p)
                        <div wire:click="selecionarPedido({{ $p['id'] }})" style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border);cursor:pointer;{{ $this->pedidoId === $p['id'] ? 'background:color-mix(in srgb, #6366f1 6%, var(--surface));' : '' }}" onmouseover="this.style.background='color-mix(in srgb, var(--text) 3%, var(--surface))'" onmouseout="this.style.background='{{ $this->pedidoId === $p['id'] ? 'color-mix(in srgb, #6366f1 6%, var(--surface))' : 'transparent' }}'">
                            <div style="width:32px;height:32px;border-radius:50%;background:{{ $p['status'] === 'recebido' ? 'var(--success)' : ($p['status'] === 'cancelado' ? 'var(--danger)' : ($p['status'] === 'enviado' ? '#6366f1' : 'var(--muted)')) }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:11px;flex-shrink:0;">{{ $p['id'] }}</div>
                            <div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:13px;color:var(--text);">{{ $p['fornecedor']['razao_social'] ?? '—' }}</div><div style="font-size:11px;color:var(--muted);">{{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }} · R$ {{ number_format($p['total_pedido'], 2, ',', '.') }}</div></div>
                            <div>@php $sc = ['rascunho'=>'badge-inativo','enviado'=>'badge-ativo','parcialmente_recebido'=>'badge-warning','recebido'=>'badge-ativo','cancelado'=>'badge-inativo'][$p['status']] ?? 'badge-inativo'; @endphp<span class="badge-sm {{ $sc }}">{{ $p['status'] }}</span></div>
                        </div>
                    @empty
                        <div style="padding:30px;text-align:center;color:var(--muted);font-size:13px;">Nenhum pedido.</div>
                    @endforelse
                </div>
            </div>

            <div class="metrics-grid" style="margin-bottom:20px;">
                <div class="metric-card"><div class="metric-icon" style="width:40px;height:40px;font-size:16px;background:color-mix(in srgb,#8b5cf6 12%,transparent);color:#8b5cf6;"><i class="fas fa-shopping-cart"></i></div><div><span class="metric-label">Total Produtos</span><span class="metric-value" style="font-size:18px;">{{ count($this->itens) }}</span><span class="metric-sub">Itens no pedido</span></div></div>
                <div class="metric-card"><div class="metric-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-box"></i></div><div><span class="metric-label">Quantidade Total</span><span class="metric-value" style="font-size:18px;">{{ array_sum(array_column($this->itens, 'quantidade')) }}</span><span class="metric-sub">Unidades</span></div></div>
                <div class="metric-card"><div class="metric-icon amber" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-dollar-sign"></i></div><div><span class="metric-label">Valor do Pedido</span><span class="metric-value" style="font-size:18px;">R$ {{ number_format($this->totalPedido, 2, ',', '.') }}</span><span class="metric-sub">Total geral</span></div></div>
                <div class="metric-card"><div class="metric-icon blue" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-calendar-alt"></i></div><div><span class="metric-label">Previsão Entrega</span><span class="metric-value" style="font-size:16px;">{{ $this->previsao_entrega ? \Carbon\Carbon::parse($this->previsao_entrega)->format('d/m/Y') : '—' }}</span><span class="metric-sub">Prazo estimado</span></div></div>
                <div class="metric-card"><div class="metric-icon" style="width:40px;height:40px;font-size:16px;background:color-mix(in srgb,#ec4899 12%,transparent);color:#ec4899;"><i class="fas fa-wallet"></i></div><div><span class="metric-label">Condição</span><span class="metric-value" style="font-size:16px;">{{ $this->condicao_pagamento }}</span><span class="metric-sub">Pagamento</span></div></div>
            </div>

            {{-- GRID: Dados (2/3) + Info (1/3) --}}
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">
                <div class="form-card">
                    <div class="form-card-header"><span class="form-card-title" style="font-size:11px;"><i class="fas fa-file-contract"></i> Dados da Compra</span></div>
                    <div class="form-body">
                        <div class="grid-2" style="margin-bottom:12px;"><div class="field"><label>Fornecedor *</label><select wire:model="fornecedor_id"><option value="">Selecione...</option>@foreach (\App\Models\Fornecedor::orderBy('razao_social')->get(['id', 'razao_social']) as $f)<option value="{{ $f->id }}">{{ $f->razao_social }}</option>@endforeach</select>@error('fornecedor_id')<div class="err">{{ $message }}</div>@enderror</div><div class="field"><label>Loja *</label><select wire:model="loja_id"><option value="">Selecione...</option>@foreach (\App\Models\Loja::orderBy('nome')->get(['id', 'nome']) as $l)<option value="{{ $l->id }}">{{ $l->nome }}</option>@endforeach</select>@error('loja_id')<div class="err">{{ $message }}</div>@enderror</div></div>
                        <div class="grid-2" style="margin-bottom:12px;"><div class="field"><label>Condição *</label><select wire:model="condicao_pagamento"><option value="30 dias">30 dias</option><option value="À vista">À vista</option><option value="30/60 dias">30/60 dias</option><option value="45 dias">45 dias</option></select></div><div class="field"><label>Frete</label><select wire:model="tipo_frete"><option value="CIF">CIF — Fornecedor</option><option value="FOB">FOB — Destinatário</option></select></div></div>
                        <div class="grid-3" style="margin-bottom:12px;"><div class="field"><label>Previsão</label><input wire:model="previsao_entrega" type="date"></div><div class="field"><label>Frete (R$)</label><input wire:model="valor_frete" type="number" step="0.01" min="0"></div><div class="field"><label>Desconto (R$)</label><input wire:model="valor_desconto" type="number" step="0.01" min="0"></div></div>
                        <div class="field"><label>Observações</label><textarea wire:model="observacoes" rows="2" placeholder="Observações..." style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;color:var(--text);background:color-mix(in srgb, var(--surface) 97%, var(--text));outline:none;resize:none;"></textarea></div>
                    </div>
                </div>
                <div class="form-card">
                    <div class="form-card-header"><span class="form-card-title" style="font-size:11px;"><i class="fas fa-info-circle"></i> Informações</span></div>
                    <div class="form-body">
                        <div class="field" style="margin-bottom:12px;"><label>Data do Pedido</label><input wire:model="data_pedido" type="date"></div>
                        <div class="field" style="margin-bottom:12px;"><label>Nº do Pedido</label><input type="text" readonly placeholder="{{ $this->pedidoId ? '#' . $this->pedidoId : 'Será gerado' }}" style="width:100%;height:44px;padding:0 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;background:color-mix(in srgb, var(--text) 3%, var(--surface));color:var(--muted);"></div>
                        <div class="field"><label>Tipo</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                <button type="button" wire:click="$set('tipo_pedido', 'normal')" style="padding:10px;border-radius:10px;border:2px solid {{ $this->tipo_pedido === 'normal' ? '#6366f1' : 'var(--border)' }};background:{{ $this->tipo_pedido === 'normal' ? 'color-mix(in srgb, #6366f1 6%, transparent)' : 'transparent' }};cursor:pointer;font-size:12px;font-weight:700;color:{{ $this->tipo_pedido === 'normal' ? '#6366f1' : 'var(--text)' }};">Normal</button>
                                <button type="button" wire:click="$set('tipo_pedido', 'urgente')" style="padding:10px;border-radius:10px;border:2px solid {{ $this->tipo_pedido === 'urgente' ? '#ef4444' : 'var(--border)' }};background:{{ $this->tipo_pedido === 'urgente' ? 'color-mix(in srgb, #ef4444 6%, transparent)' : 'transparent' }};cursor:pointer;font-size:12px;font-weight:700;color:{{ $this->tipo_pedido === 'urgente' ? '#ef4444' : 'var(--text)' }};">Urgente</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ITENS + RESUMO --}}
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
                <div class="form-card">
                    <div class="form-card-header"><span class="form-card-title" style="font-size:11px;"><i class="fas fa-boxes"></i> Itens do Pedido</span></div>
                    <div class="form-body">
                        <div style="position:relative;margin-bottom:12px;" x-data="{ open: false }">
                            <input wire:model.live.debounce.300ms="buscaVariacao" placeholder="Buscar produto por código, nome ou código de barras..." @focus="open = true" @click.outside="open = false">
                            @if (count($this->resultadosVariacao) > 0)
                                <div x-show="open" style="position:absolute;z-index:10;background:var(--surface);border:1px solid var(--border);border-radius:8px;width:100%;max-height:240px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                    @foreach ($this->resultadosVariacao as $v)
                                        <button type="button" wire:click="adicionarItem({{ $v['id'] }})" @click="open = false" style="display:block;width:100%;text-align:left;padding:8px 12px;background:none;border:0;border-bottom:1px solid var(--border);cursor:pointer;font-size:13px;color:var(--text);"><b>{{ $v['nome_completo'] }}</b> <span style="color:var(--muted);">{{ $v['marca']['nome'] ?? '' }}</span></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('itens')<div class="err" style="margin-bottom:8px;">{{ $message }}</div>@enderror
                        <table class="table-min" style="font-size:12px;">
                            <thead><tr><th>Produto</th><th>Código</th><th>Marca</th><th style="text-align:center;">Unid.</th><th style="text-align:right;">Qtd</th><th style="text-align:right;">Custo</th><th style="text-align:right;">Total</th><th style="width:30px;"></th></tr></thead>
                            <tbody>
                                @forelse ($this->itens as $idx => $item)
                                    <tr><td style="font-weight:600;">{{ $item['nome'] }}</td><td style="font-family:monospace;font-size:11px;color:var(--muted);">{{ $item['sku'] ?? '—' }}</td><td style="font-size:11px;">{{ $item['marca'] }}</td><td style="text-align:center;font-weight:700;color:var(--muted);">{{ $item['unidade'] }}</td><td><input wire:model="itens.{{ $idx }}.quantidade" type="number" step="1" min="1" style="width:65px;padding:4px 8px;border:1px solid var(--border);border-radius:6px;font-size:13px;text-align:right;margin-left:auto;display:block;"></td><td><input wire:model.live="itens.{{ $idx }}.custo" type="number" step="0.01" min="0" style="width:80px;padding:4px 8px;border:1px solid var(--border);border-radius:6px;font-size:13px;text-align:right;margin-left:auto;display:block;"></td><td style="text-align:right;font-weight:700;">{{ number_format(((float)($item['quantidade'] ?? 0))*((float)($item['custo'] ?? 0)), 2, ',', '.') }}</td><td><button type="button" wire:click="removerItem({{ $idx }})" class="btn-remove" style="width:28px;height:28px;">&times;</button></td></tr>
                                @empty
                                    <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--muted);">Nenhum produto. Busque acima para adicionar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-card">
                    <div class="form-card-header"><span class="form-card-title" style="font-size:11px;">Resumo do Pedido</span></div>
                    <div class="form-body">
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;"><span style="color:var(--muted);">Subtotal</span><span style="font-weight:700;">R$ {{ number_format($this->totalPedido, 2, ',', '.') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;"><span style="color:var(--muted);">Frete</span><span style="font-weight:700;color:var(--muted);">R$ {{ number_format((float)$this->valor_frete, 2, ',', '.') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;color:#ef4444;"><span>Desconto</span><span style="font-weight:700;">- R$ {{ number_format((float)$this->valor_desconto, 2, ',', '.') }}</span></div>
                        <div style="border-top:2px solid var(--border);padding-top:8px;margin-top:8px;display:flex;justify-content:space-between;align-items:end;"><span style="font-weight:900;font-size:15px;color:var(--text);">Total</span><span style="font-weight:900;font-size:24px;color:#6366f1;">R$ {{ number_format(max(0, $this->totalPedido + (float)$this->valor_frete - (float)$this->valor_desconto), 2, ',', '.') }}</span></div>
                        <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--muted);"><span style="display:block;margin-bottom:4px;"><b>Condição:</b> {{ $this->condicao_pagamento }}</span><span style="display:block;margin-bottom:4px;"><b>Frete:</b> {{ $this->tipo_frete }}</span><span style="display:block;"><b>Previsão:</b> {{ $this->previsao_entrega ? \Carbon\Carbon::parse($this->previsao_entrega)->format('d/m/Y') : '—' }}</span></div>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div style="display:flex;flex-direction:column;padding-top:16px;margin-top:16px;border-top:1px solid var(--border);gap:12px;">
                @if ($this->modo === 'edit' && $this->pedidoId)
                    @php $pedidoStatus = $this->pedidoAtual['status'] ?? 'rascunho'; $steps = ['rascunho'=>0,'enviado'=>1,'parcialmente_recebido'=>2,'recebido'=>3]; $currentStep = $steps[$pedidoStatus] ?? 0; $stepLabels = ['Rascunho','Enviado','Recebendo','Concluído']; @endphp
                    <div style="display:flex;align-items:center;gap:0;background:color-mix(in srgb, var(--text) 2%, var(--surface));border-radius:10px;padding:8px 12px;">
                        @foreach ($stepLabels as $i => $label)
                            <div style="display:flex;align-items:center;flex:1;">
                                <div style="display:flex;align-items:center;gap:6px;"><span style="width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;{{ $i <= $currentStep ? 'background:var(--success);color:#fff;' : 'background:color-mix(in srgb, var(--text) 8%, var(--surface));color:var(--muted);' }}">{{ $i + 1 }}</span><span style="font-size:11px;font-weight:700;{{ $i <= $currentStep ? 'color:var(--text);' : 'color:var(--muted);' }}">{{ $label }}</span></div>
                                @if ($i < count($stepLabels) - 1)<div style="flex:1;height:2px;margin:0 8px;background:{{ $i < $currentStep ? 'var(--success)' : 'color-mix(in srgb, var(--text) 8%, var(--surface))' }};"></div>@endif
                            </div>
                        @endforeach
                    </div>
                @endif
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        @if ($this->modo === 'edit' && $this->pedidoId)
                            @php $s = $this->pedidoAtual['status'] ?? 'rascunho'; @endphp
                            @if ($s === 'rascunho')<button wire:click="enviarPedido({{ $this->pedidoId }})" class="btn-primary btn-lg" style="background:#6366f1;"><i class="fas fa-paper-plane"></i> Enviar Pedido</button><button wire:click="prepararRecebimento" style="background:var(--success);color:#fff;height:44px;padding:0 16px;border:0;border-radius:10px;font-weight:700;cursor:pointer;"><i class="fas fa-truck"></i> Receber</button>@endif
                            @if ($s === 'enviado')<button wire:click="prepararRecebimento" style="background:var(--success);color:#fff;height:44px;padding:0 16px;border:0;border-radius:10px;font-weight:700;cursor:pointer;"><i class="fas fa-truck"></i> Receber</button>@endif
                            <a href="{{ route('pedidos.pdf', $this->pedidoId) }}" target="_blank" style="background:var(--surface);color:var(--text);border:1px solid var(--border);padding:0 14px;height:44px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;cursor:pointer;"><i class="fas fa-file-pdf"></i> PDF</a>
                            @if (!in_array($s, ['recebido', 'cancelado']))<button wire:click="cancelarPedido({{ $this->pedidoId }})" wire:confirm="Cancelar?" class="btn-danger btn-sm">Cancelar</button>@endif
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;">@if ($this->modo === 'create' || ($this->pedidoAtual['status'] ?? 'rascunho') === 'rascunho')<button type="button" wire:click="novoPedido" class="btn-secondary btn-lg">Cancelar</button><button wire:click="salvar" class="btn-primary btn-lg"><span wire:loading.remove>{{ $this->modo === 'create' ? 'Salvar Pedido' : 'Atualizar' }}</span><span wire:loading>Salvando...</span></button>@endif</div>
                </div>
                @if ($this->modo === 'edit' && $this->pedidoId)<div style="font-size:12px;color:var(--muted);padding:8px 12px;background:color-mix(in srgb, #6366f1 6%, transparent);border-radius:8px;"><i class="fas fa-info-circle"></i> <strong>Fluxo:</strong> Rascunho → Enviar → Receber → Concluído. Pedidos enviados geram contas a pagar.</div>@endif
            </div>
        @endif
    </div>
</div>
