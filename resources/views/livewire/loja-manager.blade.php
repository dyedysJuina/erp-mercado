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
        <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Lojas</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Lojas</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
        <div class="metrics-grid" style="grid-template-columns:repeat(5,1fr);">
            <div class="metric-card"><div class="metric-icon purple" style="width:42px;height:42px;font-size:18px;"><i class="fas fa-store"></i></div><div><span class="metric-label">Total</span><span class="metric-value" style="font-size:20px;">{{ $this->totalGeral }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green" style="width:42px;height:42px;font-size:18px;"><i class="fas fa-circle-check"></i></div><div><span class="metric-label">Ativas</span><span class="metric-value" style="font-size:20px;">{{ $this->totalAtivas }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber" style="width:42px;height:42px;font-size:18px;"><i class="fas fa-circle-minus"></i></div><div><span class="metric-label">Inativas</span><span class="metric-value" style="font-size:20px;">{{ $this->totalInativas }}</span></div></div>
            <div class="metric-card"><div class="metric-icon blue" style="width:42px;height:42px;font-size:18px;"><i class="fas fa-flag"></i></div><div><span class="metric-label">Matriz</span><span class="metric-value" style="font-size:20px;">{{ $this->totalMatriz }}</span></div></div>
            <div class="metric-card"><div class="metric-icon" style="width:42px;height:42px;border-radius:10px;font-size:18px;background:color-mix(in srgb,#f59e0b 12%,transparent);color:#f59e0b;"><i class="fas fa-code-branch"></i></div><div><span class="metric-label">Filiais</span><span class="metric-value" style="font-size:20px;">{{ $this->totalFilial }}</span></div></div>
        </div>
        <div class="grid-2col">
            <div class="col-lista">
                <div class="col-header"><span><i class="fas fa-list"></i> Lojas ({{ $this->totalGeral }})</span><button wire:click="novo" class="btn-sm btn-primary">+ Nova</button></div>
                <input wire:model.live.debounce.150ms="busca" placeholder="Buscar loja..." style="margin:10px 12px;width:calc(100% - 24px);height:36px;border:1px solid var(--border);border-radius:8px;font-size:13px;padding:0 12px;">
                <div class="lista-vars">
                    @forelse ($this->lista() as $l)
                        <div class="var-item {{ $this->editandoId === $l['id'] ? 'var-ativa' : '' }}" wire:click="selecionar({{ $l['id'] }})">
                            <div class="var-nome">{{ $l['nome'] }}</div>
                            <div class="var-info">{{ $l['cidade']['nome'] ?? '' }}/{{ $l['cidade']['estado']['uf'] ?? '' }} &middot; {{ $l['tipo'] }}</div>
                            @if ($l['cnpj'])<div class="var-info">CNPJ: {{ $l['cnpj'] }}</div>@endif
                            @if ($l['telefone'])<div class="var-info">Tel: {{ $l['telefone'] }}</div>@endif
                            <div class="var-actions"><span class="badge-sm {{ $l['ativo'] ? 'badge-ativo' : 'badge-inativo' }}">{{ $l['ativo'] ? 'Ativa' : 'Inativa' }}</span><span class="badge-sm badge-warning">{{ $l['status_operacional'] }}</span></div>
                        </div>
                    @empty
                        <div class="vazio">Nenhuma loja encontrada.</div>
                    @endforelse
                </div>
            </div>
            <div class="col-form">
                <div class="col-header"><span><i class="fas {{ $this->modo === 'create' ? 'fa-plus' : 'fa-edit' }}"></i> {{ $this->modo === 'create' ? 'Nova Loja' : 'Editando Loja' }}</span>@if ($this->modo === 'edit')<button wire:click="novo" class="btn-sm btn-secondary">Cancelar</button>@endif</div>
                <form wire:submit="salvar">
                    <div style="padding:14px 16px;border-bottom:1px solid var(--border);">
                        <div class="grid-2" style="margin-bottom:6px;"><div class="field"><label>Nome *</label><input wire:model.blur="nome" placeholder="Razão Social">@error('nome')<div class="err">{{ $message }}</div>@enderror</div><div class="field"><label>Nome Fantasia</label><input wire:model="nome_fantasia" placeholder="Nome fantasia"></div></div>
                        <div class="grid-3" style="margin-bottom:6px;"><div class="field"><label>CNPJ</label><input wire:model="cnpj" placeholder="99.999.999/9999-99" x-data x-mask="99.999.999/9999-99"></div><div class="field"><label>IE</label><input wire:model="inscricao_estadual" placeholder="IE"></div><div class="field"><label>Código Interno</label><input wire:model="codigo_interno" placeholder="Código curto"></div></div>
                        <div class="grid-2" style="margin-bottom:6px;"><div class="field"><label>Tipo *</label><select wire:model="tipo"><option value="matriz">Matriz</option><option value="filial">Filial</option></select>@error('tipo')<div class="err">{{ $message }}</div>@enderror</div><div class="field"><label>Telefone</label><input wire:model="telefone" placeholder="(66) 99999-9999" x-data x-mask="(99) 99999-9999"></div></div>
                        <div class="field"><label>Email</label><input wire:model="email" type="email" placeholder="loja@email.com"></div>
                    </div>
                    <div style="padding:14px 16px;border-bottom:1px solid var(--border);" x-data="{ endOpen: true }">
                        <div class="section-title" @click="endOpen = !endOpen" style="cursor:pointer;">Endereço <span x-text="endOpen ? '▲' : '▼'" style="font-size:10px;"></span></div>
                        <div x-show="endOpen" x-collapse>
                            <div class="grid-3" style="margin-top:6px;">
                                <div class="field"><label>CEP</label><input wire:model="cep" placeholder="99999-999" x-data x-mask="99999-999"></div>
                                <div class="field" style="grid-column:span 2;"><label>Logradouro</label><input wire:model="logradouro" placeholder="Rua..."></div>
                                <div class="field"><label>Número</label><input wire:model="numero" placeholder="S/N"></div>
                                <div class="field"><label>Bairro</label><input wire:model="bairro" placeholder="Centro"></div>
                                <div class="field"><label>Estado</label><select wire:model.live="estado_id"><option value="">Selecione...</option>@foreach ($this->estados as $e)<option value="{{ $e['id'] }}">{{ $e['uf'] }} - {{ $e['nome'] }}</option>@endforeach</select></div>
                                <div class="field"><label>Cidade *</label><select wire:model="cidade_id"><option value="">Selecione...</option>@foreach ($this->cidadesPorEstado() as $c)<option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>@endforeach</select>@error('cidade_id')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field"><label>Complemento</label><input wire:model="complemento" placeholder="Galpão..."></div>
                            </div>
                        </div>
                    </div>
                    <div style="padding:14px 16px;border-bottom:1px solid var(--border);">
                        <div class="grid-3"><div class="field"><label>Status *</label><select wire:model="status_operacional"><option value="aberta">Aberta</option><option value="fechada">Fechada</option><option value="manutencao">Manutenção</option></select></div><div class="field check-group"><label style="height:36px;"><input wire:model="ativo" type="checkbox" checked> Ativa</label></div><div class="field"><label>Tabela de Preços</label><select wire:model="tabela_preco_id"><option value="">Nenhuma</option>@foreach ($this->tabelasPreco as $t)<option value="{{ $t['id'] }}">{{ $t['nome'] }}</option>@endforeach</select></div></div>
                    </div>
                    <div style="padding:14px 16px;display:flex;align-items:center;">
                        <button type="submit" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-save"></i> {{ $this->modo === 'create' ? 'Salvar Loja' : 'Atualizar' }}</span><span wire:loading>Salvando...</span></button>
                        <button type="button" wire:click="novo" class="btn-secondary btn-lg" style="margin-left:8px;">Limpar</button>
                        @if ($this->modo === 'edit')<button type="button" wire:click="excluir({{ $this->editandoId }})" wire:confirm="Excluir loja?" class="btn-danger btn-lg" style="margin-left:auto;">Excluir</button>@endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
