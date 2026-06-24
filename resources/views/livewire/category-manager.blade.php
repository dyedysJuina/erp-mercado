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
        <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Categorias</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Cadastros</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Categorias</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
        <div class="metrics-grid" style="grid-template-columns:repeat(5,1fr);">
            <div class="metric-card" style="padding:10px 14px;"><div class="metric-icon purple" style="width:34px;height:34px;font-size:13px;"><i class="fas fa-folder-tree"></i></div><div><span class="metric-label" style="font-size:9px;">Raiz</span><span class="metric-value" style="font-size:16px;">{{ $this->metrics['raiz'] }}</span></div></div>
            <div class="metric-card" style="padding:10px 14px;"><div class="metric-icon blue" style="width:34px;height:34px;font-size:13px;"><i class="fas fa-sitemap"></i></div><div><span class="metric-label" style="font-size:9px;">Nível 2</span><span class="metric-value" style="font-size:16px;">{{ $this->metrics['n2'] }}</span></div></div>
            <div class="metric-card" style="padding:10px 14px;"><div class="metric-icon green" style="width:34px;height:34px;font-size:13px;"><i class="fas fa-tags"></i></div><div><span class="metric-label" style="font-size:9px;">Nível 3+</span><span class="metric-value" style="font-size:16px;">{{ $this->metrics['n3'] }}</span></div></div>
            <div class="metric-card" style="padding:10px 14px;"><div class="metric-icon" style="width:34px;height:34px;border-radius:8px;font-size:13px;background:color-mix(in srgb,#f59e0b 12%,transparent);color:#f59e0b;"><i class="fas fa-boxes-packing"></i></div><div><span class="metric-label" style="font-size:9px;">Produtos</span><span class="metric-value" style="font-size:16px;">{{ number_format($this->metrics['produtos'],0,',','.') }}</span></div></div>
            <div class="metric-card" style="padding:10px 14px;"><div class="metric-icon" style="width:34px;height:34px;border-radius:8px;font-size:13px;background:color-mix(in srgb,#ef4444 12%,transparent);color:#ef4444;"><i class="fas fa-folder-minus"></i></div><div><span class="metric-label" style="font-size:9px;">Inativas</span><span class="metric-value" style="font-size:16px;">{{ $this->metrics['inativas'] }}</span></div></div>
        </div>
        <div style="display:grid;grid-template-columns:4fr 5fr 3fr;gap:16px;align-items:start;">
            {{-- ÁRVORE --}}
            <div class="sub-card" style="padding:0;overflow:hidden;">
                <div class="col-header" style="font-size:10px;"><span><i class="fas fa-folder-tree"></i> Árvore</span><button wire:click="novaRaiz" class="btn-sm btn-primary" style="font-size:9px;height:26px;padding:0 8px;"><i class="fas fa-plus"></i></button></div>
                <div style="padding:5px 8px;border-bottom:1px solid var(--border);"><input wire:model.live.debounce.300ms="treeSearch" placeholder="Buscar..." style="width:100%;height:28px;padding:0 6px;border:1px solid var(--border);border-radius:5px;font-size:11px;"></div>
                <div style="max-height:480px;overflow-y:auto;">
                    @forelse ($this->estrutura as $depto)
                        <div x-data="{ open: true }">
                            <div @click="open = !open" wire:click="selectCategoria({{ $depto['id'] }})" style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;cursor:pointer;font-size:12px;font-weight:700;{{ $this->editingId === $depto['id'] ? 'background:color-mix(in srgb,#6366f1 5%,var(--surface));' : '' }}"><span><i class="fas" :class="open ? 'fa-folder-open' : 'fa-folder'" style="color:#6366f1;width:14px;font-size:11px;"></i> {{ $depto['nome'] }}</span><span style="font-size:9px;color:var(--muted);">{{ $depto['qtd'] }}</span></div>
                            <div x-show="open" x-collapse>
                                @foreach ($depto['categorias'] as $cat)
                                    <div x-data="{ open2: false }">
                                        <div @click="open2 = !open2" wire:click="selectCategoria({{ $cat['id'] }})" style="display:flex;align-items:center;justify-content:space-between;padding:5px 10px 5px 24px;cursor:pointer;font-size:11px;{{ $this->editingId === $cat['id'] ? 'background:color-mix(in srgb,#6366f1 4%,var(--surface));' : '' }}"><span><i class="fas" :class="open2 ? 'fa-folder-open' : 'fa-folder'" style="color:#6366f1;font-size:9px;width:12px;"></i> {{ $cat['nome'] }}</span><span style="font-size:9px;color:var(--muted);">{{ $cat['qtd'] }}</span></div>
                                        <div x-show="open2" x-collapse>
                                            @foreach ($cat['subs'] as $sub)
                                                <div wire:click="selectCategoria({{ $sub['id'] }})" style="display:flex;align-items:center;justify-content:space-between;padding:4px 10px 4px 40px;cursor:pointer;font-size:11px;{{ $this->editingId === $sub['id'] ? 'background:color-mix(in srgb,#6366f1 4%,var(--surface));border-left:2px solid #6366f1;' : '' }}"><span><i class="fas fa-tag" style="color:#94a3b8;font-size:8px;width:10px;"></i> {{ $sub['nome'] }}</span><span style="font-size:8px;color:var(--muted);">{{ $sub['qtd'] }}</span></div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div style="padding:24px;text-align:center;color:var(--muted);font-size:12px;">Nenhuma categoria.</div>
                    @endforelse
                </div>
            </div>
            {{-- FORMULÁRIO --}}
            <div class="sub-card" style="padding:0;overflow:hidden;">
                <div class="col-header"><div style="width:28px;height:28px;border-radius:6px;background:color-mix(in srgb,#6366f1 10%,transparent);display:flex;align-items:center;justify-content:center;color:#6366f1;font-size:11px;"><i class="fas fa-sitemap"></i></div><span style="font-weight:800;font-size:13px;color:var(--text);margin-left:8px;">{{ $this->mode === 'create' ? 'Nova Categoria' : $this->nome }}</span>@if ($this->editingId)@php $catAtual = \App\Models\Categoria::find($this->editingId); @endphp<span style="font-size:9px;color:var(--muted);margin-left:6px;">{{ $catAtual?->caminho ?? '' }}</span>@endif@if ($this->mode === 'edit')<button wire:click="novaRaiz" class="btn-sm btn-secondary" style="margin-left:auto;font-size:9px;height:26px;">✕</button>@endif</div>
                <form wire:submit="save">
                    <div style="padding:12px 14px;">
                        <div style="margin-bottom:8px;"><div class="field"><label>Nome *</label><input wire:model.blur="nome" placeholder="Ex: Arroz, Bebidas..." id="campo-nome">@error('nome')<div class="err">{{ $message }}</div>@enderror</div></div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:8px;"><div class="field"><label>Nível 1</label><select wire:model.live="nivel1"><option value="">Selecione...</option>@foreach ($this->deptos as $d)<option value="{{ $d['id'] }}">{{ $d['nome'] }}</option>@endforeach</select></div><div class="field"><label>Nível 2</label><select wire:model.live="nivel2" {{ !$this->nivel1 ? 'disabled' : '' }}><option value="">Selecione...</option>@foreach ($this->categorias as $c)<option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>@endforeach</select></div><div class="field"><label>Nível 3</label><select wire:model.live="nivel3" {{ !$this->nivel2 ? 'disabled' : '' }}><option value="">Selecione...</option>@foreach ($this->subcategorias as $s)<option value="{{ $s['id'] }}">{{ $s['nome'] }}</option>@endforeach</select></div></div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;"><div class="field"><label>Descrição</label><input wire:model="descricao" placeholder="Descrição"></div><div class="field"><label>Status</label><select wire:model="status"><option value="active">Ativo</option><option value="inactive">Inativo</option><option value="pending">Pendente</option></select>@error('status')<div class="err">{{ $message }}</div>@enderror</div></div>
                        @error('nivel1')<div style="padding:6px;border-radius:4px;font-size:11px;background:color-mix(in srgb,var(--danger)8%,transparent);color:var(--danger);margin-bottom:6px;">{{ $message }}</div>@enderror
                        @error('exclusao')<div style="padding:6px;border-radius:4px;font-size:11px;background:color-mix(in srgb,var(--danger)8%,transparent);color:var(--danger);margin-bottom:6px;">{{ $message }}</div>@enderror
                        <div style="display:flex;gap:6px;flex-wrap:wrap;padding-top:8px;border-top:1px solid var(--border);"><button type="submit" class="btn-primary btn-sm" style="height:34px;padding:0 12px;"><i class="fas fa-save"></i> Salvar</button><button type="button" wire:click="saveAndAddSub" class="btn-secondary btn-sm" style="height:34px;padding:0 12px;">+ Sub</button>@if ($this->mode === 'edit' && $this->editingId)<button type="button" wire:click="excluir({{ $this->editingId }})" wire:confirm="Excluir?" class="btn-danger btn-sm" style="height:34px;padding:0 12px;margin-left:auto;">Excluir</button>@endif</div>
                    </div>
                </form>
            </div>
            {{-- ESTATÍSTICAS --}}
            <div class="sub-card" style="padding:0;overflow:hidden;">
                <div class="col-header" style="font-size:10px;"><i class="fas fa-chart-bar"></i> Estatísticas</div>
                <div style="padding:12px 14px;">
                    @if ($this->editingId)
                        @php $catAtual = \App\Models\Categoria::find($this->editingId); $qtdProdutos = \App\Models\ProdutoBase::where('categoria_id', $this->editingId)->count(); $qtdFilhos = \App\Models\Categoria::where('parent_id', $this->editingId)->count(); $created = $catAtual?->created_at ?? now(); @endphp
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;"><span style="color:var(--muted);">Produtos</span><span style="font-weight:700;">{{ $qtdProdutos }}</span></div>
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;"><span style="color:var(--muted);">Subcategorias</span><span style="font-weight:700;">{{ $qtdFilhos }}</span></div>
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;"><span style="color:var(--muted);">Nível</span><span style="font-weight:700;">{{ $catAtual?->nivel ?? $this->nivelDestino() + 1 }}</span></div>
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;"><span style="color:var(--muted);">Slug</span><span style="font-weight:700;font-size:10px;word-break:break-all;max-width:120px;text-align:right;">{{ $catAtual?->slug ?? '' }}</span></div>
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;"><span style="color:var(--muted);">Status</span><span class="badge-sm {{ $catAtual?->ativo ? 'badge-ativo' : 'badge-inativo' }}" style="font-size:8px;">{{ $catAtual?->ativo ? 'Ativo' : 'Inativo' }}</span></div>
                        <div style="padding-top:10px;margin-top:8px;border-top:1px solid var(--border);font-size:10px;color:var(--muted);"><div style="margin-bottom:4px;"><span style="font-weight:600;color:var(--text);">Criada em:</span><br>{{ $created->format('d/m/Y H:i') }}</div><div><span style="font-weight:600;color:var(--text);">Alterada em:</span><br>{{ $catAtual?->updated_at?->format('d/m/Y H:i') ?? '—' }}</div></div>
                    @else
                        <div style="text-align:center;padding:20px 0;color:var(--muted);font-size:12px;"><i class="fas fa-chart-simple" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.3;"></i>Selecione uma categoria</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('focus-nome', () => {
            setTimeout(() => { const el = document.getElementById('campo-nome'); if (el) { el.focus(); el.select(); } }, 50);
        });
    });
</script>
