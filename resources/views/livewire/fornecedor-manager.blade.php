<div x-data="{ tab: 'ident' }">
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
            <div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Fornecedores</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Fornecedores</span></div></div>
            <div style="display:flex;gap:8px;align-items:center;">
                <div style="position:relative;width:260px;">
                    <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px;"></i>
                    <input wire:model.live.debounce.300ms="busca" placeholder="Buscar fornecedor..." style="width:100%;height:40px;padding:0 12px 0 36px;border:1px solid var(--border);border-radius:10px;font-size:13px;background:var(--background);color:var(--text);outline:none;">
                </div>
                <button wire:click="novo" class="btn-primary btn-sm" style="height:40px;padding:0 16px;"><i class="fas fa-plus"></i> Novo</button>
            </div>
        </div>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon purple" style="width:44px;height:44px;font-size:18px;"><i class="fas fa-building"></i></div>
                <div><span class="metric-label">Fornecedores Ativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalAtivos }}</span><span class="metric-sub green" style="font-size:10px;"><i class="fas fa-arrow-up"></i> {{ $this->totalGeral }} total</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon green" style="width:44px;height:44px;font-size:18px;"><i class="fas fa-shopping-basket"></i></div>
                <div><span class="metric-label">Compras (Mês)</span><span class="metric-value" style="font-size:20px;">R$ 0,00</span><span class="metric-sub" style="font-size:10px;">Inicie compras para ver</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon amber" style="width:44px;height:44px;font-size:18px;"><i class="fas fa-wallet"></i></div>
                <div><span class="metric-label">Valor em Aberto</span><span class="metric-value" style="font-size:20px;">R$ 0,00</span><span class="metric-sub" style="font-size:10px;">Nenhum pendente</span></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon" style="width:44px;height:44px;border-radius:12px;font-size:18px;background:color-mix(in srgb,#f59e0b 12%,transparent);color:#f59e0b;"><i class="fas fa-star"></i></div>
                <div><span class="metric-label">Fornecedores</span><span class="metric-value" style="font-size:20px;">{{ $this->totalGeral }}</span><span class="metric-sub" style="font-size:10px;">Cadastrados</span></div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:380px 1fr;gap:20px;align-items:start;">
            <div class="sub-card" style="padding:0;overflow:hidden;">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid var(--border);"><span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;">Fornecedores ({{ $this->totalGeral }})</span></div>
                <div style="max-height:600px;overflow-y:auto;">
                    @forelse ($this->lista as $f)
                        <div wire:click="selecionar({{ $f['id'] }})"
                             style="padding:12px 16px;border-bottom:1px solid var(--border);cursor:pointer;display:flex;align-items:center;gap:12px;{{ $this->editandoId === $f['id'] ? 'background:color-mix(in srgb, #6366f1 6%, var(--surface));' : '' }}"
                             onmouseover="this.style.background='color-mix(in srgb, var(--text) 3%, var(--surface))'"
                             onmouseout="this.style.background='{{ $this->editandoId === $f['id'] ? 'color-mix(in srgb, #6366f1 6%, var(--surface))' : 'transparent' }}'">
                            <div style="width:36px;height:36px;border-radius:50%;background:{{ $f['ativo'] ? '#6366f1' : '#94a3b8' }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:13px;flex-shrink:0;">{{ substr($f['razao_social'], 0, 2) }}</div>
                            <div style="flex:1;min-width:0;"><div style="font-weight:700;font-size:13px;color:var(--text);text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $f['razao_social'] }}</div><div style="font-size:10px;color:var(--muted);margin-top:1px;">{{ $f['cnpj_cpf'] ?? '' }}</div>@if ($f['cidade'] ?? false)<div style="font-size:9px;color:var(--muted);margin-top:1px;">{{ $f['cidade']['nome'] ?? '' }}/{{ $f['cidade']['estado']['uf'] ?? '' }}</div>@endif</div>
                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">@if (count($f['contatos'] ?? []) > 0)<span style="font-size:9px;color:var(--primary-600);font-weight:600;">{{ count($f['contatos']) }} contato(s)</span>@endif<span style="font-size:9px;font-weight:700;padding:2px 8px;border-radius:999px;{{ $f['ativo'] ? 'background:color-mix(in srgb, var(--success) 12%, transparent);color:var(--success);' : 'background:color-mix(in srgb, var(--danger) 12%, transparent);color:var(--danger);' }}">{{ $f['ativo'] ? 'Ativo' : 'Inativo' }}</span></div>
                        </div>
                    @empty
                        <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px;"><i class="fas fa-truck" style="font-size:40px;display:block;margin-bottom:12px;opacity:0.3;"></i>Nenhum fornecedor encontrado.</div>
                    @endforelse
                </div>
            </div>
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas fa-file-contract form-card-icon"></i> {{ $this->modo === 'create' ? 'Novo Fornecedor' : 'Editar Fornecedor' }}</span><span class="form-card-subtitle" style="font-size:10px;">Identificação Fiscal e Cadastral</span>@if ($this->modo === 'edit')<button wire:click="novo" class="btn-sm btn-secondary" style="margin-left:auto;">Cancelar</button>@endif</div>
                <div class="tab-nav">
                    <button type="button" @click="tab = 'ident'" :class="tab === 'ident' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-id-card"></i> Identificação</button>
                    <button type="button" @click="tab = 'end'" :class="tab === 'end' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-map-location"></i> Endereço</button>
                    <button type="button" @click="tab = 'cont'" :class="tab === 'cont' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-address-book"></i> Contatos</button>
                </div>
                <form wire:submit="salvar">
                    <div x-show="tab === 'ident'" style="padding:16px 18px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                            <div class="field"><label>Razão Social *</label><input wire:model.blur="razao_social" placeholder="Razão social">@error('razao_social')<div class="err">{{ $message }}</div>@enderror</div>
                            <div class="field"><label>Nome Fantasia</label><input wire:model.blur="nome_fantasia" placeholder="Nome fantasia"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 2fr 1fr;gap:10px;margin-bottom:10px;">
                            <div class="field"><label>Tipo *</label><select wire:model.live="tipo_pessoa"><option value="juridica">Jurídica</option><option value="fisica">Física</option></select></div>
                            <div class="field"><label>{{ $this->tipo_pessoa === 'juridica' ? 'CNPJ *' : 'CPF *' }}</label><input wire:model="{{ $this->tipo_pessoa === 'juridica' ? 'cnpj' : 'cpf' }}" placeholder="{{ $this->tipo_pessoa === 'juridica' ? '00.000.000/0000-00' : '000.000.000-00' }}" x-data x-mask="{{ $this->tipo_pessoa === 'juridica' ? '99.999.999/9999-99' : '999.999.999-99' }}">@error('cnpj')<div class="err">{{ $message }}</div>@enderror @error('cpf')<div class="err">{{ $message }}</div>@enderror</div>
                            <div class="field"><label>IE</label><input wire:model="inscricao_estadual" placeholder="IE"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                            <div class="field"><label><i class="fa-brands fa-whatsapp" style="color:var(--success);"></i> WhatsApp</label><input wire:model="telefone_celular" placeholder="(66) 99999-9999" x-data x-mask="(99) 99999-9999"></div>
                            <div class="field"><label><i class="fas fa-phone"></i> Telefone Fixo</label><input wire:model="telefone_fixo" placeholder="(66) 9999-9999" x-data x-mask="(99) 9999-9999"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <div class="field"><label><i class="fas fa-envelope"></i> Email</label><input wire:model="email" type="email" placeholder="fornecedor@exemplo.com"></div>
                            <div class="field"><label><i class="fas fa-toggle-on"></i> Situação</label><select wire:model="ativo"><option value="1">Ativo</option><option value="0">Inativo</option></select></div>
                        </div>
                    </div>
                    <div x-show="tab === 'end'" style="padding:16px 18px;display:none;">
                        <div style="display:grid;grid-template-columns:120px 1fr;gap:10px;margin-bottom:10px;"><div class="field"><label>CEP</label><input wire:model="cep" placeholder="99999-999" x-data x-mask="99999-999"></div><div class="field"><label>Logradouro</label><input wire:model="logradouro" placeholder="Rua, Avenida..."></div></div>
                        <div style="display:grid;grid-template-columns:100px 1fr 1fr;gap:10px;margin-bottom:10px;"><div class="field"><label>Número</label><input wire:model="numero" placeholder="S/N"></div><div class="field"><label>Bairro</label><input wire:model="bairro" placeholder="Centro"></div><div class="field"><label>Complemento</label><input wire:model="complemento" placeholder="Galpão..."></div></div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><div class="field"><label>Estado</label><select wire:model.live="estado_id"><option value="">Selecione...</option>@foreach ($this->estados as $e)<option value="{{ $e['id'] }}">{{ $e['uf'] }} - {{ $e['nome'] }}</option>@endforeach</select></div><div class="field"><label>Cidade</label><select wire:model="cidade_id"><option value="">Selecione...</option>@foreach ($this->getCidadesPorEstado($this->estado_id) as $c)<option value="{{ $c['id'] }}">{{ $c['nome_completo'] }}</option>@endforeach</select>@error('cidade_id')<div class="err">{{ $message }}</div>@enderror</div></div>
                    </div>
                    <div x-show="tab === 'cont'" style="padding:16px 18px;display:none;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;"><span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;">Contatos ({{ count($this->contatos) }})</span><button type="button" wire:click="adicionarContato" class="btn-sm btn-secondary"><i class="fas fa-plus"></i> Adicionar</button></div>
                        @forelse ($this->contatos as $idx => $ct)
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 30px;gap:6px;margin-bottom:6px;align-items:end;">
                                <div class="field"><label>Nome</label><input wire:model="contatos.{{ $idx }}.nome" placeholder="Nome"></div>
                                <div class="field"><label>Cargo</label><input wire:model="contatos.{{ $idx }}.cargo" placeholder="Cargo"></div>
                                <div class="field"><label>Telefone</label><input wire:model="contatos.{{ $idx }}.telefone" placeholder="Telefone"></div>
                                <div class="field"><label>Email</label><input wire:model="contatos.{{ $idx }}.email" placeholder="Email"></div>
                                <button type="button" wire:click="removerContato({{ $idx }})" class="btn-remove" style="margin-bottom:2px;">&times;</button>
                            </div>
                        @empty
                            <div style="text-align:center;padding:24px;color:var(--muted);font-size:12px;"><i class="fas fa-address-book" style="font-size:32px;display:block;margin-bottom:8px;opacity:0.3;"></i>Nenhum contato adicionado.</div>
                        @endforelse
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-top:1px solid var(--border);">
                        <div style="display:flex;gap:8px;">@if ($this->modo === 'edit')<button type="button" wire:click="excluir({{ $this->editandoId }})" wire:confirm="Excluir fornecedor?" class="btn-danger btn-sm">Excluir</button>@endif</div>
                        <div style="display:flex;gap:8px;"><button type="button" wire:click="novo" class="btn-secondary btn-sm">Cancelar</button><button type="submit" class="btn-primary btn-sm"><span wire:loading.remove>{{ $this->modo === 'create' ? 'Salvar Fornecedor' : 'Atualizar' }}</span><span wire:loading>Salvando...</span></button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
