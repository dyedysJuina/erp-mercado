<div>
    {{-- TOAST --}}
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span>
        <span x-text="msg"></span>
    </div>

    <div class="main-content-pad">

        {{-- HEADER + BREADCRUMB --}}
        <div class="header-row">
            <div>
                <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                <h1 class="page-title">Variações</h1>
                <div class="breadcrumb">
                    <span>Início</span>
                    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Produtos</span>
                    @if ($this->produtoBaseSelecionado)
                    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>{{ $this->produtoBase['nome'] ?? '' }}</span>
                    @endif
                    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Variações</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        {{-- BUSCA DE PRODUTO --}}
        <div class="search-card" x-data="{ open: false }">
            <div class="search-input-wrap">
                <i class="fas fa-search search-input-icon"></i>
                <input wire:model.live.debounce.300ms="buscaProduto" placeholder="Buscar produto base pelo nome..." @focus="open = true" @click.outside="open = false"
                       class="search-input-field">
            </div>
            @if (count($this->resultadosBusca) > 0)
                <div x-show="open" class="search-dropdown">
                    @foreach ($this->resultadosBusca as $p)
                        <button wire:click="selecionarProduto({{ $p['id'] }})" @click="open = false" class="search-dropdown-item">
                            <span class="search-dropdown-name">{{ $p['nome'] }}</span>
                            <span class="search-dropdown-cat">{{ $p['categoria']['caminho'] ?? '' }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($this->produtoBaseSelecionado)
        {{-- SUB-HEADER PRODUTO --}}
        <div class="subheader-card">
            <div class="subheader-inner">
                <button wire:click="limparSelecao" class="subheader-btn"><i class="fas fa-arrow-left"></i> Voltar</button>
                <div class="subheader-divider"></div>
                <div class="subheader-icon"><i class="fas fa-box"></i></div>
                <div>
                    <div class="subheader-name-row">
                        <span class="subheader-name">{{ $this->produtoBase['nome'] ?? '' }}</span>
                        <span class="badge-ativo-sm">Ativo</span>
                    </div>
                    <span class="subheader-count">{{ $this->totalGeral }} variação(ões) cadastrada(s)</span>
                </div>
            </div>
        </div>
        @endif

        {{-- MÉTRICAS --}}
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon purple"><i class="fas fa-layer-group"></i></div>
                <div><span class="metric-label">Total</span><span class="metric-value">{{ $this->totalGeral }}</span><span class="metric-sub">Variações cadastradas</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon green"><i class="fas fa-check-circle"></i></div>
                <div><span class="metric-label">Ativas</span><span class="metric-value">{{ $this->totalAtivas }}</span><span class="metric-sub green">Em operação</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon amber"><i class="fas fa-minus-circle"></i></div>
                <div><span class="metric-label">Inativas</span><span class="metric-value">{{ $this->totalGeral - $this->totalAtivas }}</span><span class="metric-sub amber">Desativadas</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon blue"><i class="fas fa-barcode"></i></div>
                <div><span class="metric-label">Com SKU</span><span class="metric-value">{{ $this->totalComSku }}</span><span class="metric-sub">Possuem SKU</span></div>
            </div>
        </div>

        @if ($this->produtoBaseSelecionado)
        {{-- TWO-COLUMN LAYOUT --}}
        <div class="grid-2col-custom">

            {{-- LEFT: FORM WITH TABS --}}
            <div>
                <div class="form-card" x-data="{ activeTab: 'identificacao' }">
                    <div class="form-card-header">
                        <span class="form-card-title"><i class="fas fa-pen-to-square form-card-icon"></i> {{ $this->modo === 'create' ? 'Nova Variação' : 'Editando Variação' }}</span>
                        <span class="form-card-subtitle">Preencha as informações para registrar uma nova variação do produto.</span>
                    </div>

                    {{-- TABS NAV --}}
                    <div class="tab-nav">
                        <button @click="activeTab = 'identificacao'" :class="activeTab === 'identificacao' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-info-circle"></i> Principais</button>
                        <button @click="activeTab = 'codigos'" :class="activeTab === 'codigos' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-qrcode"></i> Códigos</button>
                        <button @click="activeTab = 'regras'" :class="activeTab === 'regras' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-gavel"></i> Regras</button>
                        <button @click="activeTab = 'apresentacoes'" :class="activeTab === 'apresentacoes' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-palette"></i> Apresentações</button>
                        <button @click="activeTab = 'fiscais'" :class="activeTab === 'fiscais' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-file-invoice"></i> Fiscais</button>
                    </div>

                    <form wire:submit="salvar" class="form-body">
                        @if (count($this->atributosCategoria) > 0)
                        <div class="alert-info"><i class="fas fa-tags"></i> Esta categoria possui atributos. Vá até a aba "Apresentações" para preenchê-los.</div>
                        @endif

                        {{-- TAB 1: PRINCIPAIS --}}
                        <div x-show="activeTab === 'identificacao'">
                            <div class="section-border">Dados Cadastrais</div>
                            <div class="grid-2" style="margin-bottom:14px;">
                                <div class="field"><label>SKU (Código Interno)</label><input wire:model="sku" placeholder="Ex: ARROZ-PAR-1KG" style="font-family:monospace;">@error('sku')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field" style="position:relative;" x-data="{ searchMarca: '', openMarca: false, marcasOpts: [], async loadMarcas() { this.marcasOpts = await this.$wire.opts_marcas; } }">
                                    <label>Marca</label>
                                    <input x-model="searchMarca" @focus="loadMarcas(); openMarca = true;" @keydown.escape="openMarca = false" placeholder="Digite para buscar..." @click.outside="openMarca = false">
                                    <div x-show="openMarca && marcasOpts.length > 0" x-cloak class="marca-dropdown">
                                        <template x-for="m in marcasOpts.filter(o => !searchMarca || (o.nome||'').toLowerCase().includes(searchMarca.toLowerCase()))" :key="m.id">
                                            <button @click="$wire.set('marca_id', m.id); searchMarca = m.nome; openMarca = false" class="marca-dropdown-item" x-text="m.nome"></button>
                                        </template>
                                    </div>
                                    <div x-show="openMarca && marcasOpts.length === 0" style="padding:8px;font-size:12px;color:var(--muted);">Nenhuma marca encontrada</div>
                                </div>
                            </div>
                            <div class="grid-4" style="margin-bottom:14px;">
                                <div class="field"><label>Embalagem</label><select wire:model.live="embalagem_id"><option value="">Selecione...</option>@foreach ($this->optsEmbalagens() as $e)<option value="{{ $e['id'] }}">{{ $e['nome'] }} {{ $e['sigla'] ? '('.$e['sigla'].')' : '' }}</option>@endforeach</select></div>
                                <div class="field"><label>Unid. por emb.</label><input wire:model.live="qtd_por_embalagem" type="number" min="1" step="1" value="1" placeholder="1"></div>
                                <div class="field"><label>Conteúdo</label><input wire:model.live="conteudo_quantidade" type="number" step="0.001" min="0.001" placeholder="Ex: 1">@error('conteudo_quantidade')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field"><label>Unidade</label><select wire:model.live="unidade_medida_id"><option value="">Selecione...</option>@foreach ($this->optsUnidades() as $u)<option value="{{ $u['id'] }}">{{ $u['sigla'] }} - {{ $u['nome'] }}</option>@endforeach</select>@error('unidade_medida_id')<div class="err">{{ $message }}</div>@enderror</div>
                            </div>
                            <div class="name-preview">
                                <span class="name-preview-label">Nome gerado comercialmente:</span>
                                <span class="name-preview-value">{{ $this->nomePreview ?: '—' }}</span>
                            </div>
                        </div>

                        {{-- TAB 2: CÓDIGOS --}}
                        <div x-show="activeTab === 'codigos'">
                            <div class="section-border">Cadastro de Códigos de Barras</div>
                            <div class="sub-card">
                                @forelse ($this->codigosBarras as $idx => $cb)
                                    <div class="codigo-row">
                                        <input wire:model="codigosBarras.{{ $idx }}.codigo" placeholder="Código" x-data x-mask="9999999999999" maxlength="13">
                                        <select wire:model="codigosBarras.{{ $idx }}.tipo">@foreach ($this->optsTiposCodigo() as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
                                        <input wire:model="codigosBarras.{{ $idx }}.descricao" placeholder="Descrição">
                                        <button type="button" wire:click="removerCodigoBarras({{ $idx }})" class="btn-remove">&times;</button>
                                    </div>
                                @empty
                                    <div class="empty-state-sm"><i class="fas fa-barcode"></i> Nenhum código de barras cadastrado.</div>
                                @endforelse
                                <button type="button" wire:click="adicionarCodigoBarras" class="btn-sm btn-secondary" style="margin-top:8px;">+ Adicionar código</button>
                            </div>
                        </div>

                        {{-- TAB 3: REGRAS --}}
                        <div x-show="activeTab === 'regras'">
                            <div class="section-border">Parâmetros Operacionais</div>
                            <div class="sub-card">
                                <div class="grid-3" style="gap:16px;">
                                    <div class="check-group" style="gap:8px;">
                                        <label style="height:36px;"><input wire:model="ativo" type="checkbox" checked> Ativo</label>
                                        <label style="height:36px;"><input wire:model="fracionado" type="checkbox"> Fracionado</label>
                                        <label style="height:36px;"><input wire:model="pesavel" type="checkbox"> Pesável</label>
                                    </div>
                                    <div class="field"><label>Qtde Mínima</label><input wire:model="quantidade_minima_venda" type="number" step="0.001" min="0">@error('quantidade_minima_venda')<div class="err">{{ $message }}</div>@enderror</div>
                                    <div class="field"><label>Passo Venda</label><input wire:model="passo_venda" type="number" step="0.001" min="0">@error('passo_venda')<div class="err">{{ $message }}</div>@enderror</div>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 4: APRESENTAÇÕES --}}
                        <div x-show="activeTab === 'apresentacoes'">
                            <div style="display:flex;align-items:center;justify-content:space-between;" class="section-border" style="margin-bottom:16px;">
                                <span>Apresentações</span>
                                <button type="button" wire:click="adicionarApresentacao" class="btn-sm btn-secondary">+ Adicionar</button>
                            </div>
                            @if (count($this->atributosCategoria) > 0)
                            <div class="sub-card" style="margin-bottom:16px;">
                                <div class="section-title">Atributos da Categoria</div>
                                <div class="grid-3">
                                    @foreach ($this->atributosCategoria as $attr)
                                        <div class="field">
                                            <label>{{ $attr['nome'] }}</label>
                                            @if ($attr['tipo']==='booleano')
                                                <select wire:model="atributosValores.{{ $attr['id'] }}.valor_booleano"><option value="">—</option><option value="1">Sim</option><option value="0">Não</option></select>
                                            @elseif ($attr['tipo']==='lista' && $attr['opcoes'])
                                                <select wire:model="atributosValores.{{ $attr['id'] }}.valor_texto"><option value="">Selecione...</option>@foreach ($attr['opcoes'] as $opt)<option value="{{ $opt }}">{{ $opt }}</option>@endforeach</select>
                                            @elseif (in_array($attr['tipo'],['numero','decimal']))
                                                <input wire:model="atributosValores.{{ $attr['id'] }}.valor_numero" type="number" step="any">
                                            @elseif ($attr['tipo']==='data')
                                                <input wire:model="atributosValores.{{ $attr['id'] }}.valor_data" type="date">
                                            @else
                                                <input wire:model="atributosValores.{{ $attr['id'] }}.valor_texto" placeholder="Valor">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            <div class="sub-card">
                                <div class="section-title">Apresentações Cadastradas</div>
                                @forelse ($this->apresentacoes as $idx => $ap)
                                    <div class="apres-item">
                                        <div class="grid-3" style="gap:6px;">
                                            <input wire:model="apresentacoes.{{ $idx }}.nome" placeholder="Ex: Fardo 12un">
                                            <input wire:model="apresentacoes.{{ $idx }}.conteudo_quantidade" type="number" step="0.001" min="0.001" placeholder="Qtde">
                                            <select wire:model="apresentacoes.{{ $idx }}.tipo">@foreach ($this->optsTiposApresentacao() as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
                                        </div>
                                        <div class="apres-checkboxes">
                                            <label><input wire:model="apresentacoes.{{ $idx }}.permite_venda" type="checkbox"> Venda</label>
                                            <label><input wire:model="apresentacoes.{{ $idx }}.permite_compra" type="checkbox"> Compra</label>
                                            <label><input wire:model="apresentacoes.{{ $idx }}.controla_estoque" type="checkbox"> Estoque</label>
                                        </div>
                                        <button type="button" wire:click="removerApresentacao({{ $idx }})" class="apres-remove">&times;</button>
                                    </div>
                                @empty
                                    <div class="empty-state-sm"><i class="fas fa-box"></i> Nenhuma apresentação cadastrada.</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- TAB 5: FISCAIS --}}
                        <div x-show="activeTab === 'fiscais'">
                            <div class="section-border">Dados Tributários</div>
                            <div class="sub-card">
                                <div class="grid-2" style="gap:12px;">
                                    <div class="field" style="position:relative;">
                                        <label>NCM</label>
                                        <input wire:model.live.debounce.300ms="ncm_busca" placeholder="Buscar por código ou descrição...">
                                        @if ($this->ncm_id)
                                            @php $ncmSel = \App\Models\Ncm::find((int)$this->ncm_id); @endphp
                                            @if ($ncmSel)
                                                <div class="ncm-selected"><b>{{ $ncmSel->codigo }}</b> — {{ $ncmSel->descricao }}<button type="button" wire:click="$set('ncm_id', null)" class="ncm-clear">&times;</button></div>
                                            @endif
                                        @endif
                                        @if (strlen(trim($this->ncm_busca)) >= 1 && count($this->ncmOpts) > 0)
                                            <div class="ncm-dropdown">
                                                @foreach ($this->ncmOpts as $n)
                                                    <button type="button" wire:click="selecionarNcm({{ $n['id'] }})" class="ncm-dropdown-item"><b>{{ $n['codigo'] }}</b> — {{ $n['descricao'] }}</button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="field"><label>CEST</label><select wire:model.live="cest_id"><option value="">N/A</option>@foreach (\App\Models\Cest::where('ativo', true)->orderBy('codigo')->get() as $c)<option value="{{ $c->id }}">{{ $c->codigo }} — {{ $c->descricao }}</option>@endforeach</select></div>
                                    <div class="field"><label>CFOP</label><select wire:model.live="cfop_id"><option value="">Selecione...</option>@foreach ($this->cfopOpts as $c)<option value="{{ $c['id'] }}">{{ $c['codigo'] }} — {{ $c['descricao'] }}</option>@endforeach</select></div>
                                    <div class="field"><label>CSOSN</label><select wire:model.live="cst_icms"><option value="">Selecione...</option>@foreach ($this->csosnOpts as $cs)<option value="{{ $cs['codigo'] }}">{{ $cs['codigo'] }} — {{ $cs['descricao'] }}</option>@endforeach</select></div>
                                    <div class="field"><label>Origem</label><select wire:model.live="origem_mercadoria"><option value="0">Nacional</option><option value="1">Estrangeira (importação)</option><option value="2">Estrangeira (mercado interno)</option></select></div>
                                    <div>
                                        <label class="check-group" style="height:auto;margin-bottom:6px;"><input type="checkbox" wire:model.live="usar_aliquotas"> Alíquotas manuais</label>
                                        @if ($this->usar_aliquotas)
                                            <div class="grid-3" style="gap:6px;">
                                                <div class="field"><label style="font-size:11px;">ICMS (%)</label><input type="number" step="0.01" wire:model.blur="aliquota_icms" placeholder="Ex: 18"></div>
                                                <div class="field"><label style="font-size:11px;">PIS (%)</label><input type="number" step="0.01" wire:model.blur="aliquota_pis" placeholder="Ex: 1.65"></div>
                                                <div class="field"><label style="font-size:11px;">COFINS (%)</label><input type="number" step="0.01" wire:model.blur="aliquota_cofins" placeholder="Ex: 7.6"></div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BOTÕES --}}
                        @error('exclusao')<div class="err" style="margin-bottom:8px;">{{ $message }}</div>@enderror
                        <div class="form-actions">
                            <button type="submit" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-floppy-disk"></i> {{ $this->modo === 'create' ? 'Salvar Variação' : 'Atualizar' }}</span><span wire:loading>Salvando...</span></button>
                            <button type="button" wire:click="novaVariacao" class="btn-secondary btn-lg">Cancelar</button>
                            @if ($this->modo === 'edit' && $this->editandoId)
                                <button type="button" wire:click="excluir({{ $this->editandoId }})" wire:confirm="Excluir?" class="btn-danger btn-lg" style="margin-left:auto;">Excluir</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT: SIDEBAR --}}
            <div style="display:flex;flex-direction:column;gap:16px;">

                {{-- CARD 1: IMAGENS --}}
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-image"></i> Imagens <span class="img-count">{{ count($this->novasImagens) }}/8</span></div>
                    <div class="sidebar-card-body">
                        <div onclick="document.getElementById('imgInputVariacao').click()" class="img-upload-zone">
                            <input type="file" wire:model="novasImagens" multiple accept="image/jpeg,image/png,image/webp,image/avif" id="imgInputVariacao" style="display:none;">
                            <div class="img-upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                            <span class="img-upload-text">Clique para selecionar</span>
                            <span class="img-upload-hint">JPG, PNG, WebP • máx 5MB</span>
                        </div>
                        @if (count($this->novasImagens) > 0)
                            <div class="img-grid">
                                @foreach ($this->novasImagens as $idx => $img)
                                    <div class="img-thumb-wrap">
                                        <img src="{{ $img->temporaryUrl() }}" class="img-thumb-img">
                                        <button type="button" wire:click="removerImagemTemp({{ $idx }})" class="img-thumb-remove">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if ($this->modo === 'edit' && $this->editandoId)
                            @php $imgsSalvas = \App\Models\ProdutoImagem::where('produto_variacao_id', $this->editandoId)->orderBy('ordem')->get(); @endphp
                            @if ($imgsSalvas->count() > 0)
                                <div style="margin-top:12px;">
                                    <span class="section-title">Imagens Salvas</span>
                                    <div class="img-grid">
                                        @foreach ($imgsSalvas as $img)
                                            <div class="img-thumb-wrap" style="{{ !$img->is_active ? 'opacity:0.4;' : '' }}">
                                                <img src="{{ $img->url() }}" class="img-thumb-img">
                                                @if ($img->principal) <span class="img-principal-badge">P</span> @endif
                                                <div class="img-actions-overlay">
                                                    <button type="button" wire:click="definirPrincipal({{ $img->id }})" title="Principal">★</button>
                                                    <button type="button" wire:click="desativarImagem({{ $img->id }})">{{ $img->is_active ? '✕' : '✓' }}</button>
                                                    <button type="button" wire:click="removerImagemSalva({{ $img->id }})" wire:confirm="Remover?">&times;</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- CARD 2: RESUMO --}}
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-receipt"></i> Resumo</div>
                    <div class="sidebar-card-body">
                        <div class="resumo-list">
                            <div class="resumo-row"><span class="resumo-label">SKU</span><span class="resumo-value-mono">{{ $this->sku ?: '—' }}</span></div>
                            <div class="resumo-col"><span class="resumo-label">Nome</span><span class="resumo-value">{{ $this->nomePreview ?: '—' }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Unidade</span><span class="resumo-value">{{ $this->unidade_medida_id ? (\App\Models\UnidadeMedida::find((int)$this->unidade_medida_id)?->sigla ?? '—') : '—' }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Status</span><span class="resumo-badge {{ $this->ativo ? 'resumo-badge-ativo' : 'resumo-badge-inativo' }}">{{ $this->ativo ? 'Ativa' : 'Inativa' }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: VARIAÇÕES --}}
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-list"></i> Variações ({{ $this->totalGeral }})</div>
                    <div class="sidebar-card-body" style="padding:8px 12px;max-height:280px;overflow-y:auto;">
                        @forelse ($this->listaVariacoes as $v)
                            <div class="var-sidebar-item {{ $this->editandoId === $v['id'] ? 'ativa' : '' }}">
                                <div class="var-sidebar-icon">{{ $v['unidade_medida']['sigla'] ?? 'UN' }}</div>
                                <div style="flex:1;min-width:0;">
                                    <span class="var-sidebar-name">{{ $v['nome_completo'] }}</span>
                                    @if ($v['sku'])<span class="var-sidebar-sku">SKU: {{ $v['sku'] }}</span>@endif
                                </div>
                                <div class="var-sidebar-actions">
                                    <button wire:click="editarVariacao({{ $v['id'] }})" title="Editar"><i class="fas fa-pen"></i></button>
                                    <button wire:click="duplicarVariacao({{ $v['id'] }})" title="Duplicar"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state-sm"><i class="fas fa-cube"></i> Nenhuma variação cadastrada.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE SECTION --}}
        <div class="data-table-wrap">
            <div class="data-table-header">
                <span class="data-table-title"><i class="fas fa-table-list"></i> Todas as Variações <span class="data-table-count">({{ $this->totalGeral }})</span></span>
                <input wire:model.live.debounce.300ms="filtroLista" placeholder="Filtrar variações..." class="data-table-filter">
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="data-table-th text-left">Variação</th>
                            <th class="data-table-th text-left">SKU</th>
                            <th class="data-table-th text-center">Marca</th>
                            <th class="data-table-th text-center">Qtde</th>
                            <th class="data-table-th text-center">Status</th>
                            <th class="data-table-th text-center" style="width:130px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->listaVariacoes as $v)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold cursor-pointer" wire:click="editarVariacao({{ $v['id'] }})">{{ $v['nome_completo'] }}</td>
                                <td class="data-table-td font-mono text-muted">{{ $v['sku'] ?? '—' }}</td>
                                <td class="data-table-td text-center">{{ $v['marca']['nome'] ?? '—' }}</td>
                                <td class="data-table-td text-center font-semibold">{{ $v['conteudo_quantidade'] }} {{ $v['unidade_medida']['sigla'] ?? '' }}</td>
                                <td class="data-table-td text-center">
                                    <span class="badge-sm {{ $v['ativo'] ? 'badge-ativo' : 'badge-inativo' }}" style="font-size:10px;padding:4px 12px;">{{ $v['ativo'] ? 'Ativo' : 'Inativo' }}</span>
                                </td>
                                <td class="data-table-td text-center">
                                    <div class="table-actions">
                                        <button wire:click="editarVariacao({{ $v['id'] }})" class="table-action-btn" style="color:var(--primary-600);" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button wire:click="desativar({{ $v['id'] }})" class="table-action-btn" style="color:{{ $v['ativo'] ? 'var(--warning)' : 'var(--success)' }};" title="{{ $v['ativo'] ? 'Desativar' : 'Ativar' }}"><i class="fas {{ $v['ativo'] ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                                        <button wire:click="duplicarVariacao({{ $v['id'] }})" class="table-action-btn" style="color:#3b82f6;" title="Duplicar"><i class="fas fa-copy"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="data-table-empty">Nenhuma variação encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($this->produtoBaseSelecionado && method_exists($this->listaVariacoes, 'hasPages') && $this->listaVariacoes->hasPages())
                <div class="data-table-pagination">
                    {{ $this->listaVariacoes->links('livewire.tailwind-pagination') }}
                </div>
            @endif
        </div>

        @else
        {{-- NO PRODUCT SELECTED --}}
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-code-branch"></i></div>
            <span class="empty-state-title">Nenhum produto selecionado</span>
            <span class="empty-state-desc">Busque um produto base acima para gerenciar suas variações.</span>
        </div>
        @endif

    </div>
</div>
