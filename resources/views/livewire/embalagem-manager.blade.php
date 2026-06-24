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
            <div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Embalagens</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Cadastros</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Embalagens</span></div></div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>
        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple"><i class="fas fa-box"></i></div><div><span class="metric-label">Total</span><span class="metric-value" style="font-size:20px;">{{ $this->totalGeral }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green"><i class="fas fa-circle-check"></i></div><div><span class="metric-label">Ativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalAtivos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber"><i class="fas fa-circle-minus"></i></div><div><span class="metric-label">Inativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalInativos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon blue"><i class="fas fa-tag"></i></div><div><span class="metric-label">Com Sigla</span><span class="metric-value" style="font-size:20px;">{{ $this->totalComSigla }}</span></div></div>
        </div>
        <div class="grid-2col-custom" style="margin-bottom:20px;">
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas {{ $this->editandoId ? 'fa-edit' : 'fa-plus' }}" style="color:var(--primary-600);"></i> {{ $this->editandoId ? 'Editando Embalagem' : 'Nova Embalagem' }}</span><span class="form-card-subtitle">{{ $this->editandoId ? 'Altere os dados.' : 'Cadastre tipos de embalagem.' }}</span>@if ($this->editandoId)<button wire:click="resetForm" class="btn-sm btn-secondary" style="margin-left:auto;">Cancelar</button>@endif</div>
                <form wire:submit="cadastrar" class="form-body">
                    <div class="grid-2" style="margin-bottom:12px;">
                        <div class="field"><label>Nome *</label><input wire:model.blur="nome" placeholder="Ex: Garrafa Pet, Caixa, Saco">@error('nome')<div class="err">{{ $message }}</div>@enderror@if ($this->duplicata)<div style="color:var(--warning);font-size:12px;margin-top:4px;">Já existe.</div>@endif</div>
                        <div class="field"><label>Sigla</label><input wire:model.blur="sigla" placeholder="Ex: PET, CX, SC"></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;color:var(--text);"><input wire:model="ativo" type="checkbox" checked style="width:18px;height:18px;"> Embalagem ativa</label></div>
                    <div class="form-actions" style="margin-top:0;"><button type="submit" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-save"></i> {{ $this->editandoId ? 'Atualizar' : 'Cadastrar' }}</span><span wire:loading>Salvando...</span></button></div>
                </form>
            </div>
            <div class="sidebar-card"><div class="sidebar-card-header"><i class="fas fa-circle-info"></i> Sobre Embalagens</div><div class="sidebar-card-body" style="font-size:13px;color:var(--muted);line-height:1.6;"><p style="margin-bottom:12px;">Embalagens definem como os produtos são acondicionados.</p><div style="font-weight:700;color:var(--text);margin-bottom:8px;">Dicas:</div><ul style="list-style:none;padding:0;"><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Use nomes descritivos</li><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Sigla opcional (PET, CX)</li><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Desative sem excluir</li></ul></div></div>
        </div>
        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-list"></i> Embalagens ({{ $this->totalGeral }})</span><input wire:model.live.debounce.150ms="busca" placeholder="Buscar embalagem..." class="data-table-filter"></div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th class="data-table-th text-left">Embalagem</th><th class="data-table-th text-center">Sigla</th><th class="data-table-th text-center">Status</th><th class="data-table-th text-center" style="width:90px;">Ações</th></tr></thead>
                    <tbody>
                        @forelse ($this->estrutura() as $e)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold" style="display:flex;align-items:center;gap:10px;"><span style="width:32px;height:32px;border-radius:8px;background:color-mix(in srgb,#6366f1 10%,transparent);display:inline-flex;align-items:center;justify-content:center;color:#6366f1;font-size:13px;"><i class="fas fa-box"></i></span>{{ $e['nome'] }}</td>
                                <td class="data-table-td text-center font-mono font-bold">{{ $e['sigla'] ?? '—' }}</td>
                                <td class="data-table-td text-center"><span class="badge-sm {{ $e['ativo'] ? 'badge-ativo' : 'badge-inativo' }}">{{ $e['ativo'] ? 'Ativo' : 'Inativo' }}</span></td>
                                <td class="data-table-td text-center"><div class="table-actions"><button wire:click="selecionar({{ $e['id'] }})" class="table-action-btn" style="color:var(--primary-600);" title="Editar"><i class="fas fa-pen"></i></button><button wire:click="excluir({{ $e['id'] }})" wire:confirm="Excluir?" class="table-action-btn" style="color:var(--danger);" title="Excluir"><i class="fas fa-trash-alt"></i></button></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="data-table-empty">Nenhuma embalagem encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
