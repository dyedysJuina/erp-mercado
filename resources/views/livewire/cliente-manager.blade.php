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
                <h1 class="page-title">
                    @if ($viewState === 'list')
                        Clientes
                    @elseif ($viewState === 'create')
                        Novo Cliente
                    @else
                        {{ $this->nome ?: 'Cliente' }}
                    @endif
                </h1>
                <div class="breadcrumb">
                    <span>Início</span>
                    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Cadastros</span>
                    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    @if ($viewState !== 'list')
                        <span style="cursor:pointer;color:var(--primary-600);font-weight:600;" wire:click="voltarLista">Clientes</span>
                        <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    @endif
                    <span class="breadcrumb-active">{{ $viewState === 'list' ? 'Clientes' : ($viewState === 'create' ? 'Novo' : $this->nome) }}</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        {{-- LIST VIEW --}}
        @if ($viewState === 'list')
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button wire:click="novo" class="btn-primary btn-lg"><i class="fas fa-plus"></i> Novo Cliente</button>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon purple"><i class="fas fa-users"></i></div>
                <div><span class="metric-label">Total</span><span class="metric-value">{{ $this->totalGeral }}</span><span class="metric-sub">Clientes cadastrados</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon green"><i class="fas fa-circle-check"></i></div>
                <div><span class="metric-label">Ativos</span><span class="metric-value">{{ $this->totalAtivos }}</span><span class="metric-sub green">Em operação</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon amber"><i class="fas fa-circle-minus"></i></div>
                <div><span class="metric-label">Inativos</span><span class="metric-value">{{ $this->totalInativos }}</span><span class="metric-sub amber">Desativados</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon blue"><i class="fas fa-id-card"></i></div>
                <div><span class="metric-label">Com CPF</span><span class="metric-value">{{ $this->totalComCpf }}</span><span class="metric-sub">Possuem CPF</span></div>
            </div>
        </div>

        <div class="search-card">
            <div class="search-input-wrap">
                <i class="fas fa-search search-input-icon"></i>
                <input wire:model.live.debounce.300ms="busca" placeholder="Buscar por nome, CPF, email ou whatsapp..." class="search-input-field">
            </div>
        </div>

        <div class="data-table-wrap">
            <div class="data-table-header">
                <span class="data-table-title"><i class="fas fa-table-list"></i> Clientes <span class="data-table-count">({{ $this->totalGeral }})</span></span>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="data-table-th text-left">Nome</th>
                            <th class="data-table-th text-left">Email / CPF</th>
                            <th class="data-table-th text-center">WhatsApp</th>
                            <th class="data-table-th text-center">Endereços</th>
                            <th class="data-table-th text-center">Status</th>
                            <th class="data-table-th text-center" style="width:160px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lista = $this->lista(); @endphp
                        @forelse ($lista as $c)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold cursor-pointer" wire:click="selecionar({{ $c['id'] }})">{{ $c['nome'] }}</td>
                                <td class="data-table-td">{{ $c['email'] ?? '—' }}<br><span class="font-mono text-muted" style="font-size:11px;">{{ $c['cpf'] ?? '' }}</span></td>
                                <td class="data-table-td text-center font-mono">{{ $c['whatsapp'] ?? '—' }}</td>
                                <td class="data-table-td text-center">{{ $c['enderecos_count'] }}</td>
                                <td class="data-table-td text-center">
                                    <span class="badge-sm {{ $c['ativo'] ? 'badge-ativo' : 'badge-inativo' }}">{{ $c['ativo'] ? 'Ativo' : 'Inativo' }}</span>
                                </td>
                                <td class="data-table-td text-center">
                                    <div class="table-actions">
                                        <button wire:click="selecionar({{ $c['id'] }})" class="table-action-btn" style="color:var(--primary-600);" title="Abrir"><i class="fas fa-eye"></i></button>
                                        <button wire:click="desativar({{ $c['id'] }})" class="table-action-btn" style="color:{{ $c['ativo'] ? 'var(--warning)' : 'var(--success)' }};" title="{{ $c['ativo'] ? 'Desativar' : 'Ativar' }}"><i class="fas {{ $c['ativo'] ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                                        <button wire:click="excluir({{ $c['id'] }})" wire:confirm="Excluir cliente?" class="table-action-btn" style="color:var(--danger);" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="data-table-empty">Nenhum cliente encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($lista->hasPages())
                <div style="padding:12px 16px;border-top:1px solid var(--border);">
                    {{ $lista->links('livewire.pagination-custom') }}
                </div>
            @endif
        </div>
        @endif

        {{-- CREATE / DETAIL VIEW --}}
        @if ($viewState === 'create' || $viewState === 'detail')
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <button wire:click="voltarLista" class="subheader-btn"><i class="fas fa-arrow-left"></i> Voltar</button>
            <div style="width:1px;height:24px;background:var(--border);"></div>
            <div>
                <span style="font-size:16px;font-weight:900;color:var(--text);">{{ $viewState === 'create' ? 'Novo Cliente' : $this->nome }}</span>
                @if ($viewState === 'detail')
                <span class="badge-ativo-sm" style="margin-left:8px;">{{ $this->ativo ? 'Ativo' : 'Inativo' }}</span>
                @endif
            </div>
        </div>

        <div class="grid-2col-custom">
            {{-- LEFT: FORM --}}
            <div>
                <div class="form-card" x-data="{ activeTab: '{{ $viewState === 'create' ? 'principal' : 'compras' }}' }">
                    <div class="form-card-header">
                        <span class="form-card-title"><i class="fas fa-user form-card-icon"></i> {{ $viewState === 'create' ? 'Novo Cliente' : 'Dados do Cliente' }}</span>
                        <span class="form-card-subtitle">{{ $viewState === 'create' ? 'Preencha os dados do novo cliente.' : 'Gerencie todas as informações do cliente.' }}</span>
                    </div>

                    {{-- TABS --}}
                    <div class="tab-nav">
                        @if ($viewState === 'create')
                        <button @click="activeTab = 'principal'" :class="activeTab === 'principal' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-info-circle"></i> Principal</button>
                        <button @click="activeTab = 'enderecos'" :class="activeTab === 'enderecos' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-map-marker-alt"></i> Endereços</button>
                        <button @click="activeTab = 'config'" :class="activeTab === 'config' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-cog"></i> Config</button>
                        @else
                        <button @click="activeTab = 'compras'" :class="activeTab === 'compras' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-shopping-bag"></i> Compras</button>
                        <button @click="activeTab = 'financeiro'" :class="activeTab === 'financeiro' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-chart-line"></i> Financeiro</button>
                        <button @click="activeTab = 'principal'" :class="activeTab === 'principal' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-info-circle"></i> Dados</button>
                        <button @click="activeTab = 'enderecos'" :class="activeTab === 'enderecos' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-map-marker-alt"></i> Endereços</button>
                        <button @click="activeTab = 'grupos'" :class="activeTab === 'grupos' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-layer-group"></i> Grupos</button>
                        <button @click="activeTab = 'fidelidade'" :class="activeTab === 'fidelidade' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-gem"></i> Fidelidade</button>
                        <button @click="activeTab = 'config'" :class="activeTab === 'config' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-cog"></i> Config</button>
                        @endif
                    </div>

                    <form wire:submit="salvar" class="form-body">
                        {{-- PRINCIPAL --}}
                        <div x-show="activeTab === 'principal'">
                            <div class="section-border">Dados do Cliente</div>
                            <div class="grid-2" style="margin-bottom:12px;">
                                <div class="field"><label>Nome *</label><input wire:model="nome" placeholder="Nome completo">@error('nome')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field"><label>Email</label><input wire:model="email" type="email" placeholder="cliente@email.com">@error('email')<div class="err">{{ $message }}</div>@enderror</div>
                            </div>
                            <div class="grid-3" style="margin-bottom:12px;">
                                <div class="field"><label>CPF</label><input wire:model.live="cpf" placeholder="000.000.000-00" x-data x-mask="999.999.999-99">@error('cpf')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field"><label>WhatsApp</label><input wire:model="whatsapp" placeholder="(66) 99999-9999" x-data x-mask="(99) 99999-9999">@error('whatsapp')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field"><label>Data Nascimento</label><input wire:model="data_nascimento" type="date"></div>
                            </div>
                        </div>

                        {{-- ENDERECOS --}}
                        <div x-show="activeTab === 'enderecos'">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                                <span class="section-border" style="margin-bottom:0;">Endereços</span>
                                <button type="button" wire:click="adicionarEndereco" class="btn-sm btn-secondary">+ Adicionar</button>
                            </div>
                            @forelse ($this->enderecos as $idx => $end)
                                <div class="sub-card" style="margin-bottom:10px;position:relative;" x-data="{
                                    cep{{ $idx }}: '{{ preg_replace('/\D/', '', $end['cep'] ?? '') }}',
                                    cepErro{{ $idx }}: '', buscando{{ $idx }}: false,
                                    onCepInput(e) {
                                        let raw = e.target.value.replace(/\D/g, '').substring(0, 8);
                                        let f = '';
                                        for (let i = 0; i < raw.length; i++) { f += raw[i]; if (i === 4 && raw.length > 5) f += '-'; }
                                        e.target.value = f;
                                        this.cep{{ $idx }} = raw;
                                        this.$wire.set('enderecos.{{ $idx }}.cep', f);
                                        this.cepErro{{ $idx }} = '';
                                        if (raw.length === 8) this.buscarCep(raw);
                                    },
                                    buscarCep(cep) {
                                        this.buscando{{ $idx }} = true; this.cepErro{{ $idx }} = '';
                                        let wire = this.$wire; let el = this.$el; let idx = {{ $idx }};
                                        fetch('https://viacep.com.br/ws/' + cep + '/json/')
                                            .then(r => r.json()).then(data => {
                                                if (data.erro) { this.cepErro{{ $idx }} = 'CEP não encontrado.'; return; }
                                                wire.set('enderecos.' + idx + '.logradouro', data.logradouro || '');
                                                wire.set('enderecos.' + idx + '.bairro', data.bairro || '');
                                                if (data.uf) {
                                                    let sel = el.querySelector('.estado-select-' + idx);
                                                    if (sel) { for (let i = 0; i < sel.options.length; i++) {
                                                        if (sel.options[i].text.startsWith(data.uf)) {
                                                            sel.value = sel.options[i].value;
                                                            sel.dispatchEvent(new Event('change', { bubbles: true }));
                                                            break;
                                                        }
                                                    }}
                                                }
                                            }).catch(() => { this.cepErro{{ $idx }} = 'Erro ao buscar CEP.'; })
                                            .finally(() => { this.buscando{{ $idx }} = false; });
                                    }
                                }">
                                    <div class="grid-4" style="gap:6px;margin-bottom:6px;">
                                        <div class="field"><label>Título</label><input wire:model="enderecos.{{ $idx }}.titulo" placeholder="Principal"></div>
                                        <div class="field" style="grid-column:span 3;"><label>Logradouro</label><input wire:model="enderecos.{{ $idx }}.logradouro" placeholder="Rua..."></div>
                                    </div>
                                    <div class="grid-4" style="gap:6px;margin-bottom:6px;">
                                        <div class="field"><label>Número</label><input wire:model="enderecos.{{ $idx }}.numero" placeholder="S/N"></div>
                                        <div class="field"><label>Bairro</label><input wire:model="enderecos.{{ $idx }}.bairro" placeholder="Centro"></div>
                                        <div class="field"><label>CEP</label>
                                            <input x-init="if (cep{{ $idx }}) { let v = cep{{ $idx }}; $el.value = v.substring(0,5) + '-' + v.substring(5); }" placeholder="00000-000" maxlength="9" @input.debounce.200ms="onCepInput($event)">
                                            <div x-show="cepErro{{ $idx }}" style="color:var(--danger);font-size:11px;margin-top:2px;" x-text="cepErro{{ $idx }}"></div>
                                            <div x-show="buscando{{ $idx }}" style="font-size:11px;color:var(--muted);margin-top:2px;"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>
                                        </div>
                                        <div class="field"><label>Complemento</label><input wire:model="enderecos.{{ $idx }}.complemento" placeholder="Galpão..."></div>
                                    </div>
                                    <div class="grid-4" style="gap:6px;">
                                        <div class="field"><label>Estado</label>
                                            <select wire:model.live="enderecos.{{ $idx }}.estado_id" class="estado-select-{{ $idx }}">
                                                <option value="">Selecione...</option>
                                                @foreach ($this->estados as $e)
                                                    <option value="{{ $e['id'] }}">{{ $e['uf'] }} - {{ $e['nome'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field" style="grid-column:span 2;"><label>Cidade</label>
                                            <select wire:model="enderecos.{{ $idx }}.cidade_id">
                                                <option value="">Selecione...</option>
                                                @if ($end['estado_id'])
                                                    @foreach (\App\Models\Cidade::where('estado_id', (int)$end['estado_id'])->orderBy('nome')->get(['id','nome']) as $cidade)
                                                        <option value="{{ $cidade->id }}">{{ $cidade->nome }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="field" style="display:flex;align-items:end;padding-bottom:4px;">
                                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:var(--text);margin-bottom:0;"><input wire:model="enderecos.{{ $idx }}.principal" type="checkbox" style="width:16px;height:16px;"> Principal</label>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="removerEndereco({{ $idx }})" class="apres-remove">&times;</button>
                                </div>
                            @empty
                                <div class="sub-card" style="text-align:center;padding:24px;color:var(--muted);font-size:13px;">
                                    <i class="fas fa-map-marker-alt" style="font-size:24px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                                    Nenhum endereço cadastrado.
                                </div>
                            @endforelse
                        </div>

                        {{-- COMPRAS --}}
                        <div x-show="activeTab === 'compras'">
                            <div class="section-border">Histórico de Compras</div>
                            @php $compras = $this->comprasCliente; @endphp
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                                <div class="sub-card">
                                    <div class="section-title" style="margin-bottom:8px;">PDV (Balcão)</div>
                                    @forelse ($compras['pdv'] as $v)
                                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                                            <span style="font-weight:600;">#{{ $v['id'] }}</span>
                                            <span style="color:var(--muted);">{{ \Carbon\Carbon::parse($v['created_at'])->format('d/m/Y') }}</span>
                                            <span style="font-weight:700;">R$ {{ number_format($v['total'], 2, ',', '.') }}</span>
                                            <span class="badge-sm {{ $v['status'] === 'concluida' ? 'badge-ativo' : 'badge-inativo' }}">{{ $v['status'] }}</span>
                                        </div>
                                    @empty
                                        <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhuma venda.</div>
                                    @endforelse
                                </div>
                                <div class="sub-card">
                                    <div class="section-title" style="margin-bottom:8px;">Delivery</div>
                                    @forelse ($compras['pedidos'] as $p)
                                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                                            <span style="font-weight:600;">{{ $p['codigo'] }}</span>
                                            <span style="color:var(--muted);">{{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}</span>
                                            <span style="font-weight:700;">R$ {{ number_format($p['total'], 2, ',', '.') }}</span>
                                            <span class="badge-sm {{ $p['status'] === 'entregue' ? 'badge-ativo' : 'badge-warning' }}">{{ $p['status'] }}</span>
                                        </div>
                                    @empty
                                        <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum pedido.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- FINANCEIRO --}}
                        <div x-show="activeTab === 'financeiro'">
                            <div class="section-border">Financeiro</div>
                            <div class="sub-card">
                                @forelse ($this->financeiroCliente as $l)
                                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                                        <span style="font-weight:600;">{{ $l['descricao'] }}</span>
                                        <span style="color:var(--muted);">{{ \Carbon\Carbon::parse($l['data_vencimento'])->format('d/m/Y') }}</span>
                                        <span style="font-weight:700;color:{{ $l['tipo'] === 'receita' ? 'var(--success)' : 'var(--danger)' }};">R$ {{ number_format($l['valor'], 2, ',', '.') }}</span>
                                        <span class="badge-sm {{ $l['status'] === 'pago' ? 'badge-ativo' : ($l['status'] === 'atrasado' ? 'badge-inativo' : 'badge-warning') }}">{{ $l['status'] }}</span>
                                    </div>
                                @empty
                                    <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum lançamento financeiro.</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- GRUPOS --}}
                        <div x-show="activeTab === 'grupos'">
                            <div class="section-border">Grupos do Cliente</div>
                            <div class="sub-card">
                                @forelse ($this->gruposDisponiveis as $g)
                                    <label style="display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;cursor:pointer;color:var(--text);height:40px;padding:0 4px;border-bottom:1px solid var(--border);">
                                        <input wire:model="gruposSelecionados" type="checkbox" value="{{ $g['id'] }}" style="width:18px;height:18px;">
                                        {{ $g['nome'] }}
                                    </label>
                                @empty
                                    <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum grupo cadastrado.</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- FIDELIDADE --}}
                        <div x-show="activeTab === 'fidelidade'">
                            <div class="section-border">Programa de Fidelidade</div>
                            @php $pts = $this->pontosCliente; @endphp
                            <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
                                <div class="sub-card" style="text-align:center;">
                                    <div style="font-size:40px;font-weight:900;color:var(--primary-600);">{{ $pts['total'] }}</div>
                                    <div style="font-size:12px;color:var(--muted);font-weight:600;margin-top:4px;">Pontos Ativos</div>
                                </div>
                                <div class="sub-card" style="max-height:300px;overflow-y:auto;">
                                    @forelse ($pts['historico'] as $m)
                                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                                            <span>{{ $m['descricao'] ?? $m['tipo'] }}</span>
                                            <span style="color:var(--muted);">{{ \Carbon\Carbon::parse($m['created_at'])->format('d/m/Y') }}</span>
                                            <span style="font-weight:700;color:{{ $m['tipo'] === 'credito' ? 'var(--success)' : 'var(--danger)' }};">{{ $m['tipo'] === 'credito' ? '+' : '-' }}{{ $m['pontos'] }}</span>
                                        </div>
                                    @empty
                                        <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhuma movimentação.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- CONFIG --}}
                        <div x-show="activeTab === 'config'">
                            <div class="section-border">Configurações</div>
                            <div class="sub-card">
                                <div style="display:flex;flex-direction:column;gap:12px;">
                                    <label style="display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;cursor:pointer;color:var(--text);height:36px;"><input wire:model="ativo" type="checkbox" checked style="width:18px;height:18px;"> Cliente ativo</label>
                                    <label style="display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;cursor:pointer;color:var(--text);height:36px;"><input wire:model="aceita_marketing" type="checkbox" style="width:18px;height:18px;"> Aceita marketing</label>
                                </div>
                            </div>
                        </div>

                        {{-- BOTOES --}}
                        @error('exclusao')<div class="err" style="margin-bottom:8px;">{{ $message }}</div>@enderror
                        <div class="form-actions">
                            <button type="submit" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-save"></i> {{ $this->modo === 'create' ? 'Salvar' : 'Atualizar' }}</span><span wire:loading>Salvando...</span></button>
                            <button type="button" wire:click="voltarLista" class="btn-secondary btn-lg">Cancelar</button>
                            @if ($viewState === 'detail')
                                <button type="button" wire:click="excluir({{ $this->editandoId }})" wire:confirm="Excluir cliente?" class="btn-danger btn-lg" style="margin-left:auto;">Excluir</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT: SIDEBAR (só no detail) --}}
            @if ($viewState === 'detail')
            <div style="display:flex;flex-direction:column;gap:16px;">
                @php $resumo = $this->resumoCliente; @endphp
                @if ($resumo)
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-chart-bar"></i> Métricas</div>
                    <div class="sidebar-card-body">
                        <div class="resumo-list">
                            <div class="resumo-row">
                                <span class="resumo-label">Classificação</span>
                                <span class="resumo-badge" style="text-transform:uppercase;font-weight:700;{{ $resumo['classificacao'] === 'top' ? 'background:#fef3c7;color:#d97706;' : ($resumo['classificacao'] === 'medio' ? 'background:#dbeafe;color:#2563eb;' : ($resumo['classificacao'] === 'ocasional' ? 'background:#f1f5f9;color:#64748b;' : 'background:#e0f2fe;color:#0284c7;')) }}">{{ $resumo['classificacao'] }}</span>
                            </div>
                            <div class="resumo-row"><span class="resumo-label">Total Gasto</span><span class="resumo-value" style="font-weight:800;">R$ {{ number_format($resumo['total_gasto'], 2, ',', '.') }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Ticket Médio</span><span class="resumo-value">R$ {{ number_format($resumo['ticket_medio'], 2, ',', '.') }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Gasto Mensal</span><span class="resumo-value">R$ {{ number_format($resumo['gasto_mensal'], 2, ',', '.') }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Frequência</span><span class="resumo-value">{{ number_format($resumo['frequencia'], 1, ',', '.') }} /mês</span></div>
                            <div class="resumo-row"><span class="resumo-label">Total Compras</span><span class="resumo-value">{{ $resumo['total_compras'] }}</span></div>
                            @if ($resumo['dias_ultima_compra'] !== null)
                            <div class="resumo-row"><span class="resumo-label">Última Compra</span><span class="resumo-value" style="{{ $resumo['dias_ultima_compra'] > 30 ? 'color:var(--danger);' : '' }}">{{ $resumo['dias_ultima_compra'] }} dias</span></div>
                            @endif
                        </div>
                        @if (!empty($resumo['favoritos']))
                            <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);">
                                <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;">Produtos favoritos</span>
                                @foreach ($resumo['favoritos'] as $f)
                                    <div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;">
                                        <span style="color:var(--text);text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;">{{ $f['nome'] }}</span>
                                        <span style="color:var(--muted);font-weight:600;">{{ number_format($f['qtd'], 0, ',', '.') }}x</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @endif
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-receipt"></i> Resumo</div>
                    <div class="sidebar-card-body">
                        <div class="resumo-list">
                            <div class="resumo-row"><span class="resumo-label">Nome</span><span class="resumo-value" style="font-weight:800;">{{ $this->nome ?: '—' }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Email</span><span class="resumo-value">{{ $this->email ?: '—' }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">CPF</span><span class="resumo-value-mono">{{ $this->cpf ?: '—' }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">WhatsApp</span><span class="resumo-value">{{ $this->whatsapp ?: '—' }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Endereços</span><span class="resumo-value">{{ count($this->enderecos) }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Grupos</span><span class="resumo-value">{{ count($this->gruposSelecionados) }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Status</span><span class="resumo-badge {{ $this->ativo ? 'resumo-badge-ativo' : 'resumo-badge-inativo' }}">{{ $this->ativo ? 'Ativo' : 'Inativo' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

    </div>
</div>
