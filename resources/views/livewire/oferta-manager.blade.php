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
                <h1 class="page-title">Campanhas & Ofertas</h1>
                <div class="breadcrumb">
                    <span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Ofertas</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        <div class="metrics-grid" style="grid-template-columns:repeat(4,1fr);">
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Total</span><span class="metric-value" style="font-size:20px;">{{ $this->totais['total'] }}</span><span class="metric-sub">Campanhas criadas</span></div><div class="metric-icon blue" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-tags"></i></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Ativas</span><span class="metric-value" style="font-size:20px;color:var(--success);">{{ $this->totais['ativas'] }}</span><span class="metric-sub">Em andamento agora</span></div><div class="metric-icon green" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-play"></i></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Agendadas</span><span class="metric-value" style="font-size:20px;color:var(--warning);">{{ $this->totais['agendadas'] }}</span><span class="metric-sub">Vão começar</span></div><div class="metric-icon amber" style="width:32px;height:32px;font-size:12px;"><i class="fas fa-clock"></i></div></div>
            <div class="metric-card" style="padding:14px;"><div><span class="metric-label">Expiradas</span><span class="metric-value" style="font-size:20px;color:var(--muted);">{{ $this->totais['expiradas'] }}</span><span class="metric-sub">Já encerradas</span></div><div class="metric-icon" style="width:32px;height:32px;border-radius:8px;font-size:12px;background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--muted);"><i class="fas fa-stop"></i></div></div>
        </div>

        @if ($this->aba === 'campanhas')
            {{-- LISTA DE CAMPANHAS --}}
            <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                <div class="field" style="flex:1;min-width:200px;margin:0;"><label>Buscar</label><input wire:model.live.debounce.300ms="busca" placeholder="Nome da campanha..." style="height:38px;font-size:13px;"></div>
                <div class="field" style="width:140px;margin:0;"><label>Status</label><select wire:model.live="filtroStatus" style="height:38px;font-size:12px;"><option value="">Todas</option><option value="ativas">Ativas</option><option value="agendadas">Agendadas</option><option value="expiradas">Expiradas</option></select></div>
                <button wire:click="novo" class="btn btn-primary" style="height:38px;"><i class="fas fa-plus"></i> Nova Campanha</button>
            </div>

            <div class="data-table-wrap">
                <div style="overflow-x:auto;">
                    @php $campanhas = $this->campanhas(); @endphp
                    <table class="data-table" style="font-size:12px;">
                        <thead><tr><th class="data-table-th text-left">Campanha</th><th class="data-table-th text-left">Início</th><th class="data-table-th text-left">Fim</th><th class="data-table-th text-center">Produtos</th><th class="data-table-th text-center">Status</th><th class="data-table-th text-center" style="width:100px;">Ações</th></tr></thead>
                        <tbody>
                            @forelse ($campanhas as $c)
                                @php $st = $this->statusCampanha($c); @endphp
                                <tr class="data-table-tr">
                                    <td class="data-table-td font-bold">{{ $c->nome }}@if ($c->descricao)<span style="display:block;font-size:10px;color:var(--muted);font-weight:400;">{{ $c->descricao }}</span>@endif</td>
                                    <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ $c->data_inicio->format('d/m/Y H:i') }}</td>
                                    <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ $c->data_fim->format('d/m/Y H:i') }}</td>
                                    <td class="data-table-td text-center font-bold">{{ $c->produtos_count }}</td>
                                    <td class="data-table-td text-center"><span class="badge-sm {{ $st['class'] }}">{{ $st['label'] }}</span></td>
                                    <td class="data-table-td text-center">
                                        <div class="table-actions">
                                            <button wire:click="selecionar({{ $c->id }})" class="table-action-btn" title="Editar"><i class="fas fa-edit"></i></button>
                                            <button wire:click="excluir({{ $c->id }})" wire:confirm="Excluir campanha '{{ $c->nome }}'?" class="table-action-btn" style="color:var(--danger);" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="data-table-empty">Nenhuma campanha encontrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($campanhas->hasPages())
                    <div style="padding:12px 16px;border-top:1px solid var(--border);">{{ $campanhas->links('livewire.pagination-custom') }}</div>
                @endif
            </div>
        @else
            {{-- FORM / PRODUTOS DA CAMPANHA --}}
            <div class="grid-2col-custom" style="align-items:start;">
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div class="card" style="padding:20px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <h3 style="margin:0;font-size:16px;color:var(--text);font-weight:800;"><i class="fas fa-bullhorn" style="color:var(--primary-600);"></i> {{ $this->modo === 'create' ? 'Nova' : 'Editar' }} Campanha</h3>
                            <button wire:click="novo" class="btn-sm btn-secondary">Cancelar</button>
                        </div>
                        <form wire:submit="salvar">
                            <div class="grid-2" style="gap:10px;margin-bottom:10px;">
                                <div class="field" style="grid-column:span 2;"><label>Nome da Campanha *</label><input wire:model="nome" placeholder="Ex: Promoção de Inverno">@error('nome')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field" style="grid-column:span 2;"><label>Descrição</label><textarea wire:model="descricao" rows="2" placeholder="Descrição opcional..." style="height:auto;min-height:60px;"></textarea></div>
                                <div class="field"><label>Data/Hora Início *</label><input wire:model="data_inicio" type="datetime-local">@error('data_inicio')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field"><label>Data/Hora Fim *</label><input wire:model="data_fim" type="datetime-local">@error('data_fim')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field" style="grid-column:span 2;">
                                    <label>Lojas *</label>
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                                        @foreach ($this->lojas as $l)
                                            <label style="display:flex;align-items:center;gap:4px;padding:6px 10px;border:1px solid var(--border);border-radius:6px;cursor:pointer;font-size:13px;{{ in_array((string)$l['id'], $this->lojasSelecionadas) ? 'background:color-mix(in srgb, var(--primary-500) 8%, transparent);border-color:var(--primary-500);' : '' }}">
                                                <input type="checkbox" wire:model="lojasSelecionadas" value="{{ $l['id'] }}" style="width:16px;height:16px;accent-color:var(--primary-500);"> {{ $l['nome'] }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('lojasSelecionadas')<div class="err">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;margin-bottom:16px;"><input wire:model="ativo" type="checkbox" style="width:16px;height:16px;"> Campanha ativa</label>
                            <button type="submit" class="btn btn-primary" style="width:100%;"><span wire:loading.remove>Salvar Campanha</span><span wire:loading>Salvando...</span></button>
                        </form>
                    </div>
                </div>

                {{-- PRODUTOS DA CAMPANHA --}}
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div class="card" style="padding:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <h4 style="margin:0;font-size:13px;font-weight:700;color:var(--text);"><i class="fas fa-box"></i> Produtos na Campanha ({{ count($this->produtosCampanha) }})</h4>
                        </div>

                        {{-- Search bar --}}
                        <div style="position:relative;margin-bottom:12px;">
                            <input wire:model.live.debounce.300ms="buscaProduto" wire:input="buscarProdutos" placeholder="Buscar produto para adicionar..." style="width:100%;height:38px;padding:0 10px;border:1px solid var(--border);border-radius:8px;font-size:13px;">
                            @if (count($this->produtosResultado) > 0)
                                <div style="position:absolute;z-index:10;left:0;right:0;top:40px;max-height:260px;overflow:auto;border:1px solid var(--border);border-radius:8px;background:var(--surface);box-shadow:0 8px 24px rgba(0,0,0,0.12);">
                                    @foreach ($this->produtosResultado as $pr)
                                        <button type="button" wire:click="adicionarProduto({{ $pr['id'] }})" style="display:flex;align-items:center;gap:8px;width:100%;padding:8px 12px;border:0;border-bottom:1px solid var(--border);background:none;cursor:pointer;text-align:left;font-size:12px;color:var(--text);">
                                            <strong style="flex:1;">{{ $pr['nome'] }}</strong>
                                            <span style="color:var(--muted);font-size:10px;">{{ $pr['sku'] }}</span>
                                            <span style="color:var(--success);font-weight:700;">R$ {{ number_format($pr['preco_atual'], 2, ',', '.') }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Product list --}}
                        @if (count($this->produtosCampanha) > 0)
                            {{-- Bulk discount --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;padding:10px;background:color-mix(in srgb, var(--primary-500) 5%, var(--surface));border-radius:8px;">
                                <span style="font-size:11px;font-weight:600;color:var(--text);white-space:nowrap;">Desconto em Lote:</span>
                                <input wire:model="descontoEmLote" type="text" inputmode="decimal" placeholder="10" style="width:60px;height:32px;padding:0 6px;border:1px solid var(--border);border-radius:5px;font-size:12px;text-align:center;">
                                <select wire:model="tipoDescontoLote" style="height:32px;padding:0 4px;border:1px solid var(--border);border-radius:5px;font-size:11px;">
                                    <option value="percentual">%</option>
                                    <option value="valor">R$</option>
                                </select>
                                <button type="button" wire:click="aplicarDescontoEmLote" class="btn-sm btn-primary" style="height:32px;font-size:10px;"><i class="fas fa-check"></i> Aplicar</button>
                            </div>
                            <div style="max-height:400px;overflow-y:auto;">
                                <table class="data-table" style="font-size:11px;">
                                    <thead><tr style="position:sticky;top:0;background:var(--surface);"><th class="data-table-th text-left">Produto</th><th class="data-table-th text-right">De (R$)</th><th class="data-table-th text-right">Por (R$)</th><th class="data-table-th text-center">%</th><th class="data-table-th text-right">Clube</th><th class="data-table-th text-center" style="width:30px;"></th></tr></thead>
                                    <tbody>
                                        @foreach ($this->produtosCampanha as $idx => $p)
                                            <tr class="data-table-tr">
                                                <td class="data-table-td font-semibold" style="text-transform:uppercase;font-size:10px;">{{ $p['nome'] }}</td>
                                                <td class="data-table-td"><input wire:model.blur="produtosCampanha.{{ $idx }}.preco_de" type="text" inputmode="decimal" placeholder="0,00" style="width:70px;padding:3px 4px;border:1px solid var(--border);border-radius:4px;font-size:11px;text-align:right;"></td>
                                                <td class="data-table-td"><input wire:model.blur="produtosCampanha.{{ $idx }}.preco_por" type="text" inputmode="decimal" placeholder="0,00" style="width:70px;padding:3px 4px;border:1px solid var(--border);border-radius:4px;font-size:11px;text-align:right;font-weight:700;color:var(--danger);"></td>
                                                <td class="data-table-td text-center font-bold" style="color:var(--danger);font-size:12px;">{{ $p['desconto_pct'] > 0 ? $p['desconto_pct'] . '%' : '—' }}</td>
                                                <td class="data-table-td"><input wire:model.blur="produtosCampanha.{{ $idx }}.preco_clube" type="text" inputmode="decimal" placeholder="0,00" style="width:70px;padding:3px 4px;border:1px solid var(--border);border-radius:4px;font-size:11px;text-align:right;"></td>
                                                <td class="data-table-td text-center"><button type="button" wire:click="removerProduto({{ $idx }})" style="background:none;border:0;color:var(--danger);cursor:pointer;padding:4px;"><i class="fas fa-times"></i></button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div style="text-align:center;padding:24px;color:var(--muted);font-size:12px;"><i class="fas fa-box-open" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.3;"></i>Nenhum produto adicionado.<br>Busque acima para adicionar.</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
