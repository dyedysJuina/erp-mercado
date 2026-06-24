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
                <h1 class="page-title">NF-e de Entrada</h1>
                <div class="breadcrumb">
                    <span>Inicio</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Compras</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">NF-e Entrada</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        <div class="tab-nav" style="margin-bottom:16px;">
            <button wire:click="$set('aba', 'registrar')" class="{{ $aba === 'registrar' ? 'tab-active' : '' }}"><i class="fas fa-plus"></i> Registrar Entrada</button>
            <button wire:click="$set('aba', 'listagem')" class="{{ $aba === 'listagem' ? 'tab-active' : '' }}"><i class="fas fa-list"></i> Historico</button>
        </div>

        @if ($aba === 'registrar')
            <div class="grid-2col-custom" style="align-items:start;">
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div class="card" style="padding:20px;">
                        <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;color:var(--text);">Dados da Nota</h3>

                        <div class="grid-2" style="gap:10px;margin-bottom:10px;">
                            <div class="field" style="grid-column:span 2;"><label>Chave de Acesso NF-e</label><input wire:model="chave_nfe" placeholder="44 digitos..." maxlength="44" style="font-family:monospace;"></div>
                            <div class="field"><label>Numero da Nota</label><input wire:model="numero_nota" placeholder="Opcional"></div>
                            <div class="field"><label>Loja *</label><select wire:model="loja_id"><option value="">Selecione...</option>@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select>@error('loja_id')<div class="err">{{ $message }}</div>@enderror</div>
                            <div class="field"><label>Data Emissao</label><input wire:model="data_emissao" type="date"></div>
                            <div class="field"><label>Data Recebimento</label><input wire:model="data_recebimento" type="date"></div>
                        </div>

                        <h4 style="margin:12px 0 8px;font-size:13px;font-weight:700;color:var(--text);">Fornecedor *</h4>
                        @if ($this->fornecedor_id)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:8px;background:color-mix(in srgb,var(--primary-500)6%,transparent);margin-bottom:10px;">
                                <strong>{{ $this->fornecedor_nome }}</strong>
                                <button type="button" wire:click="limparFornecedor" style="background:none;border:0;color:var(--danger);cursor:pointer;font-size:18px;">&times;</button>
                            </div>
                        @else
                            <div style="position:relative;margin-bottom:10px;">
                                <input wire:model.live.debounce.300ms="fornecedor_busca" wire:input="buscarFornecedor" placeholder="Digite CNPJ ou razao social..." style="width:100%;height:44px;padding:0 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;">
                            </div>
                        @endif
                        @error('fornecedor_id')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="card" style="padding:20px;">
                        <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:var(--text);">Produtos</h4>

                        <div style="position:relative;margin-bottom:12px;">
                            <input wire:model.live.debounce.300ms="buscaProduto" wire:input="buscarProduto" placeholder="Buscar produto por nome ou SKU..." style="width:100%;height:44px;padding:0 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;">
                            @if (count($this->resultadosBusca) > 0)
                                <div style="position:absolute;z-index:10;left:0;right:0;top:46px;max-height:260px;overflow:auto;border:1px solid var(--border);border-radius:8px;background:var(--surface);box-shadow:0 8px 24px rgba(0,0,0,0.12);">
                                    @foreach ($this->resultadosBusca as $pr)
                                        <button type="button" wire:click="adicionarItem({{ $pr['id'] }})" style="display:flex;align-items:center;gap:8px;width:100%;padding:8px 12px;border:0;border-bottom:1px solid var(--border);background:none;cursor:pointer;text-align:left;font-size:12px;color:var(--text);">
                                            <strong style="flex:1;text-transform:uppercase;">{{ $pr['nome_completo'] }}</strong>
                                            <span style="color:var(--muted);font-size:10px;">{{ $pr['sku'] ?? '' }}</span>
                                            <span style="color:var(--muted);font-size:10px;">{{ $pr['marca']['nome'] ?? '' }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if (count($this->itens) > 0)
                            <div style="max-height:400px;overflow-y:auto;">
                                <table class="data-table" style="font-size:12px;">
                                    <thead><tr style="position:sticky;top:0;background:var(--surface);"><th class="data-table-th text-left">Produto</th><th class="data-table-th text-right">Qtd</th><th class="data-table-th text-right">Custo Un.</th><th class="data-table-th text-right">Total</th><th class="data-table-th text-center" style="width:30px;"></th></tr></thead>
                                    <tbody>
                                        @foreach ($this->itens as $idx => $item)
                                            <tr class="data-table-tr">
                                                <td class="data-table-td font-semibold" style="text-transform:uppercase;font-size:11px;">{{ $item['nome'] }}</td>
                                                <td class="data-table-td"><input wire:model.blur="itens.{{ $idx }}.quantidade" type="text" inputmode="decimal" style="width:60px;padding:3px 4px;border:1px solid var(--border);border-radius:4px;font-size:11px;text-align:right;"></td>
                                                <td class="data-table-td"><input wire:model.blur="itens.{{ $idx }}.custo" type="text" inputmode="decimal" placeholder="0,00" style="width:70px;padding:3px 4px;border:1px solid var(--border);border-radius:4px;font-size:11px;text-align:right;"></td>
                                                <td class="data-table-td text-right font-bold">R$ {{ number_format($item['total'], 2, ',', '.') }}</td>
                                                <td class="data-table-td text-center"><button type="button" wire:click="removerItem({{ $idx }})" style="background:none;border:0;color:var(--danger);cursor:pointer;padding:4px;"><i class="fas fa-times"></i></button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:12px 0 0;border-top:1px solid var(--border);margin-top:12px;">
                                <strong>Total da Nota:</strong>
                                <strong style="font-size:18px;color:var(--success);">R$ {{ number_format(collect($this->itens)->sum('total'), 2, ',', '.') }}</strong>
                            </div>
                        @else
                            <div style="text-align:center;padding:24px;color:var(--muted);font-size:12px;"><i class="fas fa-box-open" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.3;"></i>Nenhum produto. Busque acima para adicionar.</div>
                        @endif

                        @error('itens')<div class="err">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div class="sidebar-card">
                        <div class="sidebar-card-header"><i class="fas fa-info-circle"></i> Como funciona</div>
                        <div style="padding:12px 16px;font-size:12px;color:var(--text);line-height:1.6;">
                            <ol style="margin:0;padding-left:16px;">
                                <li>Informe a chave de acesso (opcional)</li>
                                <li>Selecione a loja e o fornecedor</li>
                                <li>Adicione os produtos recebidos</li>
                                <li>Informe quantidade e custo unitario</li>
                                <li>Confirme para dar entrada no estoque</li>
                            </ol>
                            <p style="margin:8px 0 0;font-weight:700;">O sistema automaticamente:</p>
                            <ul style="margin:4px 0 0;padding-left:16px;">
                                <li>Atualiza o estoque</li>
                                <li>Registra o custo na tabela de precos</li>
                                <li>Cria conta a pagar no financeiro</li>
                            </ul>
                        </div>
                    </div>

                    <button wire:click="registrar" class="btn btn-primary btn-lg" style="width:100%;">
                        <span wire:loading.remove><i class="fas fa-check-circle"></i> Confirmar Entrada no Estoque</span>
                        <span wire:loading>Registrando...</span>
                    </button>
                </div>
            </div>
        @else
            {{-- LISTAGEM --}}
            <div class="data-table-wrap">
                <div style="overflow-x:auto;">
                    @php $listagem = $this->listagem(); @endphp
                    <table class="data-table" style="font-size:12px;">
                        <thead><tr><th class="data-table-th text-left">#</th><th class="data-table-th text-left">Fornecedor</th><th class="data-table-th text-left">Loja</th><th class="data-table-th text-left">Chave/Nota</th><th class="data-table-th text-right">Valor</th><th class="data-table-th text-left">Data</th><th class="data-table-th text-center">Status</th></tr></thead>
                        <tbody>
                            @forelse ($listagem as $d)
                                <tr class="data-table-tr">
                                    <td class="data-table-td font-bold">#{{ $d->id }}</td>
                                    <td class="data-table-td font-semibold">{{ $d->fornecedor['razao_social'] ?? '-' }}</td>
                                    <td class="data-table-td text-muted">{{ $d->loja['nome'] ?? '-' }}</td>
                                    <td class="data-table-td font-mono text-muted" style="font-size:10px;">{{ Str::limit($d->chave_acesso ?? $d->numero, 20) }}</td>
                                    <td class="data-table-td text-right font-bold">R$ {{ number_format($d->valor_total, 2, ',', '.') }}</td>
                                    <td class="data-table-td text-muted" style="font-size:11px;">{{ $d->emitida_at ? $d->emitida_at->format('d/m/Y') : '-' }}</td>
                                    <td class="data-table-td text-center"><span class="badge-sm badge-ativo">{{ $d->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="data-table-empty">Nenhuma NF-e de entrada registrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($listagem->hasPages())
                    <div style="padding:12px 16px;border-top:1px solid var(--border);">{{ $listagem->links('livewire.pagination-custom') }}</div>
                @endif
            </div>
        @endif
    </div>
</div>
