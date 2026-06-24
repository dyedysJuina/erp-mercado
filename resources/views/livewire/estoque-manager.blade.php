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
            <div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Estoque & Inventário</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Estoque</span></div></div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <div class="field" style="margin:0;min-width:200px;"><select wire:model.live="loja_id" style="height:40px;font-size:13px;">@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
                <button wire:click="abrirModal" class="btn-primary btn-sm" style="height:40px;"><i class="fas fa-plus-circle"></i> Nova Movimentação</button>
            </div>
        </div>

        @if ($this->loja_id)
        <div class="metrics-grid">
            <div class="metric-card" style="padding:16px 18px;"><div><span class="metric-label">Custo Total</span><span class="metric-value" style="font-size:22px;">R$ {{ number_format($this->custoTotal, 2, ',', '.') }}</span><span class="metric-sub">Somatória de Custo × Qtde</span></div><div class="metric-icon green" style="width:44px;height:44px;font-size:18px;flex-shrink:0;"><i class="fas fa-wallet"></i></div></div>
            <div class="metric-card" style="padding:16px 18px;"><div><span class="metric-label">Total de Unidades</span><span class="metric-value" style="font-size:22px;">{{ number_format($this->unidadesFisicas, 0, ',', '.') }}</span><span class="metric-sub">Volumes físicos estocados</span></div><div class="metric-icon" style="width:44px;height:44px;border-radius:12px;font-size:18px;background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--text);flex-shrink:0;"><i class="fas fa-boxes-packing"></i></div></div>
            <div class="metric-card" style="padding:16px 18px;"><div><span class="metric-label">Estoque Crítico</span><span class="metric-value" style="font-size:22px;color:var(--warning);">{{ $this->itensCriticos }} itens</span><span class="metric-sub" style="color:var(--warning);font-weight:600;">Requer reposição</span></div><div class="metric-icon amber" style="width:44px;height:44px;font-size:18px;flex-shrink:0;"><i class="fas fa-exclamation-triangle"></i></div></div>
            <div class="metric-card" style="padding:16px 18px;"><div><span class="metric-label">Zerados em Loja</span><span class="metric-value" style="font-size:22px;color:var(--danger);">{{ $this->itensZerados }} itens</span><span class="metric-sub" style="color:var(--danger);font-weight:600;">Estoque zerado</span></div><div class="metric-icon" style="width:44px;height:44px;border-radius:12px;font-size:18px;background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);flex-shrink:0;"><i class="fas fa-ban"></i></div></div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="flex:1;min-width:250px;margin:0;"><label style="font-size:11px;">Buscar Item</label><input wire:model.live.debounce.300ms="busca" placeholder="Nome do produto, SKU ou código de barras..." style="height:40px;font-size:13px;"></div>
            <div class="field" style="width:200px;margin:0;"><label style="font-size:11px;">Status</label><select wire:model.live="filtroStatus" style="height:40px;font-size:13px;"><option value="todos">Todos</option><option value="normal">Normal</option><option value="baixo">Estoque Baixo</option><option value="zerado">Zerados</option></select></div>
        </div>

        <div class="data-table-wrap" style="margin-bottom:20px;">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-table-list"></i> Inventário Ativo</span><span style="font-size:10px;background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--muted);padding:3px 10px;border-radius:999px;font-weight:700;">{{ count($this->saldos) }} registro(s)</span></div>
            <div style="overflow-x:auto;">
                @php $saldosTabela = $this->saldosTable(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr><th class="data-table-th text-center" style="width:40px;"></th><th class="data-table-th text-left">Produto</th><th class="data-table-th text-left">EAN / Código</th><th class="data-table-th text-left">Marca / Depto</th><th class="data-table-th text-right">Est. Atual</th><th class="data-table-th text-right">Mínimo</th><th class="data-table-th text-right">Custo Unt.</th><th class="data-table-th text-right">Valor Total</th><th class="data-table-th text-center" style="width:90px;">Ações</th></tr></thead>
                    <tbody>
                        @forelse ($saldosTabela as $s)
                            @php
                                $qtd = (float)($s['quantidade_atual'] ?? 0);
                                $min = (float)($s['estoque_minimo'] ?? 0);
                                $custo = (float)($s['preco_custo'] ?? 0);
                                $status = $qtd <= 0 ? 'zerado' : ($qtd <= $min ? 'baixo' : 'ok');
                            @endphp
                            <tr class="data-table-tr">
                                <td class="data-table-td text-center"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;{{ $status === 'ok' ? 'background:var(--success);' : ($status === 'baixo' ? 'background:var(--warning);' : 'background:var(--danger);') }}"></span></td>
                                <td class="data-table-td"><div style="font-weight:600;color:var(--text);text-transform:uppercase;font-size:12px;">{{ $s['nome_completo'] ?? '—' }}@if ($s['sku'])<span style="color:var(--muted);font-weight:400;font-size:10px;font-family:monospace;margin-left:4px;">{{ $s['sku'] }}</span>@endif</div><span style="font-size:10px;color:var(--muted);">NCM: {{ $s['ncm_codigo'] }}</span></td>
                                <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ $s['codigo_barras'] ?? '—' }}</td>
                                <td class="data-table-td"><div style="font-weight:600;font-size:12px;color:var(--text);text-transform:uppercase;">{{ $s['marca_nome'] ?? '—' }}</div><span style="font-size:9px;background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--muted);padding:1px 6px;border-radius:4px;font-weight:700;text-transform:uppercase;">{{ $s['cat_nome'] ?? '—' }}</span></td>
                                <td class="data-table-td text-right font-bold font-mono" style="font-size:13px;{{ $status === 'zerado' ? 'color:var(--danger);' : ($status === 'baixo' ? 'color:var(--warning);' : 'color:var(--text);') }}">{{ number_format($qtd, 3, ',', '.') }} <span style="font-size:9px;color:var(--muted);font-weight:700;text-transform:uppercase;">{{ $s['unid_sigla'] ?? '' }}</span></td>
                                <td class="data-table-td text-right font-semibold text-muted font-mono">{{ number_format($min, 3, ',', '.') }}</td>
                                <td class="data-table-td text-right font-semibold font-mono">R$ {{ number_format($custo, 2, ',', '.') }}</td>
                                <td class="data-table-td text-right font-bold font-mono">R$ {{ number_format($qtd * $custo, 2, ',', '.') }}</td>
                                <td class="data-table-td text-center"><div class="table-actions"><button wire:click="abrirModal({{ $s['produto_variacao_id'] }})" class="table-action-btn" style="font-size:12px;" title="Movimentar"><i class="fas fa-right-left"></i></button><button wire:click="filtrarPorProduto('{{ addslashes($s['nome_completo']) }}')" class="table-action-btn" style="font-size:12px;" title="Histórico"><i class="fas fa-clock-rotate-left"></i></button></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="data-table-empty">@if (!$this->loja_id) Selecione uma loja. @else Nenhum produto encontrado. @endif</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($saldosTabela->hasPages())
                <div style="padding:12px 16px;border-top:1px solid var(--border);">
                    {{ $saldosTabela->links('livewire.pagination-custom') }}
                </div>
            @endif
        </div>

        <div class="sub-card">
            <div class="section-title" style="font-size:12px;margin-bottom:10px;"><i class="fas fa-history"></i> Movimentações Recentes</div>
            <div style="max-height:220px;overflow-y:auto;">
                @forelse ($this->movimentacoesRecentes as $log)
                    @php $ehEntrada = in_array($log['tipo'], ['entrada_compra', 'transferencia_entrada']); $ehAjuste = $log['tipo'] === 'ajuste'; @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-radius:8px;margin-bottom:4px;background:color-mix(in srgb,var(--text)2%,var(--surface));">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:10px;{{ $ehEntrada ? 'background:color-mix(in srgb,var(--success)12%,transparent);color:var(--success);' : ($ehAjuste ? 'background:color-mix(in srgb,var(--primary-500)12%,transparent);color:var(--primary-600);' : 'background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);') }}"><i class="fas {{ $ehEntrada ? 'fa-arrow-up' : ($ehAjuste ? 'fa-scale-balanced' : 'fa-arrow-down') }}"></i></span>
                            <div><span style="font-size:12px;font-weight:600;color:var(--text);">{{ $log['prod_nome'] ?? '—' }}</span><span style="font-size:10px;color:var(--muted);display:block;">{{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }} · {{ $log['justificativa'] ?? $log['tipo'] }}</span></div>
                        </div>
                        <div style="text-align:right;"><span style="font-weight:900;font-size:13px;{{ $ehEntrada ? 'color:var(--success);' : ($ehAjuste ? 'color:var(--primary-600);' : 'color:var(--danger);') }}">{{ $ehEntrada ? '+' : '' }}{{ number_format((float)$log['quantidade'], 3, ',', '.') }}</span><span style="font-size:10px;color:var(--muted);display:block;font-weight:600;">{{ $log['user_nome'] ?? '—' }}</span></div>
                    </div>
                @empty
                    <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhuma movimentação registrada.</div>
                @endforelse
            </div>
        </div>
        @else
        <div style="text-align:center;padding:80px 20px;color:var(--muted);"><i class="fa-solid fa-warehouse" style="font-size:48px;display:block;margin-bottom:16px;opacity:0.3;"></i>Selecione uma loja para consultar o estoque.</div>
        @endif
    </div>

    {{-- MODAL --}}
    <div x-data="{ open: @entangle('modalOpen') }"
         :style="'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);display:' + (open ? 'flex' : 'none') + ';align-items:center;justify-content:center;padding:20px;'">
        <div style="background:var(--surface);border-radius:20px;border:1px solid var(--border);max-width:520px;width:100%;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin:0;font-size:16px;color:var(--text);font-weight:800;display:flex;align-items:center;gap:8px;"><i class="fas fa-right-left" style="color:var(--primary-600);"></i> Movimentação</h3>
                <button type="button" wire:click="fecharModal" style="background:none;border:0;color:var(--muted);cursor:pointer;font-size:18px;">&times;</button>
            </div>
            <form wire:submit="registrarMovimentacao">
                <div style="margin-bottom:14px;">
                    <label style="font-size:11px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Produto</label>
                    @if ($this->movVariacaoId)
                        <div style="padding:10px 12px;background:color-mix(in srgb,var(--primary-500)6%,var(--surface));border-radius:8px;font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;justify-content:space-between;"><span>{{ $this->movVariacaoNome }}</span><button type="button" wire:click="$set('movVariacaoId', '')" style="background:none;border:0;color:var(--danger);cursor:pointer;">✕</button></div>
                    @else
                        <input wire:model.live="buscaVariacao" placeholder="Digite para buscar..." class="input-field">
                        @if (strlen(trim($this->buscaVariacao)) >= 2)
                            @php $resultados = \App\Models\ProdutoVariacao::where('nome_completo', 'like', '%' . $this->buscaVariacao . '%')->with('marca')->limit(8)->get(); @endphp
                            @if ($resultados->count() > 0)
                                <div style="max-height:200px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;margin-top:4px;">
                                    @foreach ($resultados as $r)
                                        <button type="button" wire:click="$set('movVariacaoId', {{ $r->id }}); $set('movVariacaoNome', '{{ addslashes($r->nome_completo) }}')" style="display:block;width:100%;text-align:left;padding:8px 12px;background:none;border:0;border-bottom:1px solid var(--border);cursor:pointer;font-size:12px;color:var(--text);"><b>{{ $r->nome_completo }}</b><span style="color:var(--muted);"> — {{ $r->marca?->nome ?? '' }}</span></button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    @endif
                    @error('movVariacaoId')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                    <div><label style="font-size:11px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Tipo</label><select wire:model="movTipo" style="height:44px;font-size:13px;">@foreach ($this->tiposMov as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach</select></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Quantidade</label><input wire:model="movQuantidade" type="number" step="0.001" min="0.001" style="height:44px;font-size:13px;">@error('movQuantidade')<div class="err">{{ $message }}</div>@enderror</div>
                </div>
                <div style="margin-bottom:14px;"><label style="font-size:11px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Custo de Ajuste</label><input wire:model="movCusto" type="number" step="0.01" placeholder="R$ 0,00" style="height:44px;font-size:13px;"></div>
                <div style="margin-bottom:20px;"><label style="font-size:11px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Justificativa</label><select wire:model="movJustificativa" style="height:44px;font-size:13px;"><option value="">— Selecione —</option><option value="Compra de Fornecedor">Compra de Fornecedor</option><option value="Ajuste de Inventário">Ajuste de Inventário</option><option value="Perda por Validade Vencida">Perda por Validade</option><option value="Produto Danificado">Produto Danificado</option><option value="Devolução de Cliente">Devolução de Cliente</option></select></div>
                <div style="display:flex;gap:8px;border-top:1px solid var(--border);padding-top:16px;">
                    <button type="button" wire:click="fecharModal" class="btn-secondary btn-lg" style="flex:1;">Cancelar</button>
                    <button type="submit" class="btn-primary btn-lg" style="flex:1;"><span wire:loading.remove>Salvar Movimentação</span><span wire:loading>Salvando...</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
