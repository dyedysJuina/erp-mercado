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
            <div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Atributos</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Cadastros</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Atributos</span></div></div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>
        {{-- MÉTRICAS --}}
        <div class="metrics-grid" style="grid-template-columns:repeat(5,1fr);">
            <div class="metric-card"><div class="metric-icon purple"><i class="fas fa-cubes"></i></div><div><span class="metric-label">Total</span><span class="metric-value" style="font-size:20px;">{{ $this->totalGeral }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green"><i class="fas fa-circle-check"></i></div><div><span class="metric-label">Ativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalAtivos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber"><i class="fas fa-circle-minus"></i></div><div><span class="metric-label">Inativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalInativos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon blue"><i class="fas fa-gears"></i></div><div><span class="metric-label">Tipos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalTipos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon" style="background:color-mix(in srgb,#f59e0b 12%,transparent);color:#f59e0b;"><i class="fas fa-tags"></i></div><div><span class="metric-label">Vinculados</span><span class="metric-value" style="font-size:20px;">{{ $this->totalVinculados }}</span></div></div>
        </div>
        {{-- GRID: FORM + INFO --}}
        <div class="grid-2col-custom" style="margin-bottom:20px;">
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas {{ $this->editandoId ? 'fa-edit' : 'fa-plus' }}" style="color:var(--primary-600);"></i> {{ $this->editandoId ? 'Editando Atributo' : 'Novo Atributo' }}</span><span class="form-card-subtitle">{{ $this->editandoId ? 'Altere os dados do atributo.' : 'Cadastre atributos para variações.' }}</span>@if ($this->editandoId)<button wire:click="resetForm" class="btn-sm btn-secondary" style="margin-left:auto;">Cancelar</button>@endif</div>
                <form wire:submit="cadastrar" class="form-body">
                    <div class="grid-2" style="margin-bottom:12px;">
                        <div class="field"><label>Nome *</label><input wire:model.live="nome" placeholder="Ex: Cor, Tamanho, Material">@error('nome')<div class="err">{{ $message }}</div>@enderror@if ($this->duplicata)<div style="color:var(--warning);font-size:12px;margin-top:4px;">Já existe um atributo com este nome.</div>@endif</div>
                        <div class="field"><label>Slug</label><input wire:model="slug" placeholder="Gerado automaticamente" readonly style="background:color-mix(in srgb,var(--text)3%,var(--surface));color:var(--muted);"></div>
                    </div>
                    <div class="grid-2" style="margin-bottom:12px;">
                        <div class="field"><label>Tipo *</label><select wire:model="tipo">@foreach ($this->tipos as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach</select></div>
                        <div class="field"><label>Unidade de Medida</label><select wire:model="unidade_medida_id"><option value="">Nenhuma</option>@foreach ($this->listaUnidades as $u)<option value="{{ $u['id'] }}">{{ $u['label'] }}</option>@endforeach</select></div>
                    </div>
                    @if (in_array($this->tipo, ['lista', 'checkbox', 'radio']))
                        <div class="field" style="margin-bottom:12px;"><label>Opções (separadas por vírgula)</label><input wire:model="opcoes" placeholder="Ex: Vermelho, Azul, Verde, Preto"><div style="font-size:11px;color:var(--muted);margin-top:4px;">@if ($this->tipo === 'checkbox') O cliente poderá selecionar várias opções de uma vez.@elseif ($this->tipo === 'radio') O cliente escolherá apenas uma opção.@else O cliente selecionará uma opção em uma lista.@endif</div></div>
                    @endif
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;color:var(--text);"><input wire:model="ativo" type="checkbox" style="width:18px;height:18px;"> Atributo ativo</label></div>
                    <div class="form-actions" style="margin-top:0;"><button type="submit" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-save"></i> {{ $this->editandoId ? 'Atualizar' : 'Cadastrar' }}</span><span wire:loading>Salvando...</span></button></div>
                </form>
            </div>
            <div class="sidebar-card"><div class="sidebar-card-header"><i class="fas fa-circle-info"></i> Sobre Atributos</div><div class="sidebar-card-body" style="font-size:13px;color:var(--muted);line-height:1.6;"><p style="margin-bottom:12px;">Atributos são características usadas para criar variações de produtos.</p><div style="font-weight:700;color:var(--text);margin-bottom:8px;">Dicas:</div><ul style="list-style:none;padding:0;"><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Use nomes claros</li><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> O slug é gerado automaticamente</li><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Escolha o tipo correto</li></ul></div></div>
        </div>
        {{-- TABELA --}}
        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-list"></i> Atributos ({{ $this->totalGeral }})</span><input wire:model.live.debounce.300ms="busca" placeholder="Buscar atributo..." class="data-table-filter"></div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th class="data-table-th text-left">Atributo</th><th class="data-table-th text-left">Slug</th><th class="data-table-th text-center">Tipo</th><th class="data-table-th text-center">Unidade</th><th class="data-table-th text-center">Status</th><th class="data-table-th text-center" style="width:90px;">Ações</th></tr></thead>
                    <tbody>
                        @forelse ($this->estrutura as $a)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold" style="display:flex;align-items:center;gap:10px;"><span style="width:32px;height:32px;border-radius:8px;background:color-mix(in srgb,#6366f1 10%,transparent);display:inline-flex;align-items:center;justify-content:center;color:#6366f1;font-size:13px;"><i class="fas fa-tag"></i></span>{{ $a['nome'] }}</td>
                                <td class="data-table-td font-mono text-muted">{{ $a['slug'] }}</td>
                                <td class="data-table-td text-center"><span class="badge-sm" style="background:color-mix(in srgb,var(--primary-500)10%,transparent);color:var(--primary-600);text-transform:uppercase;">{{ $a['tipo'] }}</span></td>
                                <td class="data-table-td text-center text-muted">{{ $a['unidade_medida']['sigla'] ?? '—' }}</td>
                                <td class="data-table-td text-center"><span class="badge-sm {{ $a['ativo'] ? 'badge-ativo' : 'badge-inativo' }}">{{ $a['ativo'] ? 'Ativo' : 'Inativo' }}</span></td>
                                <td class="data-table-td text-center"><div class="table-actions"><button wire:click="selecionar({{ $a['id'] }})" class="table-action-btn" style="color:var(--primary-600);" title="Editar"><i class="fas fa-pen"></i></button><button wire:click="excluir({{ $a['id'] }})" wire:confirm="Excluir atributo?" class="table-action-btn" style="color:var(--danger);" title="Excluir"><i class="fas fa-trash-alt"></i></button></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="data-table-empty">Nenhum atributo encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
