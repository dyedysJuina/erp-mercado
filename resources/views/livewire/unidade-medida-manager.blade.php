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
            <div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Unidades de Medida</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Cadastros</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Unidades</span></div></div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>
        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple"><i class="fas fa-ruler"></i></div><div><span class="metric-label">Total</span><span class="metric-value" style="font-size:20px;">{{ $this->totalGeral }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green"><i class="fas fa-percent"></i></div><div><span class="metric-label">Decimal</span><span class="metric-value" style="font-size:20px;">{{ $this->totalDecimal }}</span></div></div>
            <div class="metric-card"><div class="metric-icon blue"><i class="fas fa-hashtag"></i></div><div><span class="metric-label">Inteiro</span><span class="metric-value" style="font-size:20px;">{{ $this->totalInteiro }}</span></div></div>
            <div class="metric-card"><div class="metric-icon" style="background:color-mix(in srgb,#f59e0b 12%,transparent);color:#f59e0b;"><i class="fas fa-font"></i></div><div><span class="metric-label">Siglas</span><span class="metric-value" style="font-size:20px;">{{ $this->totalSiglas }}</span></div></div>
        </div>
        <div class="grid-2col-custom" style="margin-bottom:20px;">
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas {{ $this->editandoId ? 'fa-edit' : 'fa-plus' }}" style="color:var(--primary-600);"></i> {{ $this->editandoId ? 'Editando Unidade' : 'Nova Unidade' }}</span><span class="form-card-subtitle">{{ $this->editandoId ? 'Altere os dados.' : 'Cadastre unidades para produtos.' }}</span>@if ($this->editandoId)<button wire:click="resetForm" class="btn-sm btn-secondary" style="margin-left:auto;">Cancelar</button>@endif</div>
                <form wire:submit="cadastrar" class="form-body">
                    <div class="grid-2" style="margin-bottom:12px;">
                        <div class="field"><label>Sigla *</label><input wire:model.blur="sigla" placeholder="Ex: KG, L, UN, CX" maxlength="10">@error('sigla')<div class="err">{{ $message }}</div>@enderror@if ($this->duplicata)<div style="color:var(--warning);font-size:12px;margin-top:4px;">Já existe.</div>@endif</div>
                        <div class="field"><label>Nome *</label><input wire:model.blur="nome" placeholder="Ex: Quilograma, Litro, Unidade">@error('nome')<div class="err">{{ $message }}</div>@enderror</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;color:var(--text);"><input wire:model="permite_decimal" type="checkbox" style="width:18px;height:18px;"> Permite casas decimais</label></div>
                    <div class="form-actions" style="margin-top:0;"><button type="submit" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-save"></i> {{ $this->editandoId ? 'Atualizar' : 'Cadastrar' }}</span><span wire:loading>Salvando...</span></button></div>
                </form>
            </div>
            <div class="sidebar-card"><div class="sidebar-card-header"><i class="fas fa-circle-info"></i> Sobre Unidades</div><div class="sidebar-card-body" style="font-size:13px;color:var(--muted);line-height:1.6;"><p style="margin-bottom:12px;">Unidades de medida para quantificar produtos.</p><div style="font-weight:700;color:var(--text);margin-bottom:8px;">Dicas:</div><ul style="list-style:none;padding:0;"><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Use siglas padronizadas</li><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Decimal para fracionáveis</li><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Inteiro para contáveis</li></ul></div></div>
        </div>
        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-list"></i> Unidades ({{ $this->totalGeral }})</span><input wire:model.live.debounce.150ms="busca" placeholder="Buscar unidade..." class="data-table-filter"></div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th class="data-table-th text-left">Unidade</th><th class="data-table-th text-center">Sigla</th><th class="data-table-th text-center">Tipo</th><th class="data-table-th text-center" style="width:90px;">Ações</th></tr></thead>
                    <tbody>
                        @forelse ($this->estrutura() as $u)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold" style="display:flex;align-items:center;gap:10px;"><span style="width:32px;height:32px;border-radius:8px;background:color-mix(in srgb,#6366f1 10%,transparent);display:inline-flex;align-items:center;justify-content:center;color:#6366f1;font-size:13px;"><i class="fas fa-ruler"></i></span>{{ $u['nome'] }}</td>
                                <td class="data-table-td text-center font-mono font-bold">{{ $u['sigla'] }}</td>
                                <td class="data-table-td text-center"><span class="badge-sm" style="background:color-mix(in srgb,{{ $u['permite_decimal'] ? 'var(--success)' : 'var(--primary-500)' }}10%,transparent);color:{{ $u['permite_decimal'] ? 'var(--success)' : 'var(--primary-600)' }};text-transform:uppercase;">{{ $u['permite_decimal'] ? 'Decimal' : 'Inteiro' }}</span></td>
                                <td class="data-table-td text-center"><div class="table-actions"><button wire:click="selecionar({{ $u['id'] }})" class="table-action-btn" style="color:var(--primary-600);" title="Editar"><i class="fas fa-pen"></i></button><button wire:click="excluir({{ $u['id'] }})" wire:confirm="Excluir?" class="table-action-btn" style="color:var(--danger);" title="Excluir"><i class="fas fa-trash-alt"></i></button></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="data-table-empty">Nenhuma unidade encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
